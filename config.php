<?php
// Konfigurasi aplikasi
define('APP_NAME', 'Sistem Inventori');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/inventory');

// Konfigurasi database
define('DB_FILE', __DIR__ . '/database/app.db');
define('INIT_SQL', __DIR__ . '/database/init.sql');

// Konfigurasi session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set 1 jika menggunakan HTTPS

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (set false untuk production)
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
