<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../helpers/MahasiswaHelper.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$email        = trim($_POST['email'] ?? '');
$password     = $_POST['password'] ?? '';
$requestedRole = $_POST['role'] ?? '';

$auth   = new AuthController();
$result = $auth->login($email, $password, $requestedRole);

if (!$result['status']) {
    MahasiswaHelper::redirectWithError($result['message'], '../../frontend/auth/login.php');
}

if ($result['role'] === 'korbid') {
    header('Location: ../../frontend/koordinator/dashboard.php');
} else {
    header('Location: ../../frontend/mahasiswa/dashboard.php');
}
exit;
