<?php
class Helper {
    
    // Validasi dan sanitasi input
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validatePhone($phone) {
        // Validasi format nomor telepon Indonesia
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $phone);
    }
    
    public static function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    public static function validateNumber($number, $min = 0) {
        return is_numeric($number) && $number >= $min;
    }
    
    public static function validateRequired($value) {
        return !empty(trim($value));
    }
    
    // Format output
    public static function formatMoney($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    
    public static function formatDate($date) {
        if (empty($date)) return '-';
        return date('d/m/Y', strtotime($date));
    }
    
    public static function formatDateTime($datetime) {
        if (empty($datetime)) return '-';
        return date('d/m/Y H:i', strtotime($datetime));
    }
    
    // Generate kode otomatis
    public static function generateCode($prefix, $table, $column, $db) {
        $sql = "SELECT MAX(CAST(SUBSTR($column, " . (strlen($prefix) + 1) . ") AS INTEGER)) as max_num 
                FROM $table WHERE $column LIKE ?";
        $result = $db->fetch($sql, [$prefix . '%']);
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
    
    // Upload file
    public static function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file parameter');
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }
        
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception('File too large');
        }
        
        $filename = uniqid() . '.' . $extension;
        $filepath = $destination . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        return $filename;
    }
    
    // CSRF Protection
    public static function generateCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Response JSON
    public static function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    // Success/Error messages
    public static function successResponse($message, $data = null) {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
    }
    
    public static function errorResponse($message, $code = 400) {
        return [
            'status' => 'error',
            'message' => $message,
            'code' => $code
        ];
    }
}
?>
