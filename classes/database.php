<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    // Encapsulation: property private statis untuk menyimpan instance
    private static $instance = null;
    private $conn;

    // Constructor dibuat private agar tidak bisa di-instansiasi dengan 'new Database()'
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            // Syarat PDO Prepared Statement
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    // Method statis untuk mendapatkan satu-satunya instance (Singleton)
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Method untuk mengambil objek PDO-nya
    public function getConnection() {
        return $this->conn;
    }
}
?>