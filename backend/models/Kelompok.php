<?php

require_once __DIR__ . '/../core/BaseModel.php';

/**
 * Kelompok
 * Model untuk entitas kelompok magang.
 * Prinsip OOP: Inheritance (extends BaseModel).
 */
class Kelompok extends BaseModel
{
    public function findByKetua(int $userId): ?array
    {
        return $this->fetchOne(
            'SELECT id, nama, ketua_user_id FROM kelompok WHERE ketua_user_id = ? LIMIT 1',
            'i',
            [$userId]
        );
    }

    /**
     * Buat kelompok baru dan kembalikan ID-nya.
     *
     * @throws RuntimeException Jika pembuatan gagal.
     */
    public function create(string $nama, int $ketuaUserId): int
    {
        $id = $this->run(
            'INSERT INTO kelompok (nama, ketua_user_id, created_at) VALUES (?, ?, NOW())',
            'si',
            [$nama, $ketuaUserId]
        );

        if (!$id) {
            throw new RuntimeException('Gagal membuat kelompok.');
        }

        return (int) $id;
    }

    /**
     * Tambahkan anggota ke kelompok.
     *
     * @throws RuntimeException Jika penambahan anggota gagal.
     */
    public function addAnggota(int $kelompokId, int $mahasiswaId, string $peran): void
    {
        $result = $this->run(
            'INSERT INTO anggota_kelompok (kelompok_id, mahasiswa_id, peran, status_berkas, created_at) VALUES (?, ?, ?, "pending", NOW())',
            'iis',
            [$kelompokId, $mahasiswaId, $peran]
        );

        if (!$result) {
            throw new RuntimeException('Gagal menyimpan relasi anggota kelompok.');
        }
    }
}
