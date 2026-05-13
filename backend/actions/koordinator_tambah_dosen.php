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

if ($namaDosen === '') {
    Session::setFlash('error', 'Nama dosen tidak boleh kosong.');
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$db    = Database::getInstance()->getConnection();
$model = new KoordinatorModel($db);

if ($model->findDosenByName($namaDosen)) {
    Session::setFlash('error', 'Dosen dengan nama tersebut sudah ada.');
} else {
    $model->createDosen($namaDosen);
    Session::setFlash('success', 'Dosen berhasil ditambahkan.');
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
