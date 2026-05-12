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

$controller = new PendaftaranController();
$result     = $controller->submitBerkas((int) $userId, $_FILES);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
