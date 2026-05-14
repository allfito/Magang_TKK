<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/KelompokController.php';

$anggotaId   = (int) ($_POST['anggota_id']   ?? 0);
$mahasiswaId = (int) ($_POST['mahasiswa_id']  ?? 0);
$nama        = trim($_POST['nama']   ?? '');
$nim         = trim($_POST['nim']    ?? '');
$noTlp       = trim($_POST['no_tlp'] ?? '');

$controller = new KelompokController();
$result     = $controller->editAnggota((int) $userId, $anggotaId, $mahasiswaId, $nama, $nim, $noTlp);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ../../frontend/mahasiswa/kelompok.php');
exit;
