<?php
// Debug file untuk troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

// Test config
echo "<h3>Testing Configuration</h3>";
if (file_exists('config.php')) {
    echo "<p>✅ config.php exists</p>";
    try {
        require_once 'config.php';
        echo "<p>✅ config.php loaded successfully</p>";
        echo "<p><strong>APP_NAME:</strong> " . (defined('APP_NAME') ? APP_NAME : 'NOT DEFINED') . "</p>";
        echo "<p><strong>APP_VERSION:</strong> " . (defined('APP_VERSION') ? APP_VERSION : 'NOT DEFINED') . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error loading config.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ config.php not found</p>";
}

// Test classes
echo "<h3>Testing Classes</h3>";
if (file_exists('classes/Database.php')) {
    echo "<p>✅ Database.php exists</p>";
    try {
        require_once 'classes/Database.php';
        echo "<p>✅ Database.php loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error loading Database.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Database.php not found</p>";
}

if (file_exists('classes/Helper.php')) {
    echo "<p>✅ Helper.php exists</p>";
    try {
        require_once 'classes/Helper.php';
        echo "<p>✅ Helper.php loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error loading Helper.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Helper.php not found</p>";
}

// Test views
echo "<h3>Testing Views</h3>";
$views = ['layouts/header.php', 'layouts/footer.php', 'dashboard/index.php'];
foreach ($views as $view) {
    if (file_exists("views/$view")) {
        echo "<p>✅ views/$view exists</p>";
    } else {
        echo "<p>❌ views/$view not found</p>";
    }
}

// Test database
echo "<h3>Testing Database</h3>";
if (file_exists('database/app.db')) {
    echo "<p>✅ database/app.db exists</p>";
    echo "<p><strong>Database size:</strong> " . number_format(filesize('database/app.db')) . " bytes</p>";
} else {
    echo "<p>❌ database/app.db not found</p>";
}

// Test permissions
echo "<h3>Testing Permissions</h3>";
$dirs = ['database', 'uploads', 'classes', 'views'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p>✅ $dir directory exists and is readable</p>";
    } else {
        echo "<p>❌ $dir directory not found or not readable</p>";
    }
}

echo "<hr>";
echo "<p><strong>Debug completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
