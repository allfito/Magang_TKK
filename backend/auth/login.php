<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

require_once __DIR__ . '/functions.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$requestedRole = $_POST['role'] ?? '';

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Email tidak valid', '../../frontend/auth/login.php');
}

if ($requestedRole === '') {
    redirectWithError('Pilih role terlebih dahulu (Mahasiswa atau Koordinator Bidang)', '../../frontend/auth/login.php');
}

$mysqli = require __DIR__ . '/../database.php';
$user = findUserByEmail($mysqli, $email);

if (! $user) {
    redirectWithError('Email tidak terdaftar', '../../frontend/auth/login.php');
}

if (! password_verify($password, $user['password_hash'])) {
    redirectWithError('Password salah', '../../frontend/auth/login.php');
}

if ($requestedRole !== $user['role']) {
    redirectWithError('Role tidak sesuai dengan akun Anda. Role akun Anda: ' . ucfirst($user['role']), '../../frontend/auth/login.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

if ($user['role'] === 'korbid') {
    header('Location: ../../frontend/koordinator/dashboard.php');
} else {
    header('Location: ../../frontend/mahasiswa/dashboard.php');
}
exit;
