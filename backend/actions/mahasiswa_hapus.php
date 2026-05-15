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
require_once __DIR__ . '/../controllers/MahasiswaPendaftaranViewController.php';

$type = $_POST['type'] ?? '';

// Ambil data sebelum dihapus untuk disimpan di session agar bisa di-prefill
$viewController = new MahasiswaPendaftaranViewController();
$oldData = $viewController->getPendaftaranData((int) $userId);
if (isset($oldData[$type])) {
    $_SESSION['form_data'][$type] = $oldData[$type];
}

if ($type === 'berkas') {
    $_SESSION['open_form_berkas'] = true;
    $_SESSION['success'] = 'Silakan perbaiki berkas yang ditolak.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$controller = new PendaftaranController();
$result     = $controller->deleteRegistration((int) $userId, $type);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
