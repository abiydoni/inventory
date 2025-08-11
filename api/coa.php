<?php
require_once __DIR__ . '/../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

spl_autoload_register(function ($c) { $f = __DIR__ . '/../classes/' . $c . '.php'; if (file_exists($f)) require_once $f; });
header('Content-Type: application/json');

try { $db = Database::getInstance(); } catch (Exception $e) { http_response_code(500); echo json_encode(['status'=>'error','message'=>'DB init error']); exit; }

try {
  $method = $_SERVER['REQUEST_METHOD'];
  $action = $_GET['action'] ?? ($_POST['action'] ?? '');

  if ($action === 'get' && $method === 'GET') {
    $token = $_GET['token'] ?? '';
    if (!Helper::validateCSRF($token)) throw new Exception('Invalid CSRF token');
    $id = (int)($_GET['id'] ?? 0); if ($id<=0) throw new Exception('ID tidak valid');
    $row = $db->fetch('SELECT * FROM coa WHERE id = ?', [$id]);
    if (!$row) throw new Exception('Data tidak ditemukan');
    echo json_encode($row); exit;
  }

  if ($method === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!Helper::validateCSRF($token)) throw new Exception('Invalid CSRF token');

    if ($action === 'tambah') {
      $kode = trim($_POST['kode'] ?? '');
      $nama = trim($_POST['nama'] ?? '');
      $tipe = trim($_POST['tipe'] ?? '');
      $laporan = trim($_POST['laporan'] ?? '');
      $aktif = (int)($_POST['aktif'] ?? 1);
      if (!$kode || !$nama || !$tipe || !$laporan) throw new Exception('Semua field wajib diisi');
      $exist = $db->fetch('SELECT id FROM coa WHERE kode = ?', [Helper::sanitize($kode)]);
      if ($exist) throw new Exception('Kode akun sudah digunakan');
      $db->execute('INSERT INTO coa (kode,nama,tipe,laporan,aktif) VALUES (?,?,?,?,?)', [
        Helper::sanitize($kode), Helper::sanitize($nama), Helper::sanitize($tipe), Helper::sanitize($laporan), $aktif
      ]);
      echo json_encode(['status'=>'success','message'=>'Akun berhasil ditambahkan']); exit;
    }

    if ($action === 'edit') {
      $id = (int)($_POST['id'] ?? 0); if ($id<=0) throw new Exception('ID tidak valid');
      $kode = trim($_POST['kode'] ?? '');
      $nama = trim($_POST['nama'] ?? '');
      $tipe = trim($_POST['tipe'] ?? '');
      $laporan = trim($_POST['laporan'] ?? '');
      $aktif = (int)($_POST['aktif'] ?? 1);
      if (!$kode || !$nama || !$tipe || !$laporan) throw new Exception('Semua field wajib diisi');
      $exist = $db->fetch('SELECT id FROM coa WHERE kode = ? AND id != ?', [Helper::sanitize($kode), $id]);
      if ($exist) throw new Exception('Kode akun sudah digunakan');
      $db->execute('UPDATE coa SET kode=?, nama=?, tipe=?, laporan=?, aktif=? WHERE id=?', [
        Helper::sanitize($kode), Helper::sanitize($nama), Helper::sanitize($tipe), Helper::sanitize($laporan), $aktif, $id
      ]);
      echo json_encode(['status'=>'success','message'=>'Akun berhasil diperbarui']); exit;
    }

    if ($action === 'hapus') {
      $id = (int)($_POST['id'] ?? 0); if ($id<=0) throw new Exception('ID tidak valid');
      $db->execute('DELETE FROM coa WHERE id = ?', [$id]);
      echo json_encode(['status'=>'success']); exit;
    }

    throw new Exception('Action tidak valid');
  }

  throw new Exception('Endpoint tidak valid');
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
