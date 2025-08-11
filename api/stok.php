<?php
// api/stok.php - Endpoint JSON untuk modul stok
require_once __DIR__ . '/../config.php';

// Start session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB init error']);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');

    if ($action === 'get' && $method === 'GET') {
        $token = $_GET['token'] ?? '';
        if (!Helper::validateCSRF($token)) {
            throw new Exception('Invalid CSRF token');
        }
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID tidak valid');
        $row = $db->fetch('SELECT * FROM stok WHERE id = ?', [$id]);
        if (!$row) throw new Exception('Data tidak ditemukan');
        echo json_encode($row);
        exit;
    }

    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!Helper::validateCSRF($token)) {
            throw new Exception('Invalid CSRF token');
        }

        if ($action === 'tambah') {
            $kode = $_POST['kode'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $stok = $_POST['stok'] ?? '';
            $harga = $_POST['harga'] ?? '';
            if (!Helper::validateRequired($kode)) throw new Exception('Kode produk wajib diisi');
            if (!Helper::validateRequired($nama)) throw new Exception('Nama produk wajib diisi');
            if (!Helper::validateNumber($stok)) throw new Exception('Stok harus berupa angka positif');
            if (!Helper::validateNumber($harga)) throw new Exception('Harga harus berupa angka positif');
            $existing = $db->fetch('SELECT id FROM stok WHERE kode = ?', [Helper::sanitize($kode)]);
            if ($existing) throw new Exception('Kode produk sudah digunakan');
            $db->execute('INSERT INTO stok (kode, nama, stok, harga) VALUES (?, ?, ?, ?)', [
                Helper::sanitize($kode),
                Helper::sanitize($nama),
                (int)$stok,
                (int)$harga,
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan']);
            exit;
        }

        if ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $kode = $_POST['kode'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $stok = $_POST['stok'] ?? '';
            $harga = $_POST['harga'] ?? '';
            if ($id <= 0) throw new Exception('ID tidak valid');
            if (!Helper::validateRequired($kode)) throw new Exception('Kode produk wajib diisi');
            if (!Helper::validateRequired($nama)) throw new Exception('Nama produk wajib diisi');
            if (!Helper::validateNumber($stok)) throw new Exception('Stok harus berupa angka positif');
            if (!Helper::validateNumber($harga)) throw new Exception('Harga harus berupa angka positif');
            $existing = $db->fetch('SELECT id FROM stok WHERE kode = ? AND id != ?', [Helper::sanitize($kode), $id]);
            if ($existing) throw new Exception('Kode produk sudah digunakan');
            $db->execute('UPDATE stok SET kode = ?, nama = ?, stok = ?, harga = ? WHERE id = ?', [
                Helper::sanitize($kode),
                Helper::sanitize($nama),
                (int)$stok,
                (int)$harga,
                $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Produk berhasil diperbarui']);
            exit;
        }

        throw new Exception('Action tidak valid');
    }

    throw new Exception('Endpoint tidak valid');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
