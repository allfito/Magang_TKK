<?php

require_once __DIR__ . '/../../config/config.php';

/**
 * Database
 * Menerapkan pola Singleton untuk memastikan hanya ada satu koneksi database.
 * Prinsip OOP: Encapsulation (constructor private, akses via getInstance).
 */
class Database
{
    private static ?Database $instance = null;
    private mysqli $connection;

    private function __construct()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_error) {
            // Gunakan exception agar error bisa di-catch, bukan die() langsung
            throw new RuntimeException(
                'Koneksi database gagal: ' . $this->connection->connect_error
            );
        }

        $this->connection->set_charset('utf8mb4');
    }

    /**
     * Mencegah kloning instance (Singleton).
     */
    private function __clone() {}

    /**
     * Kembalikan satu-satunya instance Database.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    /**
     * Kembalikan objek koneksi mysqli.
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}
