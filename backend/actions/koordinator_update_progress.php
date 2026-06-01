<?php
require_once __DIR__ . '/../helpers/KoordinatorHelper.php';
KoordinatorHelper::requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$kelompokId = (int) ($_POST['kelompok_id'] ?? 0);
$progress    = trim($_POST['progress'] ?? '');

$validProgress = [
    'DI TOLAK',
    'REVISI TEMPAT',
    'Pengajuan Tempat',
    'ACC Pembuatan Proposal',
    'Pengurusan Surat Pengantar',
    'Pengiriman Proposal',
    'Surat Penerimaan Magang',
    'Pengajuan di Tolak Lokasi',
    'Ditolak Perusahaan'
];

if ($kelompokId <= 0 || !in_array($progress, $validProgress)) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak valid.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $db->begin_transaction();
    
    $stmt = $db->prepare("UPDATE kelompok SET status_progress = ? WHERE id = ?");
    $stmt->bind_param('si', $progress, $kelompokId);
    $stmt->execute();
    
    // Sync verification status tables
    if ($progress === 'ACC Pembuatan Proposal') {
        $db->query("UPDATE pendaftaran_lokasi SET status_verifikasi = 'disetujui' WHERE kelompok_id = $kelompokId");
    } elseif ($progress === 'Pengurusan Surat Pengantar') {
        // Also ensure location is disetujui
        $db->query("UPDATE pendaftaran_lokasi SET status_verifikasi = 'disetujui' WHERE kelompok_id = $kelompokId");
        $db->query("UPDATE proposal SET status_verifikasi = 'disetujui' WHERE kelompok_id = $kelompokId");
    } elseif ($progress === 'Surat Penerimaan Magang') {
        // Also ensure location and proposal are disetujui
        $db->query("UPDATE pendaftaran_lokasi SET status_verifikasi = 'disetujui' WHERE kelompok_id = $kelompokId");
        $db->query("UPDATE proposal SET status_verifikasi = 'disetujui' WHERE kelompok_id = $kelompokId");
        $db->query("UPDATE bukti_diterima SET status_verifikasi = 'disetujui' WHERE kelompok_id = $kelompokId");
    } elseif ($progress === 'REVISI TEMPAT' || $progress === 'DI TOLAK' || $progress === 'Pengajuan di Tolak Lokasi' || $progress === 'Ditolak Perusahaan') {
        $db->query("UPDATE pendaftaran_lokasi SET status_verifikasi = 'ditolak' WHERE kelompok_id = $kelompokId");
    } elseif ($progress === 'Pengajuan Tempat') {
        $db->query("UPDATE pendaftaran_lokasi SET status_verifikasi = 'menunggu' WHERE kelompok_id = $kelompokId");
    } elseif ($progress === 'Pengiriman Proposal') {
        $db->query("UPDATE proposal SET status_verifikasi = 'menunggu' WHERE kelompok_id = $kelompokId");
    }
    
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Progress kelompok berhasil diperbarui.']);
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database: ' . $e->getMessage()]);
}
