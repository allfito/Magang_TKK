<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId || ($_SESSION['role'] ?? '') !== 'korbid') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/KoordinatorModel.php';

$namaDosen = trim($_POST['nama_dosen'] ?? '');

if ($namaDosen === '') {
    $_SESSION['error'] = 'Nama dosen tidak boleh kosong.';
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$db    = Database::getInstance()->getConnection();
$model = new KoordinatorModel($db);

if ($model->findDosenByName($namaDosen)) {
    $_SESSION['error'] = 'Dosen dengan nama tersebut sudah ada.';
} else {
    $model->createDosen($namaDosen);
    $_SESSION['success'] = 'Dosen berhasil ditambahkan.';
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
