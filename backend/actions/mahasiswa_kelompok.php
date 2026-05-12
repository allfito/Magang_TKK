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

$namaKelompok = trim($_POST['nama_kelompok'] ?? '');
$anggota      = $_POST['anggota'] ?? [];

$controller = new KelompokController();
$result     = $controller->createKelompok((int) $userId, $namaKelompok, $anggota);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ../../frontend/mahasiswa/kelompok.php');
exit;
