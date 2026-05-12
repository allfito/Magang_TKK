<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';

/**
 * BaseController
 * Kelas abstrak yang menyediakan dependensi umum untuk semua controller.
 * Prinsip OOP: Abstraction & Inheritance.
 * Dependency Injection: DB connection disuntikkan lewat constructor.
 */
abstract class BaseController
{
    protected mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kembalikan response standar.
     */
    protected function response(bool $status, string $message, array $extra = []): array
    {
        return array_merge(['status' => $status, 'message' => $message], $extra);
    }

    protected function success(string $message, array $extra = []): array
    {
        return $this->response(true, $message, $extra);
    }

    protected function error(string $message, array $extra = []): array
    {
        return $this->response(false, $message, $extra);
    }
}
