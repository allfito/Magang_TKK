<?php

/**
 * BaseModel
 * Kelas abstrak sebagai fondasi untuk semua model.
 * Menerapkan prinsip OOP: Abstraction & Inheritance.
 */
abstract class BaseModel
{
    protected mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Eksekusi prepared statement dengan bind_param dinamis.
     * Mengurangi duplikasi kode di seluruh model.
     */
    protected function execute(string $sql, string $types = '', array $params = []): mysqli_stmt|false
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Ambil satu baris hasil query.
     */
    protected function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $stmt = $this->execute($sql, $types, $params);
        if (!$stmt) {
            return null;
        }
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    /**
     * Ambil semua baris hasil query.
     */
    protected function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->execute($sql, $types, $params);
        if (!$stmt) {
            return [];
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Jalankan query tanpa hasil (INSERT / UPDATE / DELETE).
     * Mengembalikan insert_id untuk INSERT, atau true/false.
     */
    protected function run(string $sql, string $types = '', array $params = []): int|bool
    {
        $stmt = $this->execute($sql, $types, $params);
        if (!$stmt) {
            return false;
        }
        return $stmt->insert_id ?: true;
    }
}
