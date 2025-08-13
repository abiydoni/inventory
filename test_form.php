<?php
// Test form submission
header('Content-Type: application/json');

// Simulate POST data
$_POST['aksi'] = 'simpan';
$_POST['csrf_token'] = 'test_token';
$_POST['tanggal'] = date('Y-m-d H:i:s');
$_POST['supplier_id'] = '1';
$_POST['jenis_pembayaran'] = 'cash';
$_POST['diskon'] = '0';
$_POST['pajak'] = '0';
$_POST['items'] = json_encode([['id' => '1', 'nama' => 'Test Item', 'qty' => 1, 'harga' => 1000]]);

// Include the pembelian logic
try {
    require_once 'config.php';
    require_once 'classes/Database.php';
    require_once 'classes/Helper.php';
    
    $db = Database::getInstance();
    
    // Test CSRF validation
    if (!Helper::validateCSRF($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status'=>'error', 'message'=>'Invalid CSRF token']);
        exit;
    }
    
    $tanggal = $_POST['tanggal'] ?: date('Y-m-d H:i:s');
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $jenis_pembayaran = $_POST['jenis_pembayaran'] ?? 'cash';
    $diskon = (float)$_POST['diskon'];
    $pajak = (float)$_POST['pajak'];
    $items = json_decode($_POST['items'], true);
    
    if (!$supplier_id) {
        echo json_encode(['status'=>'error', 'message'=>'Supplier harus dipilih']);
        exit;
    }
    
    if (empty($items)) {
        echo json_encode(['status'=>'error', 'message'=>'Item harus ditambahkan']);
        exit;
    }
    
    // Calculate total
    $subtotal = 0;
    foreach ($items as $it) $subtotal += intval($it['qty'])*intval($it['harga']);
    $afterDisk = $subtotal - ($subtotal * $diskon/100);
    $afterTax = $afterDisk + ($afterDisk * $pajak/100);
    $total = round($afterTax);
    
    echo json_encode([
        'status' => 'ok',
        'message' => 'Test form submission successful',
        'data' => [
            'tanggal' => $tanggal,
            'supplier_id' => $supplier_id,
            'jenis_pembayaran' => $jenis_pembayaran,
            'subtotal' => $subtotal,
            'total' => $total,
            'items_count' => count($items)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Test failed: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
