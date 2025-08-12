<?php
require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

spl_autoload_register(function ($c) { $f = __DIR__ . '/../../classes/' . $c . '.php'; if (file_exists($f)) require_once $f; });
header('Content-Type: application/json');

try { $db = Database::getInstance(); } catch (Exception $e) { http_response_code(500); echo json_encode(['status'=>'error','message'=>'DB init error']); exit; }

function getSetting($db, $key) {
    $row = $db->fetch('SELECT value FROM settings WHERE key = ?', [$key]);
    return $row['value'] ?? null;
}

function getCoaInfo($db, $id) {
    if (!$id) return ['nama' => null, 'kode' => null];
    $row = $db->fetch('SELECT nama, kode FROM coa WHERE id = ?', [$id]);
    return $row ? ['nama' => $row['nama'], 'kode' => $row['kode']] : ['nama' => null, 'kode' => null];
}

try {
  $method = $_SERVER['REQUEST_METHOD'];
  $action = $_GET['action'] ?? ($_POST['action'] ?? '');

  if ($action === 'get' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0); if ($id<=0) throw new Exception('ID tidak valid');
    $p = $db->fetch('SELECT p.*, s.nama as supplier_nama FROM pembelian p LEFT JOIN supplier s ON p.supplier_id=s.id WHERE p.id=?', [$id]);
    if (!$p) throw new Exception('Data tidak ditemukan');
    $items = $db->fetchAll('SELECT d.*, st.nama FROM pembelian_detail d LEFT JOIN stok st ON st.id=d.stok_id WHERE d.pembelian_id=?', [$id]);
    echo json_encode(['status'=>'success','data'=> array_merge($p, ['items'=>$items])]);
    exit;
  }

  if ($action === 'bayar_hutang' && $method === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!Helper::validateCSRF($token)) throw new Exception('Invalid CSRF token');
    $id = (int)($_POST['id'] ?? 0); if ($id<=0) throw new Exception('ID tidak valid');
    $nominal = (int)($_POST['nominal'] ?? 0); if ($nominal<=0) throw new Exception('Nominal tidak valid');
    $metode = $_POST['metode'] === 'bank' ? 'bank' : 'kas';
    $akunKas = $metode==='bank' ? getSetting($db, 'akun_bank') : getSetting($db, 'akun_kas');
    if (!$akunKas) throw new Exception('Akun kas/bank belum disetel di Settings');
    $akunHutang = getSetting($db, 'akun_hutang');
    if (!$akunHutang) throw new Exception('Akun hutang belum disetel di Settings');

    // Override template jika tersedia
    $overrideDebet = getSetting($db, 'pembayaran_hutang_debet');
    $overrideKredit = getSetting($db, 'pembayaran_hutang_kredit');
    $debetCoaId = $overrideDebet ?: $akunHutang;
    $kreditCoaId = $overrideKredit ?: $akunKas;
    
    $debetInfo = getCoaInfo($db, $debetCoaId);
    $kreditInfo = getCoaInfo($db, $kreditCoaId);
    $debetNama = $debetInfo['nama'] ?: 'Hutang Usaha';
    $kreditNama = $kreditInfo['nama'] ?: ($metode==='bank' ? 'Bank' : 'Kas');
    $debetKode = $debetInfo['kode'] ?: 'HUTANG';
    $kreditKode = $kreditInfo['kode'] ?: ($metode==='bank' ? 'BANK' : 'KAS');

    $p = $db->fetch('SELECT * FROM pembelian WHERE id=?', [$id]);
    if (!$p) throw new Exception('Data tidak ditemukan');
    if ($p['status_pembayaran'] === 'lunas') throw new Exception('Hutang sudah lunas');

    // Hitung sisa
    $paid = (int)($db->fetch('SELECT COALESCE(SUM(nominal),0) as t FROM pembayaran_hutang WHERE pembelian_id=?', [$id])['t'] ?? 0);
    $total = (int)$p['total'];
    $sisa = max(0, $total - $paid);
    if ($sisa <= 0) throw new Exception('Hutang sudah lunas');
    if ($nominal > $sisa) { $nominal = $sisa; }

    // Catat pembayaran
    $db->execute('INSERT INTO pembayaran_hutang (pembelian_id, tanggal, metode, akun_kas_bank, nominal, keterangan) VALUES (?,?,?,?,?,?)', [
      $id, date('Y-m-d H:i:s'), $metode, $akunKas, $nominal, 'Pembayaran hutang #' . $id
    ]);

    // Update status jika lunas (anggap nominal >= total -> lunas; implementasi cicilan bisa dikembangkan)
    $paidBaru = $paid + $nominal;
    $db->execute("UPDATE pembelian SET status_pembayaran=? WHERE id=?", [
      $paidBaru >= $total ? 'lunas' : 'belum_lunas', $id
    ]);

    // Jurnal: Dr (override/default), Cr (override/default)
    $db->execute('INSERT INTO jurnal (tanggal, akun, debit, kredit, keterangan, coa_kode) VALUES (?,?,?,?,?,?)', [
      date('Y-m-d'), $debetNama, $nominal, 0, 'Pembayaran hutang #' . $id, $debetKode
    ]);
    $db->execute('INSERT INTO jurnal (tanggal, akun, debit, kredit, keterangan, coa_kode) VALUES (?,?,?,?,?,?)', [
      date('Y-m-d'), $kreditNama, 0, $nominal, 'Pembayaran hutang #' . $id, $kreditKode
    ]);

    echo json_encode(['status'=>'success','message'=>'Pembayaran hutang tercatat', 'sisa'=> max(0, $total - $paidBaru)]);
    exit;
  }

  throw new Exception('Endpoint tidak valid');
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
