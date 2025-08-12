<?php
require_once __DIR__ . '/../../config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../../classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

header('Content-Type: application/json');

try { $db = Database::getInstance(); } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB init error']);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');

    if ($action === 'list' && $method === 'GET') {
        $settings = $db->fetchAll('SELECT key, value FROM settings');
        $map = [];
        foreach ($settings as $s) { $map[$s['key']] = $s['value']; }
        echo json_encode(['status' => 'success', 'data' => $map]);
        exit;
    }

    if ($method === 'POST' && $action === 'save') {
        $token = $_POST['csrf_token'] ?? '';
        if (!Helper::validateCSRF($token)) throw new Exception('Invalid CSRF token');

        // keys yang diperbolehkan
        $allowedKeys = [
            'akun_persediaan', 'akun_kas', 'akun_bank', 'akun_piutang', 'akun_hutang',
            'akun_pendapatan_penjualan', 'akun_hpp', 'akun_beban_administrasi',
            'penjualan_persediaan', 'pembelian_persediaan', 'penjualan_piutang', 'pembelian_hutang',
            'pembayaran_hutang_debet', 'pembayaran_hutang_kredit', 'pembayaran_piutang_debet', 'pembayaran_piutang_kredit'
        ];

        foreach ($allowedKeys as $key) {
            $val = isset($_POST[$key]) ? (int)$_POST[$key] : null;
            $db->execute('INSERT INTO settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
                          ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = CURRENT_TIMESTAMP',
                [$key, $val]
            );
        }

        echo json_encode(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan']);
        exit;
    }

    throw new Exception('Endpoint tidak valid');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
