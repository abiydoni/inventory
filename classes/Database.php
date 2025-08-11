<?php
class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        try {
            if (!file_exists(dirname(DB_FILE))) {
                mkdir(dirname(DB_FILE), 0755, true);
            }
            
            $this->pdo = new PDO('sqlite:' . DB_FILE);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA foreign_keys = ON;');
            
            // Buat tabel jika belum ada
            $this->initializeTables();
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                die('Database Error: ' . $e->getMessage());
            } else {
                die('Database connection failed. Please contact administrator.');
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    private function initializeTables() {
        if (file_exists(INIT_SQL)) {
            $sql = file_get_contents(INIT_SQL);
            $stmts = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($stmts as $stmt) {
                if ($stmt) {
                    $this->pdo->exec($stmt);
                }
            }
        }
        
        // Buat tabel profil perusahaan jika belum ada
        $this->createProfileTable();
    }
    
    private function createProfileTable() {
        $sql = "CREATE TABLE IF NOT EXISTS profil_perusahaan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_perusahaan TEXT NOT NULL,
            alamat TEXT,
            telepon TEXT,
            email TEXT,
            website TEXT,
            logo TEXT,
            npwp TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        
        // Insert default profile jika belum ada
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM profil_perusahaan");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->insertDefaultProfile();
        }
    }
    
    private function insertDefaultProfile() {
        $sql = "INSERT INTO profil_perusahaan (nama_perusahaan, alamat, telepon, email) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'Perusahaan Saya',
            'Jl. Contoh No. 123, Kota, Provinsi',
            '+62 812-3456-7890',
            'info@perusahaan.com'
        ]);
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw new Exception('Query Error: ' . $e->getMessage());
            } else {
                throw new Exception('Database operation failed.');
            }
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }
    
    public function execute($sql, $params = []) {
        return $this->query($sql, $params);
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
?>
