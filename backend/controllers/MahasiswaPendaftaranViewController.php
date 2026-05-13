<?php

require_once __DIR__ . '/../core/Database.php';

class MahasiswaPendaftaranViewController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getPendaftaranData(int $userId): array
    {
        $data = [
            'kelompokId' => null,
            'anggotaList' => [],
            'lokasi' => null,
            'proposal' => null,
            'bukti' => null,
            'plotting' => null,
            'berkasData' => [],
            'berkasFilePaths' => [],
            'berkasStatusMap' => [],
            'berkasUploadDate' => null,
            'berkasStatus' => 'belum',
        ];

        $stmt = $this->db->prepare('SELECT id FROM kelompok WHERE ketua_user_id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        
        if ($r) {
            $kelId = (int) $r['id'];
            $data['kelompokId'] = $kelId;

            $stmt = $this->db->prepare('SELECT ak.id, m.nama, ak.peran FROM anggota_kelompok ak JOIN mahasiswa m ON ak.mahasiswa_id = m.id WHERE ak.kelompok_id = ? ORDER BY ak.peran ASC, ak.created_at ASC');
            $stmt->bind_param('i', $kelId);
            $stmt->execute();
            $data['anggotaList'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $stmt = $this->db->prepare('SELECT p.nama AS perusahaan, p.nama_pimpinan, p.bidang, p.telepon, p.alamat, p.latitude, p.longitude, pl.status_verifikasi, pl.updated_at, pl.catatan FROM pendaftaran_lokasi pl JOIN perusahaan p ON pl.perusahaan_id = p.id WHERE pl.kelompok_id = ? LIMIT 1');
            $stmt->bind_param('i', $kelId);
            $stmt->execute();
            $data['lokasi'] = $stmt->get_result()->fetch_assoc();

            $stmt = $this->db->prepare('SELECT * FROM proposal WHERE kelompok_id = ? LIMIT 1');
            $stmt->bind_param('i', $kelId);
            $stmt->execute();
            $data['proposal'] = $stmt->get_result()->fetch_assoc();

            $stmt = $this->db->prepare('SELECT bd.id, p.nama AS tempat_diterima, bd.file_path, bd.status_verifikasi FROM bukti_diterima bd JOIN perusahaan p ON bd.perusahaan_id = p.id WHERE bd.kelompok_id = ? LIMIT 1');
            $stmt->bind_param('i', $kelId);
            $stmt->execute();
            $data['bukti'] = $stmt->get_result()->fetch_assoc();

            $stmt = $this->db->prepare('SELECT pl.*, d.nama AS dosen_pembimbing FROM plotting pl JOIN dosen d ON pl.dosen_id = d.id WHERE pl.kelompok_id = ? LIMIT 1');
            $stmt->bind_param('i', $kelId);
            $stmt->execute();
            $data['plotting'] = $stmt->get_result()->fetch_assoc();

            $bStmt = $this->db->prepare('SELECT ba.anggota_id, ba.jenis_berkas, ba.file_path, ba.status_verifikasi, ba.created_at FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
            $bStmt->bind_param('i', $kelId);
            $bStmt->execute();
            foreach ($bStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $bRow) {
                $data['berkasData'][$bRow['anggota_id']][$bRow['jenis_berkas']] = basename($bRow['file_path']);
                $data['berkasFilePaths'][$bRow['anggota_id']][$bRow['jenis_berkas']] = $bRow['file_path'];
                $data['berkasStatusMap'][$bRow['anggota_id']][$bRow['jenis_berkas']] = $bRow['status_verifikasi'];
                if (!$data['berkasUploadDate'] || $bRow['created_at'] > $data['berkasUploadDate']) {
                    $data['berkasUploadDate'] = $bRow['created_at'];
                }
            }

            $stmt = $this->db->prepare('SELECT COUNT(*) AS total, SUM(ba.status_verifikasi = "disetujui") AS approved, SUM(ba.status_verifikasi = "ditolak") AS rejected FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
            $stmt->bind_param('i', $kelId);
            $stmt->execute();
            $r2 = $stmt->get_result()->fetch_assoc();
            if ($r2 && $r2['total'] > 0) {
                if ($r2['rejected'] > 0) $data['berkasStatus'] = 'ditolak';
                elseif ($r2['approved'] == $r2['total']) $data['berkasStatus'] = 'disetujui';
                else $data['berkasStatus'] = 'menunggu';
            }
        }

        return $data;
    }
}
