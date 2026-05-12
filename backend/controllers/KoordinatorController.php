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

    public function plotDosen(int $kelompokId, string $dosenPembimbing): array
    {
        if ($kelompokId <= 0 || empty($dosenPembimbing)) {
            return $this->error('Dosen pembimbing wajib diisi.');
        }

        try {
            // Cari atau buat data dosen
            $dosenId = $this->koordinatorModel->findDosenByName($dosenPembimbing)
                ?? $this->koordinatorModel->createDosen($dosenPembimbing);

            if ($this->koordinatorModel->upsertPlotting($kelompokId, $dosenId)) {
                return $this->success('Plotting dosen berhasil disimpan.');
            }

            return $this->error('Gagal menyimpan plotting dosen.');
        } catch (Exception $e) {
            return $this->error('Terjadi kesalahan saat memproses plotting.');
        }
    }
}
