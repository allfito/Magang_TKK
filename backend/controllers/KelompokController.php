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

            // Batasi panjang karakter
            $nim = substr($nim, 0, 9);
            $tlp = substr($tlp, 0, 15);

            $valid[] = ['nama' => $nama, 'nim' => $nim, 'no_tlp' => $tlp];
        }
        return $valid;
    }

    /**
     * Edit biodata anggota kelompok.
     */
    public function editAnggota(int $userId, int $anggotaId, int $mahasiswaId, string $nama, string $nim, string $noTlp): array
    {
        $kelompok = $this->kelompokModel->findByKetua($userId);
        if (!$kelompok) {
            return $this->error('Kelompok tidak ditemukan.');
        }

        $nama  = trim($nama);
        $nim   = trim($nim);
        $noTlp = trim($noTlp);

        if ($nama === '' || $nim === '') {
            return $this->error('Nama dan NIM wajib diisi.');
        }
        if (mb_strlen($nim) > 9) {
            return $this->error('NIM maksimal 9 karakter.');
        }
        if (mb_strlen($noTlp) > 15) {
            return $this->error('No telepon maksimal 15 karakter.');
        }

        try {
            $this->kelompokModel->updateMahasiswa($mahasiswaId, $nama, $nim, $noTlp);
            return $this->success('Data anggota berhasil diperbarui.');
        } catch (Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Hapus anggota dari kelompok.
     * Ketua (peran='ketua') tidak boleh dihapus.
     * Jumlah minimal anggota setelah hapus adalah 3.
     */
    public function hapusAnggota(int $userId, int $anggotaId): array
    {
        $kelompok = $this->kelompokModel->findByKetua($userId);
        if (!$kelompok) {
            return $this->error('Kelompok tidak ditemukan.');
        }
        $kelompokId = (int) $kelompok['id'];

        // Pastikan anggota ini ada di kelompok milik user dan bukan ketua
        $row = $this->fetchOne(
            'SELECT ak.id, ak.peran FROM anggota_kelompok ak WHERE ak.id = ? AND ak.kelompok_id = ?',
            'ii',
            [$anggotaId, $kelompokId]
        );
        if (!$row) {
            return $this->error('Anggota tidak ditemukan dalam kelompok Anda.');
        }
        if ($row['peran'] === 'ketua') {
            return $this->error('Ketua kelompok tidak dapat dihapus.');
        }

        $totalAnggota = $this->kelompokModel->countAnggota($kelompokId);
        if ($totalAnggota <= 3) {
            return $this->error('Kelompok minimal harus memiliki 3 anggota.');
        }

        try {
            $this->kelompokModel->removeAnggota($anggotaId, $kelompokId);
            return $this->success('Anggota berhasil dihapus dari kelompok.');
        } catch (Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Tambah anggota ke kelompok yang sudah ada.
     * Maksimal anggota adalah 4.
     */
    public function tambahAnggotaExisting(int $userId, string $nama, string $nim, string $noTlp): array
    {
        $kelompok = $this->kelompokModel->findByKetua($userId);
        if (!$kelompok) {
            return $this->error('Kelompok tidak ditemukan atau Anda bukan ketua.');
        }

        $kelompokId = (int) $kelompok['id'];
        $totalAnggota = $this->kelompokModel->countAnggota($kelompokId);

        if ($totalAnggota >= 4) {
            return $this->error('Kelompok sudah penuh (maksimal 4 anggota).');
        }

        $nama  = trim($nama);
        $nim   = trim($nim);
        $noTlp = trim($noTlp);

        if ($nama === '' || $nim === '') {
            return $this->error('Nama dan NIM wajib diisi.');
        }
        if (mb_strlen($nim) > 9) {
            return $this->error('NIM maksimal 9 karakter.');
        }
        if (mb_strlen($noTlp) > 15) {
            return $this->error('No telepon maksimal 15 karakter.');
        }

        $this->db->begin_transaction();
        try {
            $mahasiswa   = $this->mahasiswaModel->findByNim($nim);
            $mahasiswaId = $mahasiswa
                ? (int) $mahasiswa['id']
                : $this->mahasiswaModel->create($nim, $nama, $noTlp);

            // Cek apakah mahasiswa sudah ada di kelompok ini
            $row = $this->fetchOne(
                'SELECT id FROM anggota_kelompok WHERE mahasiswa_id = ? AND kelompok_id = ?',
                'ii',
                [$mahasiswaId, $kelompokId]
            );

            if ($row) {
                $this->db->rollback();
                return $this->error('Mahasiswa ini sudah ada dalam kelompok Anda.');
            }

            $this->kelompokModel->addAnggota($kelompokId, $mahasiswaId, 'anggota');

            $this->db->commit();
            return $this->success('Anggota berhasil ditambahkan ke kelompok.');
        } catch (Throwable $e) {
            $this->db->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * Ambil satu baris dari DB (helper inline).
     */
    private function fetchOne(string $sql, string $types, array $params): ?array
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }
}
