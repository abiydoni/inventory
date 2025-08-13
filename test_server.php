<?php
// Simple server test
header('Content-Type: application/json');

// Test database connection
try {
    require_once 'config.php';
    require_once 'classes/Database.php';
    require_once 'classes/Helper.php';
    
    $db = Database::getInstance();
    
    // Test basic query
    $result = $db->fetch('SELECT COUNT(*) as count FROM coa');
    
    echo json_encode([
        'status' => 'ok',
        'message' => 'Server working correctly',
        'coa_count' => $result['count'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
