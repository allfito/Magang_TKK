<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Kelompok.php';
require_once __DIR__ . '/../models/Mahasiswa.php';

/**
 * KelompokController
 * Menangani pembuatan kelompok magang oleh ketua.
 * Prinsip OOP: Inheritance (extends BaseController).
 */
class KelompokController extends BaseController
{
    private Kelompok  $kelompokModel;
    private Mahasiswa $mahasiswaModel;

    public function __construct()
    {
        parent::__construct();
        $this->kelompokModel  = new Kelompok($this->db);
        $this->mahasiswaModel = new Mahasiswa($this->db);
    }

    /**
     * Buat kelompok beserta anggota-anggotanya.
     */
    public function createKelompok(int $userId, string $namaKelompok, array $anggotaRaw): array
    {
        if (trim($namaKelompok) === '') {
            return $this->error('Nama kelompok wajib diisi.');
        }

        $anggotaValid = $this->filterAnggota($anggotaRaw);

        if (count($anggotaValid) < 3 || count($anggotaValid) > 4) {
            return $this->error('Kelompok harus terdiri dari 3 sampai 4 anggota (minimal ketua + 2 anggota).');
        }

        if ($this->kelompokModel->findByKetua($userId)) {
            return $this->error('Anda sudah memiliki kelompok.');
        }

        $this->db->begin_transaction();
        try {
            $kelompokId = $this->kelompokModel->create(trim($namaKelompok), $userId);

            foreach ($anggotaValid as $index => $member) {
                $peran = ($index === 0) ? 'ketua' : 'anggota';

                $mahasiswa   = $this->mahasiswaModel->findByNim($member['nim']);
                $mahasiswaId = $mahasiswa
                    ? (int) $mahasiswa['id']
                    : $this->mahasiswaModel->create($member['nim'], $member['nama'], $member['no_tlp']);

                $this->kelompokModel->addAnggota($kelompokId, $mahasiswaId, $peran);
            }

            $this->db->commit();
            return $this->success('Kelompok berhasil dibuat.');
        } catch (Throwable $e) {
            $this->db->rollback();
            return $this->error($e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    /**
     * Saring dan validasi data anggota dari input form.
     * Hanya anggota dengan nama dan NIM lengkap yang diproses.
     */
    private function filterAnggota(array $anggotaRaw): array
    {
        $valid = [];
        foreach ($anggotaRaw as $member) {
            $nama = trim($member['nama']  ?? '');
            $nim  = trim($member['nim']   ?? '');
            $tlp  = trim($member['no_tlp'] ?? '');

            if ($nama === '' || $nim === '') {
                continue;
            }

            $valid[] = ['nama' => $nama, 'nim' => $nim, 'no_tlp' => $tlp];
        }
        return $valid;
    }
}
