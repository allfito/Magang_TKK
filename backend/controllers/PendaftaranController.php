<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/FileUploader.php';
require_once __DIR__ . '/../models/Kelompok.php';
require_once __DIR__ . '/../models/PendaftaranModel.php';

/**
 * PendaftaranController
 * Menangani seluruh alur pendaftaran magang oleh ketua kelompok.
 * Prinsip OOP: Inheritance (extends BaseController), Composition (memakai FileUploader).
 */
class PendaftaranController extends BaseController
{
    private Kelompok        $kelompokModel;
    private PendaftaranModel $pendaftaranModel;

    public function __construct()
    {
        parent::__construct();
        $this->kelompokModel    = new Kelompok($this->db);
        $this->pendaftaranModel = new PendaftaranModel($this->db);
    }

    // ---------------------------------------------------------------
    // LOKASI MAGANG
    // ---------------------------------------------------------------

    public function submitLokasi(int $userId, array $data): array
    {
        $kelompokId = $this->getKelompokIdOrFail($userId);
        if ($kelompokId === null) {
            return $this->error('Anda belum memiliki kelompok atau bukan ketua.');
        }

        $fields = ['perusahaan', 'nama_pimpinan', 'bidang', 'telepon', 'alamat'];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                return $this->error('Semua field lokasi magang harus diisi.');
            }
        }

        // If exists, delete old one to allow "Edit"
        if ($this->pendaftaranModel->getLokasi($kelompokId)) {
            $this->pendaftaranModel->deleteRegistration($kelompokId, 'lokasi');
        }

        $this->db->begin_transaction();
        try {
            $perusahaanId = $this->pendaftaranModel->createPerusahaan([
                'nama'         => trim($data['perusahaan']),
                'nama_pimpinan'=> trim($data['nama_pimpinan']),
                'bidang'       => trim($data['bidang']),
                'telepon'      => trim($data['telepon']),
                'alamat'       => trim($data['alamat']),
                'latitude'     => '',
                'longitude'    => '',
            ]);

            $this->pendaftaranModel->createLokasi($kelompokId, $perusahaanId);
            $this->db->commit();

            return $this->success('Pendaftaran lokasi magang berhasil dikirim.');
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->error($e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // PROPOSAL
    // ---------------------------------------------------------------

    public function submitProposal(int $userId, string $judul, array $fileData): array
    {
        $kelompokId = $this->getKelompokIdOrFail($userId);
        if ($kelompokId === null) {
            return $this->error('Anda belum memiliki kelompok atau bukan ketua.');
        }

        if (empty($judul)) {
            return $this->error('Judul proposal wajib diisi.');
        }

        $existing = $this->pendaftaranModel->getProposal($kelompokId);
        $filePath = '';

        if ($existing) {
            $filePath = $existing['file_path'];
            $this->pendaftaranModel->deleteRegistration($kelompokId, 'proposal');
        }

        if (!empty($fileData['name']) && $fileData['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploader = FileUploader::forProposal();
            $upload   = $uploader->upload($fileData, 'proposal');

            if (!$upload['status']) {
                return $this->error($upload['message']);
            }

            $filePath = $upload['path'];
        } elseif (empty($filePath)) {
            return $this->error('File proposal wajib diunggah.');
        }

        try {
            $this->pendaftaranModel->createProposal($kelompokId, trim($judul), $filePath);
            return $this->success('Proposal berhasil diunggah.');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // BERKAS ANGGOTA
    // ---------------------------------------------------------------

    public function submitBerkas(int $userId, array $filesData): array
    {
        $kelompokId = $this->getKelompokIdOrFail($userId);
        if ($kelompokId === null) {
            return $this->error('Anda belum memiliki kelompok atau bukan ketua.');
        }

        $anggotaList = $this->pendaftaranModel->getAnggotaKelompok($kelompokId);
        if (empty($anggotaList)) {
            return $this->error('Tidak ada anggota kelompok yang terdaftar.');
        }

        $uploader  = FileUploader::forBerkas();
        $jenisList = ['formulir', 'ktm', 'transkrip', 'pas_foto', 'cv'];
        $uploaded  = 0;

        // Reset any remaining 'rejected' status to 'menunggu' since user is resubmitting
        $this->pendaftaranModel->resetRejectedBerkas($kelompokId);

        foreach ($anggotaList as $index => $anggota) {
            foreach ($jenisList as $jenis) {
                $fieldName = "berkas_{$index}_{$jenis}";

                if (!isset($filesData[$fieldName])) {
                    continue;
                }

                $result = $uploader->upload($filesData[$fieldName], "berkas_{$jenis}");
                if (!$result['status']) {
                    continue;
                }

                // Hapus file lama jika ada
                $oldBerkas = $this->pendaftaranModel->getSingleBerkas($anggota['id'], $jenis);
                if ($oldBerkas && !empty($oldBerkas['file_path'])) {
                    FileUploader::deleteFile($oldBerkas['file_path']);
                }

                if ($this->pendaftaranModel->upsertBerkas($anggota['id'], $jenis, $result['path'])) {
                    $uploaded++;
                }
            }
        }

        if ($uploaded > 0) {
            return $this->success("Berhasil mengunggah {$uploaded} berkas.");
        }

        return $this->error('Tidak ada berkas yang berhasil diunggah. Pastikan format file benar (PDF, JPEG, PNG).');
    }

    // ---------------------------------------------------------------
    // BUKTI DITERIMA
    // ---------------------------------------------------------------

    public function submitBuktiDiterima(int $userId, string $tempatDiterima, array $fileData): array
    {
        $kelompokId = $this->getKelompokIdOrFail($userId);
        if ($kelompokId === null) {
            return $this->error('Anda belum memiliki kelompok atau bukan ketua.');
        }

        if (empty($tempatDiterima)) {
            return $this->error('Tempat diterima wajib diisi.');
        }

        $existing = $this->pendaftaranModel->getBuktiDiterima($kelompokId);
        $filePath = '';

        if ($existing) {
            $filePath = $existing['file_path'];
        }

        // Allow overwrite
        $this->pendaftaranModel->deleteRegistration($kelompokId, 'bukti');

        if (!empty($fileData['name']) && $fileData['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploader = FileUploader::forBukti();
            $upload   = $uploader->upload($fileData, 'bukti');

            if (!$upload['status']) {
                return $this->error($upload['message']);
            }

            $filePath = $upload['path'];
        } elseif (empty($filePath)) {
            return $this->error('Surat penerimaan wajib diunggah.');
        }

        $this->db->begin_transaction();
        try {
            $perusahaanId = $this->pendaftaranModel->findPerusahaanByName($tempatDiterima)
                ?? $this->pendaftaranModel->createPerusahaan([
                    'nama'          => trim($tempatDiterima),
                    'nama_pimpinan' => '',
                    'bidang'        => '',
                    'telepon'       => '',
                    'alamat'        => '',
                ]);

            $this->pendaftaranModel->createBuktiDiterima($kelompokId, $perusahaanId, $filePath);
            $this->db->commit();

            return $this->success('Bukti penerimaan berhasil diunggah.');
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->error($e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // DELETE
    // ---------------------------------------------------------------

    public function deleteRegistration(int $userId, string $type): array
    {
        $kelompokId = $this->getKelompokIdOrFail($userId);
        if ($kelompokId === null) {
            return $this->error('Anda belum memiliki kelompok atau bukan ketua.');
        }

        try {
            $this->pendaftaranModel->deleteRegistration($kelompokId, $type);

            $messages = [
                'lokasi'   => 'Lokasi dihapus. Silakan ajukan ulang.',
                'proposal' => 'Proposal dihapus. Silakan ajukan ulang.',
                'berkas'   => 'Berkas dihapus. Silakan ajukan ulang.',
                'bukti'    => 'Bukti dihapus. Silakan ajukan ulang.',
            ];

            return $this->success($messages[$type] ?? 'Data berhasil dihapus.');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    /**
     * Cari kelompok milik user (sebagai ketua).
     * Mengembalikan kelompokId atau null bila tidak ditemukan.
     */
    private function getKelompokIdOrFail(int $userId): ?int
    {
        $kelompok = $this->kelompokModel->findByKetua($userId);
        return $kelompok ? (int) $kelompok['id'] : null;
    }
}
