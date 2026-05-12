<?php

require_once __DIR__ . '/../core/BaseModel.php';

/**
 * PendaftaranModel
 * Model untuk semua operasi data pendaftaran magang kelompok.
 * Prinsip OOP: Inheritance (extends BaseModel), Single Responsibility.
 */
class PendaftaranModel extends BaseModel
{
    // ---------------------------------------------------------------
    // READ
    // ---------------------------------------------------------------

    public function getAnggotaKelompok(int $kelompokId): array
    {
        return $this->fetchAll(
            'SELECT id FROM anggota_kelompok WHERE kelompok_id = ? ORDER BY created_at ASC',
            'i',
            [$kelompokId]
        );
    }

    public function getLokasi(int $kelompokId): ?array
    {
        return $this->fetchOne(
            'SELECT id FROM pendaftaran_lokasi WHERE kelompok_id = ? LIMIT 1',
            'i',
            [$kelompokId]
        );
    }

    public function getProposal(int $kelompokId): ?array
    {
        return $this->fetchOne(
            'SELECT id FROM proposal WHERE kelompok_id = ? LIMIT 1',
            'i',
            [$kelompokId]
        );
    }

    public function findPerusahaanByName(string $nama): ?int
    {
        $row = $this->fetchOne(
            'SELECT id FROM perusahaan WHERE nama = ? LIMIT 1',
            's',
            [$nama]
        );
        return $row ? (int) $row['id'] : null;
    }

    // ---------------------------------------------------------------
    // CREATE
    // ---------------------------------------------------------------

    /**
     * Simpan data perusahaan baru dan kembalikan ID-nya.
     *
     * @throws RuntimeException
     */
    public function createPerusahaan(array $data): int
    {
        $id = $this->run(
            'INSERT INTO perusahaan (nama, nama_pimpinan, bidang, telepon, alamat, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            'sssssss',
            [
                $data['nama'],
                $data['nama_pimpinan'],
                $data['bidang'],
                $data['telepon'],
                $data['alamat'],
                $data['latitude'] ?? '',
                $data['longitude'] ?? '',
            ]
        );

        if (!$id) {
            throw new RuntimeException('Gagal menyimpan data perusahaan.');
        }

        return (int) $id;
    }

    /**
     * @throws RuntimeException
     */
    public function createLokasi(int $kelompokId, int $perusahaanId): void
    {
        $result = $this->run(
            'INSERT INTO pendaftaran_lokasi (kelompok_id, perusahaan_id, status_verifikasi, created_at) VALUES (?, ?, "menunggu", NOW())',
            'ii',
            [$kelompokId, $perusahaanId]
        );

        if (!$result) {
            throw new RuntimeException('Gagal menyimpan pendaftaran lokasi.');
        }
    }

    /**
     * @throws RuntimeException
     */
    public function createProposal(int $kelompokId, string $judul, string $filePath): void
    {
        $result = $this->run(
            'INSERT INTO proposal (kelompok_id, judul, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())',
            'iss',
            [$kelompokId, $judul, $filePath]
        );

        if (!$result) {
            throw new RuntimeException('Gagal menyimpan proposal.');
        }
    }

    /**
     * @throws RuntimeException
     */
    public function createBuktiDiterima(int $kelompokId, int $perusahaanId, string $filePath): void
    {
        $result = $this->run(
            'INSERT INTO bukti_diterima (kelompok_id, perusahaan_id, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())',
            'iis',
            [$kelompokId, $perusahaanId, $filePath]
        );

        if (!$result) {
            throw new RuntimeException('Gagal menyimpan bukti diterima.');
        }
    }

    // ---------------------------------------------------------------
    // UPSERT
    // ---------------------------------------------------------------

    /**
     * Simpan atau perbarui berkas anggota.
     */
    public function upsertBerkas(int $anggotaId, string $jenis, string $filePath): bool
    {
        $existing = $this->fetchOne(
            'SELECT id FROM berkas_anggota WHERE anggota_id = ? AND jenis_berkas = ? LIMIT 1',
            'is',
            [$anggotaId, $jenis]
        );

        if ($existing) {
            $result = $this->run(
                'UPDATE berkas_anggota SET file_path = ?, status_verifikasi = "menunggu", created_at = NOW() WHERE id = ?',
                'si',
                [$filePath, $existing['id']]
            );
        } else {
            $result = $this->run(
                'INSERT INTO berkas_anggota (anggota_id, jenis_berkas, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())',
                'iss',
                [$anggotaId, $jenis, $filePath]
            );
        }

        return $result !== false;
    }

    // ---------------------------------------------------------------
    // DELETE
    // ---------------------------------------------------------------

    /**
     * Hapus data pendaftaran berdasarkan tipe.
     *
     * @throws InvalidArgumentException Tipe tidak valid.
     * @throws RuntimeException         Gagal menghapus.
     */
    public function deleteRegistration(int $kelompokId, string $type): void
    {
        $queries = [
            'lokasi'   => 'DELETE FROM pendaftaran_lokasi WHERE kelompok_id = ?',
            'proposal' => 'DELETE FROM proposal WHERE kelompok_id = ?',
            'bukti'    => 'DELETE FROM bukti_diterima WHERE kelompok_id = ?',
        ];

        if ($type === 'berkas') {
            $this->deleteBerkas($kelompokId);
            return;
        }

        if (!isset($queries[$type])) {
            throw new InvalidArgumentException("Tipe penghapusan '{$type}' tidak valid.");
        }

        $result = $this->run($queries[$type], 'i', [$kelompokId]);
        if ($result === false) {
            throw new RuntimeException("Gagal menghapus data {$type}.");
        }
    }

    /**
     * Hapus seluruh berkas anggota pada satu kelompok.
     */
    private function deleteBerkas(int $kelompokId): void
    {
        $this->run(
            'DELETE ba FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?',
            'i',
            [$kelompokId]
        );

        $this->run(
            'UPDATE anggota_kelompok SET status_berkas = "belum" WHERE kelompok_id = ?',
            'i',
            [$kelompokId]
        );
    }
}
