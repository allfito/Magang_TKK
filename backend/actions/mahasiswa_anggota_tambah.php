<?php
require_once __DIR__ . '/../helpers/MahasiswaHelper.php';
require_once __DIR__ . '/../controllers/KelompokController.php';

MahasiswaHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$nama   = $_POST['nama'] ?? '';
$nim    = $_POST['nim'] ?? '';
$noTlp  = $_POST['no_tlp'] ?? '';

$controller = new KelompokController();
$result = $controller->tambahAnggotaExisting($userId, $nama, $nim, $noTlp);

if ($result['status'] === 'success') {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ../../frontend/mahasiswa/kelompok.php');
exit;
