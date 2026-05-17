<?php

require_once __DIR__ . '/../core/BaseController.php';

/**
 * MahasiswaDashboardController
 * Menyediakan data untuk halaman dashboard mahasiswa.
 * Prinsip OOP: Inheritance (extends BaseController).
 */
class MahasiswaDashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function formatName(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }
        return ucwords(strtolower(trim($name)));
    }

    /**
     * Kumpulkan semua data yang dibutuhkan dashboard mahasiswa.
     */
    public function getDashboardData(int $userId): array
    {
        $data = [
            'kelompok'      => null,
            'kelompokId'    => null,
            'isKetua'       => false,
            'anggotaList'   => [],
            'lokasiStatus'  => 'belum',
            'proposalStatus'=> 'belum',
            'berkasStatus'  => 'belum',
            'buktiStatus'   => 'belum',
            'plottingStatus'=> 'belum',
        ];

        $kelompok = $this->findKelompokByUser($userId);

        if (!$kelompok) {
            return $data;
        }

        $data['kelompok']   = $kelompok;
        $data['kelompokId'] = (int) $kelompok['id'];
        $data['isKetua']    = ((int) $kelompok['ketua_user_id'] === $userId);

        $kelompokId = $data['kelompokId'];

        $data['anggotaList']    = $this->getAnggotaList($kelompokId);
        $data['lokasiStatus']   = $this->getStatusField('pendaftaran_lokasi', 'kelompok_id', $kelompokId, 'menunggu') ?? 'belum';
        $data['proposalStatus'] = $this->getStatusField('proposal', 'kelompok_id', $kelompokId, 'menunggu') ?? 'belum';
        $data['berkasStatus']   = $this->getBerkasStatus($kelompokId);
        $data['buktiStatus']    = $this->getStatusField('bukti_diterima', 'kelompok_id', $kelompokId, 'menunggu') ?? 'belum';
        $data['plottingStatus'] = $this->getPlottingStatus($kelompokId);

        return $data;
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    /**
     * Cari kelompok yang dimiliki atau diikuti oleh user.
     */
    private function findKelompokByUser(int $userId): ?array
    {
        // Cek sebagai ketua
        $stmt = $this->db->prepare(
            'SELECT k.id, k.nama, k.ketua_user_id, u.nama AS ketua_nama
             FROM kelompok k
             JOIN user u ON k.ketua_user_id = u.id
             WHERE k.ketua_user_id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $row['nama'] = $this->formatName($row['nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
        }

        return $row ?: null;
    }

    private function getAnggotaList(int $kelompokId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.nama, m.nim, ak.peran, ak.status_berkas
             FROM anggota_kelompok ak
             JOIN mahasiswa m ON ak.mahasiswa_id = m.id
             WHERE ak.kelompok_id = ?
             ORDER BY ak.peran ASC, ak.created_at ASC'
        );
        $stmt->bind_param('i', $kelompokId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['nama'] = $this->formatName($row['nama']);
        }
        return $rows;
    }

    /**
     * Ambil kolom status_verifikasi dari sebuah tabel pendaftaran.
     * Mengembalikan nilai status atau null jika belum ada data.
     */
    private function getStatusField(string $table, string $column, int $id, string $default): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT status_verifikasi FROM `{$table}` WHERE `{$column}` = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['status_verifikasi'] : null;
    }

    /**
     * Hitung status berkas: disetujui hanya jika semua berkas disetujui.
     */
    private function getBerkasStatus(int $kelompokId): string
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total,
                     SUM(ba.status_verifikasi = "disetujui") AS approved
             FROM berkas_anggota ba
             JOIN anggota_kelompok ak ON ba.anggota_id = ak.id
             WHERE ak.kelompok_id = ?'
        );
        $stmt->bind_param('i', $kelompokId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || (int) $row['total'] === 0) {
            return 'belum';
        }

        return ($row['approved'] == $row['total']) ? 'disetujui' : 'menunggu';
    }

    private function getPlottingStatus(int $kelompokId): string
    {
        $stmt = $this->db->prepare('SELECT id FROM plotting WHERE kelompok_id = ? LIMIT 1');
        $stmt->bind_param('i', $kelompokId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? 'selesai' : 'belum';
    }
}
