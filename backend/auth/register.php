<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/auth/register.php');
    exit;
}

require_once __DIR__ . '/../helpers/MahasiswaHelper.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth   = new AuthController();
$result = $auth->register($_POST);

if (!$result['status']) {
    MahasiswaHelper::redirectWithError($result['message'], '../../frontend/auth/register.php');
} else {
    MahasiswaHelper::redirectWithSuccess($result['message'], '../../frontend/auth/login.php');
}
