<?php

require_once __DIR__ . '/../core/Database.php';

class MahasiswaKelompokViewController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function formatName(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }
        return ucwords(strtolower(trim($name)));
    }

    public function getKelompokData(int $userId): array
    {
        $data = [
            'kelompok' => null,
            'anggotaList' => [],
        ];

        $stmt = $this->db->prepare('SELECT k.id, k.nama FROM kelompok k WHERE k.ketua_user_id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $kel = $result->fetch_assoc();
            $kel['nama'] = $this->formatName($kel['nama']);
            $data['kelompok'] = $kel;
            
            $stmt = $this->db->prepare('SELECT ak.id as anggota_id, m.id as mahasiswa_id, m.nama, m.nim, m.no_tlp, ak.peran, ak.status_berkas FROM anggota_kelompok ak JOIN mahasiswa m ON ak.mahasiswa_id = m.id WHERE ak.kelompok_id = ? ORDER BY ak.peran ASC, ak.created_at ASC');
            $stmt->bind_param('i', $kel['id']);
            $stmt->execute();
            $anggota = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach ($anggota as &$ang) {
                $ang['nama'] = $this->formatName($ang['nama']);
            }
            $data['anggotaList'] = $anggota;
        }

        return $data;
    }
}
