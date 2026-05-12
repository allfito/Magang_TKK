<?php

require_once __DIR__ . '/../core/BaseModel.php';

/**
 * Mahasiswa
 * Model untuk data biodata mahasiswa.
 * Prinsip OOP: Inheritance (extends BaseModel).
 */
class Mahasiswa extends BaseModel
{
    public function findByNim(string $nim): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM mahasiswa WHERE nim = ? LIMIT 1',
            's',
            [$nim]
        );
    }

    /**
     * Simpan biodata mahasiswa baru dan kembalikan ID-nya.
     *
     * @throws RuntimeException Jika penyimpanan gagal.
     */
    public function create(string $nim, string $nama, string $noTlp): int
    {
        $id = $this->run(
            'INSERT INTO mahasiswa (nim, nama, no_tlp, created_at) VALUES (?, ?, ?, NOW())',
            'sss',
            [$nim, $nama, $noTlp]
        );

        if (!$id) {
            throw new RuntimeException('Gagal menyimpan biodata mahasiswa.');
        }

        return (int) $id;
    }
}
