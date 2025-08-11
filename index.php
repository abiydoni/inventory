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

// Load header (sudah include definisi $page)
include __DIR__ . '/views/layouts/header.php';

// Load content berdasarkan $page yang sudah didefinisikan di header
$path = __DIR__ . "/views/{$page}/index.php";
if (file_exists($path)) {
    include $path;
} else {
    echo "<div class='text-center mt-10 text-gray-600'>
            <i class='bx bx-error-circle text-6xl mb-4'></i>
            <h2 class='text-2xl font-bold mb-2'>Halaman Tidak Ditemukan</h2>
            <p>Halaman <strong>" . htmlspecialchars($page) . "</strong> tidak tersedia.</p>
          </div>";
}

// Load footer
include __DIR__ . '/views/layouts/footer.php';
?>