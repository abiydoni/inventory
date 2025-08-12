<?php
// views/supplier/api.php - Endpoint JSON untuk modul supplier
require_once __DIR__ . '/../../config.php';

// Start session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../../classes/' . $class . '.php';
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
        $row = $db->fetch('SELECT * FROM supplier WHERE id = ?', [$id]);
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
            $telepon = $_POST['telepon'] ?? '';
            $email = $_POST['email'] ?? '';
            $alamat = $_POST['alamat'] ?? '';
            
            if (!Helper::validateRequired($kode)) throw new Exception('Kode supplier wajib diisi');
            if (!Helper::validateRequired($nama)) throw new Exception('Nama supplier wajib diisi');
            if (!empty($email) && !Helper::validateEmail($email)) throw new Exception('Format email tidak valid');
            if (!empty($telepon) && !Helper::validatePhone($telepon)) throw new Exception('Format telepon tidak valid');
            
            $existing = $db->fetch('SELECT id FROM supplier WHERE kode = ?', [Helper::sanitize($kode)]);
            if ($existing) throw new Exception('Kode supplier sudah digunakan');
            
            $db->execute('INSERT INTO supplier (kode, nama, telepon, email, alamat) VALUES (?, ?, ?, ?, ?)', [
                Helper::sanitize($kode),
                Helper::sanitize($nama),
                Helper::sanitize($telepon),
                Helper::sanitize($email),
                Helper::sanitize($alamat)
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Supplier berhasil ditambahkan']);
            exit;
        }

        if ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $kode = $_POST['kode'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $telepon = $_POST['telepon'] ?? '';
            $email = $_POST['email'] ?? '';
            $alamat = $_POST['alamat'] ?? '';
            
            if ($id <= 0) throw new Exception('ID tidak valid');
            if (!Helper::validateRequired($kode)) throw new Exception('Kode supplier wajib diisi');
            if (!Helper::validateRequired($nama)) throw new Exception('Nama supplier wajib diisi');
            if (!empty($email) && !Helper::validateEmail($email)) throw new Exception('Format email tidak valid');
            if (!empty($telepon) && !Helper::validatePhone($telepon)) throw new Exception('Format telepon tidak valid');
            
            $existing = $db->fetch('SELECT id FROM supplier WHERE kode = ? AND id != ?', [Helper::sanitize($kode), $id]);
            if ($existing) throw new Exception('Kode supplier sudah digunakan');
            
            $db->execute('UPDATE supplier SET kode = ?, nama = ?, telepon = ?, email = ?, alamat = ? WHERE id = ?', [
                Helper::sanitize($kode),
                Helper::sanitize($nama),
                Helper::sanitize($telepon),
                Helper::sanitize($email),
                Helper::sanitize($alamat),
                $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Supplier berhasil diperbarui']);
            exit;
        }

        throw new Exception('Action tidak valid');
    }

    throw new Exception('Endpoint tidak valid');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
