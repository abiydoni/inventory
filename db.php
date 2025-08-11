<!-- --- FILE: db.php --- -->
<?php
// db.php - koneksi SQLite & helper
$dbFile = __DIR__ . '/database/app.db';
$initSql = __DIR__ . '/database/init.sql';

try {
    if (!file_exists(dirname($dbFile))) mkdir(dirname($dbFile), 0755, true);
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON;');
    // create tables if not exists
    if (file_exists($initSql)) {
        $sql = file_get_contents($initSql);
        // split by semicolon but keep safe
        $stmts = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($stmts as $s) {
            if ($s) $db->exec($s);
        }
    }
} catch (Exception $e) {
    die('DB Error: ' . $e->getMessage());
}

function uang($n) {
    return 'Rp ' . number_format($n,0,',','.');
}
?>