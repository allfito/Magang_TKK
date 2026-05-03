<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/auth/register.php');
    exit;
}

require_once __DIR__ . '/functions.php';

$mysqli = require __DIR__ . '/../database.php';
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$no_tlp = trim($_POST['no_tlp'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi = $_POST['konfirmasi_password'] ?? '';

if ($nama === '') {
    redirectWithError('Nama tidak boleh kosong', '../../frontend/auth/register.php');
}
if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Email tidak valid', '../../frontend/auth/register.php');
}
if ($no_tlp === '') {
    redirectWithError('Nomor telepon tidak boleh kosong', '../../frontend/auth/register.php');
}
if ($password !== $konfirmasi) {
    redirectWithError('Password tidak cocok', '../../frontend/auth/register.php');
}
if ($error = validatePassword($password)) {
    redirectWithError($error, '../../frontend/auth/register.php');
}

if (findUserByEmail($mysqli, $email)) {
    redirectWithError('Email sudah terdaftar', '../../frontend/auth/register.php');
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO user (nama, email, no_tlp, password_hash, role, created_at)
        VALUES (?, ?, ?, ?, 'mahasiswa', NOW())";
$stmt = $mysqli->stmt_init();
if (! $stmt->prepare($sql)) {
    redirectWithError('Ada kesalahan pada query: ' . $mysqli->error, '../../frontend/auth/register.php');
}

$stmt->bind_param('ssss', $nama, $email, $no_tlp, $password_hash);
if (! $stmt->execute()) {
    redirectWithError('Gagal membuat akun: ' . $stmt->error, '../../frontend/auth/register.php');
}

redirectWithSuccess('Pendaftaran berhasil', '../../frontend/auth/login.php');
