<?php

require_once __DIR__ . '/../core/BaseModel.php';

/**
 * KoordinatorModel
 * Model untuk semua operasi data yang dilakukan koordinator.
 * Prinsip OOP: Inheritance (extends BaseModel).
 */
class KoordinatorModel extends BaseModel
{
    /** Tipe verifikasi yang valid beserta SQL-nya. */
    private const VERIFIKASI_MAP = [
        'lokasi'          => 'UPDATE pendaftaran_lokasi SET status_verifikasi = ? WHERE id = ?',
        'proposal'        => 'UPDATE proposal SET status_verifikasi = ? WHERE id = ?',
        'berkas_satuan'   => 'UPDATE berkas_anggota SET status_verifikasi = ? WHERE id = ?',
        'bukti'           => 'UPDATE bukti_diterima SET status_verifikasi = ? WHERE id = ?',
    ];

    // ---------------------------------------------------------------
    // VERIFIKASI
    // ---------------------------------------------------------------

    /**
     * Perbarui status verifikasi dokumen.
     *
     * @return bool True jika berhasil.
     */
    public function updateVerifikasi(string $type, string $action, int $id): bool
    {
        // Verifikasi semua berkas dalam satu kelompok
        if ($type === 'berkas') {
            return $this->verifikasiBerkasKelompok($action, $id);
        }

        // Verifikasi berkas per-mahasiswa (anggota_id)
        if ($type === 'berkas_mahasiswa') {
            return $this->verifikasiBerkasMahasiswa($action, $id);
        }

        if (!isset(self::VERIFIKASI_MAP[$type])) {
            return false;
        }

        $result = $this->run(self::VERIFIKASI_MAP[$type], 'si', [$action, $id]);
        return $result !== false;
    }

    private function verifikasiBerkasKelompok(string $action, int $kelompokId): bool
    {
        $this->run(
            'UPDATE berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id SET ba.status_verifikasi = ? WHERE ak.kelompok_id = ?',
            'si',
            [$action, $kelompokId]
        );

        $berkasStatus = ($action === 'disetujui') ? 'lengkap' : 'belum';
        $result = $this->run(
            'UPDATE anggota_kelompok SET status_berkas = ? WHERE kelompok_id = ?',
            'si',
            [$berkasStatus, $kelompokId]
        );

        return $result !== false;
    }

    private function verifikasiBerkasMahasiswa(string $action, int $anggotaId): bool
    {
        $this->run(
            'UPDATE berkas_anggota SET status_verifikasi = ? WHERE anggota_id = ?',
            'si',
            [$action, $anggotaId]
        );

        $berkasStatus = ($action === 'disetujui') ? 'lengkap' : 'belum';
        $result = $this->run(
            'UPDATE anggota_kelompok SET status_berkas = ? WHERE id = ?',
            'si',
            [$berkasStatus, $anggotaId]
        );

        return $result !== false;
    }

    // ---------------------------------------------------------------
    // DOSEN
    // ---------------------------------------------------------------

    public function findDosenByName(string $nama): ?int
    {
        $row = $this->fetchOne(
            'SELECT id FROM dosen WHERE nama = ? LIMIT 1',
            's',
            [$nama]
        );
        return $row ? (int) $row['id'] : null;
    }

    public function createDosen(string $nama): int
    {
        $id = $this->run(
            'INSERT INTO dosen (nama, created_at) VALUES (?, NOW())',
            's',
            [$nama]
        );
        return (int) $id;
    }

    // ---------------------------------------------------------------
    // PLOTTING
    // ---------------------------------------------------------------

    public function upsertPlotting(int $kelompokId, int $dosenId): bool
    {
        $existing = $this->fetchOne(
            'SELECT id FROM plotting WHERE kelompok_id = ? LIMIT 1',
            'i',
            [$kelompokId]
        );

        if ($existing) {
            $result = $this->run(
                'UPDATE plotting SET dosen_id = ? WHERE id = ?',
                'ii',
                [$dosenId, $existing['id']]
            );
        } else {
            $result = $this->run(
                'INSERT INTO plotting (kelompok_id, dosen_id, created_at) VALUES (?, ?, NOW())',
                'ii',
                [$kelompokId, $dosenId]
            );
        }

        return $result !== false;
    }
}
