<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/PendaftaranController.php';

$tempatDiterima = trim($_POST['tempat_diterima'] ?? '');
$fileData       = $_FILES['surat_penerimaan'] ?? null;

$controller = new PendaftaranController();
$result     = $controller->submitBuktiDiterima((int) $userId, $tempatDiterima, $fileData);

if ($result['status']) {
    unset($_SESSION['form_data']['bukti']);
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
