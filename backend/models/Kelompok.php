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

    /**
     * Update biodata mahasiswa (nama, no_tlp).
     *
     * @throws RuntimeException Jika update gagal.
     */
    public function updateMahasiswa(int $mahasiswaId, string $nama, string $nim, string $noTlp): void
    {
        $result = $this->run(
            'UPDATE mahasiswa SET nama = ?, nim = ?, no_tlp = ? WHERE id = ?',
            'sssi',
            [$nama, $nim, $noTlp, $mahasiswaId]
        );
        if ($result === false) {
            throw new RuntimeException('Gagal mengupdate data mahasiswa.');
        }
    }

    /**
     * Hapus anggota kelompok berdasarkan anggota_kelompok.id.
     *
     * @throws RuntimeException Jika hapus gagal.
     */
    public function removeAnggota(int $anggotaId, int $kelompokId): void
    {
        $result = $this->run(
            'DELETE FROM anggota_kelompok WHERE id = ? AND kelompok_id = ?',
            'ii',
            [$anggotaId, $kelompokId]
        );
        if ($result === false) {
            throw new RuntimeException('Gagal menghapus anggota.');
        }
    }

    /**
     * Hitung jumlah anggota di kelompok.
     */
    public function countAnggota(int $kelompokId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) as total FROM anggota_kelompok WHERE kelompok_id = ?',
            'i',
            [$kelompokId]
        );
        return $row ? (int) $row['total'] : 0;
    }
}
