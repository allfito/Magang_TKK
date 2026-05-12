<?php
/**
 * Action: Submit Lokasi Magang (Mahasiswa)
 * Hanya menerima POST. Autentikasi via MahasiswaHelper.
 */

require_once __DIR__ . '/../helpers/MahasiswaHelper.php';
require_once __DIR__ . '/../controllers/PendaftaranController.php';

MahasiswaHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$userId     = Session::getUserId();
$controller = new PendaftaranController();
$result     = $controller->submitLokasi($userId, $_POST);

if ($result['status']) {
    MahasiswaHelper::redirectWithSuccess($result['message'], '../../frontend/mahasiswa/pendaftaran.php');
} else {
    MahasiswaHelper::redirectWithError($result['message'], '../../frontend/mahasiswa/pendaftaran.php');
}
