<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/KoordinatorModel.php';

/**
 * KoordinatorController
 * Menangani aksi verifikasi dan plotting oleh koordinator.
 * Prinsip OOP: Inheritance (extends BaseController).
 */
class KoordinatorController extends BaseController
{
    private KoordinatorModel $koordinatorModel;

    /** Peta tipe verifikasi ke halaman redirect. */
    private const REDIRECT_MAP = [
        'lokasi'          => 'verifikasi_lokasi.php',
        'proposal'        => 'verifikasi_proposal.php',
        'berkas'          => 'verifikasi_berkas.php',
        'berkas_mahasiswa'=> 'verifikasi_berkas.php',
        'berkas_satuan'   => 'verifikasi_berkas.php',
        'bukti'           => 'verifikasi_bukti.php',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->koordinatorModel = new KoordinatorModel($this->db);
    }

    // ---------------------------------------------------------------
    // VERIFIKASI DOKUMEN
    // ---------------------------------------------------------------

    public function verifyDocument(string $action, string $type, int $id): array
    {
        if (!in_array($action, ['disetujui', 'ditolak'], true) || $id <= 0) {
            return $this->error('Aksi tidak valid.', ['redirect' => 'dashboard.php']);
        }

        if (!isset(self::REDIRECT_MAP[$type])) {
            return $this->error('Tipe verifikasi tidak valid.', ['redirect' => 'dashboard.php']);
        }

        $redirect = self::REDIRECT_MAP[$type];
        $success  = $this->koordinatorModel->updateVerifikasi($type, $action, $id);

        if ($success) {
            return $this->success('Verifikasi berhasil diperbarui.', ['redirect' => $redirect]);
        }

        return $this->error('Gagal memperbarui verifikasi.', ['redirect' => $redirect]);
    }

    // ---------------------------------------------------------------
    // PLOTTING DOSEN
    // ---------------------------------------------------------------

    public function plotDosen(int $kelompokId, string $dosenPembimbing, int $korbidId): array
    {
        if ($kelompokId <= 0 || empty(trim($dosenPembimbing))) {
            return $this->error('Dosen pembimbing wajib diisi.');
        }

        try {
            // Cari data dosen pembimbing
            $dosenId = $this->koordinatorModel->findDosenByName($dosenPembimbing);

            if (!$dosenId) {
                return $this->error('Dosen "' . $dosenPembimbing . '" belum terdaftar. Silakan tambahkan dosen terlebih dahulu.');
            }

            if ($this->koordinatorModel->upsertPlotting($kelompokId, $dosenId, $korbidId)) {
                return $this->success('Plotting dosen berhasil disimpan.');
            }

            return $this->error('Gagal menyimpan plotting dosen.');
        } catch (Exception $e) {
            return $this->error('Terjadi kesalahan saat memproses plotting.');
        }
    }

    public function deletePlotting(int $kelompokId): array
    {
        if ($kelompokId <= 0) {
            return $this->error('ID kelompok tidak valid.');
        }

        if ($this->koordinatorModel->deletePlotting($kelompokId)) {
            return $this->success('Plotting dosen pembimbing berhasil dihapus.');
        }

        return $this->error('Gagal menghapus plotting dosen pembimbing.');
    }

    public function deleteDosen(int $dosenId): array
    {
        if ($dosenId <= 0) {
            return $this->error('ID dosen tidak valid.');
        }

        if ($this->koordinatorModel->checkDosenHasPlotting($dosenId)) {
            return $this->error('Dosen pembimbing tidak dapat dihapus karena sedang aktif membimbing kelompok magang.');
        }

        if ($this->koordinatorModel->deleteDosen($dosenId)) {
            return $this->success('Dosen pembimbing berhasil dihapus.');
        }

        return $this->error('Gagal menghapus dosen pembimbing.');
    }
}
