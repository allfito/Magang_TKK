<?php

require_once __DIR__ . '/../core/BaseModel.php';

/**
 * User
 * Model untuk entitas pengguna (mahasiswa & koordinator).
 * Prinsip OOP: Inheritance (extends BaseModel), Encapsulation.
 */
class User extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM user WHERE email = ? LIMIT 1',
            's',
            [$email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nama, email, no_tlp, role, created_at FROM user WHERE id = ? LIMIT 1',
            'i',
            [$id]
        );
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Buat akun pengguna baru dengan role 'mahasiswa'.
     */
    public function create(array $data): bool
    {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $result = $this->run(
            'INSERT INTO user (nama, email, no_tlp, password_hash, role, created_at) VALUES (?, ?, ?, ?, \'mahasiswa\', NOW())',
            'ssss',
            [$data['nama'], $data['email'], $data['no_tlp'], $passwordHash]
        );

        return $result !== false;
    }
}
