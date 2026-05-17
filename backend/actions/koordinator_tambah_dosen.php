<?php
/**
 * Action: Tambah Dosen Baru (Koordinator)
 */

require_once __DIR__ . '/../helpers/KoordinatorHelper.php';

KoordinatorHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/KoordinatorModel.php';

$namaDosen = trim($_POST['nama_dosen'] ?? '');
$nipDosen  = trim($_POST['nip_dosen']  ?? '');
$tlpDosen  = trim($_POST['tlp_dosen']  ?? '');

if ($namaDosen === '') {
    Session::setFlash('error', 'Nama dosen tidak boleh kosong.');
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

if ($nipDosen === '' || !preg_match('/^[0-9]{18}$/', $nipDosen)) {
    Session::setFlash('error', 'NIP wajib diisi dan harus terdiri dari 18 digit angka.');
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

if ($tlpDosen === '' || !preg_match('/^08[0-9]{8,11}$/', $tlpDosen)) {
    Session::setFlash('error', 'Nomor telepon wajib diisi, diawali 08, dan terdiri dari 10-13 digit angka.');
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$db    = Database::getInstance()->getConnection();
$model = new KoordinatorModel($db);

if ($model->findDosenByName($namaDosen)) {
    Session::setFlash('error', 'Dosen dengan nama tersebut sudah terdaftar.');
} elseif ($model->findDosenByNip($nipDosen)) {
    Session::setFlash('error', 'Dosen dengan NIP ' . htmlspecialchars($nipDosen) . ' sudah terdaftar.');
} elseif ($model->findDosenByTlp($tlpDosen)) {
    Session::setFlash('error', 'Dosen dengan nomor telepon ' . htmlspecialchars($tlpDosen) . ' sudah terdaftar.');
} else {
    $model->createDosen($namaDosen, $nipDosen, $tlpDosen);
    Session::setFlash('success', 'Dosen baru berhasil ditambahkan.');
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
