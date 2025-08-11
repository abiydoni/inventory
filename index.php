<!-- --- FILE: index.php --- -->
<?php
// Load konfigurasi
require_once 'config.php';

// Start session dengan keamanan
session_start();
session_regenerate_id(true);

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inisialisasi database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    if (DEBUG_MODE) {
        die('Database Error: ' . $e->getMessage());
    } else {
        die('Aplikasi sedang dalam maintenance. Silakan coba lagi nanti.');
    }
}

// Tentukan halaman
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'stok', 'customer', 'supplier', 'pembelian', 'penjualan', 'jurnal', 'laporan', 'profil', 'coa'];
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}
$path = __DIR__ . "/views/{$page}/index.php";

// Jika ini request API (aksi), langsung delegasikan ke view tanpa header/footer
if (isset($_GET['aksi'])) {
    if (file_exists($path)) {
        include $path; // view bertanggung jawab mengirim JSON dan exit
        exit;
    } else {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint tidak ditemukan']);
        exit;
    }
}

// Render normal (HTML)
include __DIR__ . '/views/layouts/header.php';
if (file_exists($path)) {
    include $path;
} else {
    echo "<div class='text-center mt-10 text-gray-600'>
            <i class='bx bx-error-circle text-6xl mb-4'></i>
            <h2 class='text-2xl font-bold mb-2'>Halaman Tidak Ditemukan</h2>
            <p>Halaman <strong>" . htmlspecialchars($page) . "</strong> tidak tersedia.</p>
          </div>";
}
include __DIR__ . '/views/layouts/footer.php';
?>