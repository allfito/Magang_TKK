<?php

require_once __DIR__ . '/../core/BaseController.php';

class KoordinatorViewController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Helper to format name to Title Case (Capitalize each word)
     */
    private function formatName(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }
        // Protect placeholder values like '-'
        if (trim($name) === '-') {
            return '-';
        }
        return ucwords(strtolower(trim($name)));
    }

    public function getActiveGroupCount(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM kelompok";
        $row = $this->db->query($sql)->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getPendingLocationCount(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM pendaftaran_lokasi WHERE status_verifikasi = 'menunggu'";
        $row = $this->db->query($sql)->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getPendingProposalCount(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM proposal WHERE status_verifikasi = 'menunggu'";
        $row = $this->db->query($sql)->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getPendingBerkasCount(): int
    {
        $sql = "SELECT COUNT(DISTINCT a.kelompok_id) AS total
                FROM anggota_kelompok a
                JOIN berkas_anggota b ON a.id = b.anggota_id
                WHERE b.status_verifikasi = 'menunggu'";
        $row = $this->db->query($sql)->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getPendingBuktiCount(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM bukti_diterima WHERE status_verifikasi = 'menunggu'";
        $row = $this->db->query($sql)->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getGroupsPendingVerification(): array
    {
        $sql = "SELECT DISTINCT k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
                CASE
                    WHEN EXISTS(SELECT 1 FROM pendaftaran_lokasi pl WHERE pl.kelompok_id = k.id AND pl.status_verifikasi = 'menunggu') THEN 'Lokasi Magang'
                    WHEN EXISTS(SELECT 1 FROM proposal pr WHERE pr.kelompok_id = k.id AND pr.status_verifikasi = 'menunggu') THEN 'Proposal'
                    WHEN EXISTS(SELECT 1 FROM anggota_kelompok a JOIN berkas_anggota b ON a.id = b.anggota_id WHERE a.kelompok_id = k.id AND b.status_verifikasi = 'menunggu') THEN 'Berkas'
                    WHEN EXISTS(SELECT 1 FROM bukti_diterima bd WHERE bd.kelompok_id = k.id AND bd.status_verifikasi = 'menunggu') THEN 'Bukti Diterima'
                    ELSE 'Info Kelompok'
                END AS jenis_verifikasi
                FROM kelompok k
                JOIN user u ON k.ketua_user_id = u.id
                WHERE EXISTS(SELECT 1 FROM pendaftaran_lokasi pl WHERE pl.kelompok_id = k.id AND pl.status_verifikasi = 'menunggu')
                   OR EXISTS(SELECT 1 FROM proposal pr WHERE pr.kelompok_id = k.id AND pr.status_verifikasi = 'menunggu')
                   OR EXISTS(SELECT 1 FROM anggota_kelompok a JOIN berkas_anggota b ON a.id = b.anggota_id WHERE a.kelompok_id = k.id AND b.status_verifikasi = 'menunggu')
                   OR EXISTS(SELECT 1 FROM bukti_diterima bd WHERE bd.kelompok_id = k.id AND bd.status_verifikasi = 'menunggu')
                ORDER BY k.nama ASC";
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
        }
        return $rows;
    }

    public function getMembersCount(int $kelompokId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM anggota_kelompok WHERE kelompok_id = ?");
        $stmt->bind_param('i', $kelompokId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getGroupsForLocationVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        $orderBy = "pl.created_at DESC";
        switch($sortBy) {
            case 'tanggal_terlama': $orderBy = "pl.created_at ASC"; break;
            case 'nama_a': $orderBy = "kelompok_nama ASC"; break;
            case 'nama_z': $orderBy = "kelompok_nama DESC"; break;
            case 'ketua_a': $orderBy = "ketua_nama ASC"; break;
            case 'ketua_z': $orderBy = "ketua_nama DESC"; break;
            case 'status_menunggu': $orderBy = "CASE WHEN pl.status_verifikasi = 'menunggu' THEN 0 ELSE 1 END ASC, pl.created_at DESC"; break;
        }
        
        $sql = "SELECT pl.id AS lokasi_id, k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
                p.nama AS perusahaan, p.bidang, p.alamat, p.nama_pimpinan, p.telepon, pl.status_verifikasi, pl.created_at
                FROM pendaftaran_lokasi pl
                JOIN perusahaan p ON pl.perusahaan_id = p.id
                JOIN kelompok k ON pl.kelompok_id = k.id
                JOIN user u ON k.ketua_user_id = u.id
                ORDER BY " . $orderBy;
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
            $row['perusahaan'] = $this->formatName($row['perusahaan']);
            $row['nama_pimpinan'] = $this->formatName($row['nama_pimpinan']);
        }
        return $rows;
    }

    public function getGroupsForProposalVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        $orderBy = "pr.created_at DESC";
        switch($sortBy) {
            case 'tanggal_terlama': $orderBy = "pr.created_at ASC"; break;
            case 'nama_a': $orderBy = "kelompok_nama ASC"; break;
            case 'nama_z': $orderBy = "kelompok_nama DESC"; break;
            case 'ketua_a': $orderBy = "ketua_nama ASC"; break;
            case 'ketua_z': $orderBy = "ketua_nama DESC"; break;
            case 'status_menunggu': $orderBy = "CASE WHEN pr.status_verifikasi = 'menunggu' THEN 0 ELSE 1 END ASC, pr.created_at DESC"; break;
        }
        
        $sql = "SELECT pr.id AS proposal_id, k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
                pr.file_path, pr.status_verifikasi, pr.created_at
                FROM proposal pr
                JOIN kelompok k ON pr.kelompok_id = k.id
                JOIN user u ON k.ketua_user_id = u.id
                ORDER BY " . $orderBy;
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
        }
        return $rows;
    }

    public function getGroupsForBerkasVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        $orderBy = "tanggal_upload DESC";
        switch($sortBy) {
            case 'tanggal_terlama': $orderBy = "tanggal_upload ASC"; break;
            case 'nama_a': $orderBy = "kelompok_nama ASC"; break;
            case 'nama_z': $orderBy = "kelompok_nama DESC"; break;
            case 'ketua_a': $orderBy = "ketua_nama ASC"; break;
            case 'ketua_z': $orderBy = "ketua_nama DESC"; break;
            case 'status_menunggu': $orderBy = "CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END ASC, tanggal_upload DESC"; break;
        }
        
        $sql = "SELECT k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
                COUNT(b.id) AS jumlah_berkas,
                MAX(b.created_at) AS tanggal_upload,
                CASE
                    WHEN SUM(b.status_verifikasi = 'menunggu') > 0 THEN 'menunggu'
                    WHEN SUM(b.status_verifikasi = 'ditolak') > 0 THEN 'ditolak'
                    ELSE 'disetujui'
                END AS status
                FROM anggota_kelompok a
                JOIN berkas_anggota b ON a.id = b.anggota_id
                JOIN kelompok k ON a.kelompok_id = k.id
                JOIN user u ON k.ketua_user_id = u.id
                GROUP BY k.id, k.nama, u.id, u.nama
                ORDER BY " . $orderBy;
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
        }
        return $rows;
    }

    public function getBerkasByGroup(int $kelompokId): array
    {
        $sql = "SELECT m.nama AS anggota_nama, m.nim, a.id AS anggota_id, b.id AS berkas_id, b.jenis_berkas, b.file_path, b.status_verifikasi
                FROM anggota_kelompok a
                JOIN mahasiswa m ON a.mahasiswa_id = m.id
                JOIN berkas_anggota b ON a.id = b.anggota_id
                WHERE a.kelompok_id = ?
                ORDER BY CASE WHEN a.peran = 'ketua' THEN 0 ELSE 1 END ASC, m.nama ASC, b.jenis_berkas ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $kelompokId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['anggota_nama'] = $this->formatName($row['anggota_nama']);
        }
        return $rows;
    }

    public function getGroupsForBuktiVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        $orderBy = "bd.created_at DESC";
        switch($sortBy) {
            case 'tanggal_terlama': $orderBy = "bd.created_at ASC"; break;
            case 'nama_a': $orderBy = "kelompok_nama ASC"; break;
            case 'nama_z': $orderBy = "kelompok_nama DESC"; break;
            case 'ketua_a': $orderBy = "ketua_nama ASC"; break;
            case 'ketua_z': $orderBy = "ketua_nama DESC"; break;
            case 'status_menunggu': $orderBy = "CASE WHEN bd.status_verifikasi = 'menunggu' THEN 0 ELSE 1 END ASC, bd.created_at DESC"; break;
        }
        
        $sql = "SELECT bd.id AS bukti_id, k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
                p.nama AS tempat_diterima, bd.file_path, bd.status_verifikasi, bd.created_at
                FROM bukti_diterima bd
                JOIN perusahaan p ON bd.perusahaan_id = p.id
                JOIN kelompok k ON bd.kelompok_id = k.id
                JOIN user u ON k.ketua_user_id = u.id
                ORDER BY " . $orderBy;
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
            $row['tempat_diterima'] = $this->formatName($row['tempat_diterima']);
        }
        return $rows;
    }

    public function getGroupsForPlotting(string $sortBy = 'nama_a'): array
    {
        $orderBy = "k.nama ASC";
        switch($sortBy) {
            case 'nama_z': $orderBy = "k.nama DESC"; break;
            case 'ketua_a': $orderBy = "u.nama ASC"; break;
            case 'ketua_z': $orderBy = "u.nama DESC"; break;
            case 'status_selesai': $orderBy = "CASE WHEN pl.id IS NULL THEN 1 ELSE 0 END ASC, k.nama ASC"; break;
        }
        
        $sql = "SELECT k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
                COALESCE(per.nama, '') AS lokasi, COALESCE(d.nama, '') AS dosen_pembimbing,
                pl.id AS plotting_id, d.id AS dosen_id,
                COUNT(a.id) AS anggota_count,
                CASE WHEN pl.id IS NULL THEN 'menunggu' ELSE 'selesai' END AS status
                FROM kelompok k
                LEFT JOIN plotting pl ON k.id = pl.kelompok_id
                LEFT JOIN dosen d ON pl.dosen_id = d.id
                LEFT JOIN pendaftaran_lokasi plok ON k.id = plok.kelompok_id
                LEFT JOIN perusahaan per ON plok.perusahaan_id = per.id
                LEFT JOIN anggota_kelompok a ON a.kelompok_id = k.id
                JOIN user u ON k.ketua_user_id = u.id
                GROUP BY k.id, k.nama, u.id, u.nama, per.nama, d.nama, pl.id, d.id
                ORDER BY " . $orderBy;
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
            $row['lokasi'] = $this->formatName($row['lokasi']);
            $row['dosen_pembimbing'] = $this->formatName($row['dosen_pembimbing']);
        }
        return $rows;
    }

    public function getPlottingSummary(): array
    {
        $sql = "SELECT d.nama AS dosen_pembimbing, COUNT(*) AS jumlah_kelompok
                FROM plotting p
                JOIN dosen d ON p.dosen_id = d.id
                GROUP BY d.nama
                ORDER BY jumlah_kelompok DESC";
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['dosen_pembimbing'] = $this->formatName($row['dosen_pembimbing']);
        }
        return $rows;
    }

    public function getCompleteGroupsData(string $sortBy = 'nama_a'): array
    {
        $orderBy = "k.nama ASC";
        switch($sortBy) {
            case 'nama_z': $orderBy = "k.nama DESC"; break;
            case 'ketua_a': $orderBy = "MAX(u.nama) ASC"; break;
            case 'ketua_z': $orderBy = "MAX(u.nama) DESC"; break;
            case 'jumlah_mhs': $orderBy = "COUNT(DISTINCT a.id) DESC"; break;
        }
        
        $sql = "SELECT 
                    k.id AS kelompok_id,
                    k.nama AS kelompok_nama,
                    COUNT(DISTINCT a.id) AS jumlah_mhs,
                    GROUP_CONCAT(m.nama ORDER BY CASE WHEN a.peran = 'ketua' THEN 0 ELSE 1 END ASC, m.nama ASC SEPARATOR ', ') AS nama_mahasiswa,
                    GROUP_CONCAT(m.nim ORDER BY CASE WHEN a.peran = 'ketua' THEN 0 ELSE 1 END ASC, m.nama ASC SEPARATOR ', ') AS nim,
                    GROUP_CONCAT(m.no_tlp ORDER BY CASE WHEN a.peran = 'ketua' THEN 0 ELSE 1 END ASC, m.nama ASC SEPARATOR ', ') AS no_hp,
                    COALESCE(MAX(per.nama), '-') AS lokasi_magang,
                    COALESCE(MAX(per.alamat), '-') AS alamat_lengkap,
                    COALESCE(MAX(per.latitude), '') AS latitude,
                    COALESCE(MAX(per.longitude), '') AS longitude,
                    COALESCE(MAX(pl.status_verifikasi), 'belum') AS status_lokasi,
                    COALESCE(MAX(p.status_verifikasi), 'belum_upload') AS status_proposal,
                    MAX(u.nama) AS ketua_nama,
                    MAX(u.no_tlp) AS kontak_ketua,
                    MAX(u.email) AS email_ketua,
                    COALESCE(MAX(per.nama_pimpinan), '-') AS cp_nama,
                    COALESCE(MAX(per.telepon), '-') AS cp_tlp
                FROM kelompok k
                LEFT JOIN anggota_kelompok a ON k.id = a.kelompok_id
                LEFT JOIN mahasiswa m ON a.mahasiswa_id = m.id
                LEFT JOIN pendaftaran_lokasi pl ON k.id = pl.kelompok_id
                LEFT JOIN perusahaan per ON pl.perusahaan_id = per.id
                LEFT JOIN proposal p ON k.id = p.kelompok_id
                JOIN user u ON k.ketua_user_id = u.id
                GROUP BY k.id, k.nama
                ORDER BY " . $orderBy;
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['kelompok_nama'] = $this->formatName($row['kelompok_nama']);
            $row['ketua_nama'] = $this->formatName($row['ketua_nama']);
            $row['lokasi_magang'] = $this->formatName($row['lokasi_magang']);
            $row['cp_nama'] = $this->formatName($row['cp_nama']);
            
            if (!empty($row['nama_mahasiswa']) && $row['nama_mahasiswa'] !== '-') {
                $names = explode(', ', $row['nama_mahasiswa']);
                $formatted = array_map(function($n) {
                    return $this->formatName($n);
                }, $names);
                $row['nama_mahasiswa'] = implode(', ', $formatted);
            }
        }
        return $rows;
    }

    public function getAllDosen(): array
    {
        $sql = "SELECT id, nip, nama, no_tlp FROM dosen ORDER BY nama ASC";
        $result = $this->db->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($rows as &$row) {
            $row['nama'] = $this->formatName($row['nama']);
        }
        return $rows;
    }

    public function getGroupMembers(int $kelompokId): array
    {
        $sql = "SELECT m.nama, m.nim, ak.peran
                FROM anggota_kelompok ak
                JOIN mahasiswa m ON ak.mahasiswa_id = m.id
                WHERE ak.kelompok_id = ?
                ORDER BY CASE WHEN ak.peran = 'ketua' THEN 0 ELSE 1 END ASC, m.nama ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $kelompokId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['nama'] = $this->formatName($row['nama']);
        }
        return $rows;
    }
}
