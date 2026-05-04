<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$mysqli = require __DIR__ . '/../database.php';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$perusahaan = trim($_POST['perusahaan'] ?? '');
$namaPimpinan = trim($_POST['nama_pimpinan'] ?? '');
$bidang = trim($_POST['bidang'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$latitude = '';
$longitude = '';

if ($perusahaan === '' || $namaPimpinan === '' || $bidang === '' || $telepon === '' || $alamat === '') {
    $_SESSION['error'] = 'Semua field lokasi magang harus diisi.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

// Cari kelompok milik user (sebagai ketua atau anggota)
$stmt = $mysqli->prepare('SELECT k.id FROM kelompok k LEFT JOIN anggota_kelompok ak ON ak.kelompok_id = k.id AND ak.mahasiswa_id = ? WHERE k.ketua_id = ? OR ak.mahasiswa_id = ? LIMIT 1');
$stmt->bind_param('iii', $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();
if (! $result || $result->num_rows === 0) {
    $_SESSION['error'] = 'Anda belum memiliki kelompok.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$kelompok = $result->fetch_assoc();
$kelompokId = $kelompok['id'];

$stmt = $mysqli->prepare('SELECT id FROM pendaftaran_lokasi WHERE kelompok_id = ? LIMIT 1');
$stmt->bind_param('i', $kelompokId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = 'Kelompok sudah terdaftar lokasi magang.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$stmt = $mysqli->prepare('INSERT INTO pendaftaran_lokasi (kelompok_id, perusahaan, nama_pimpinan, bidang, telepon, alamat, latitude, longitude, status_verifikasi, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "menunggu", NOW())');
$stmt->bind_param('isssssss', $kelompokId, $perusahaan, $namaPimpinan, $bidang, $telepon, $alamat, $latitude, $longitude);
if (! $stmt->execute()) {
    $_SESSION['error'] = 'Gagal menyimpan pendaftaran lokasi: ' . $stmt->error;
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$_SESSION['success'] = 'Pendaftaran lokasi magang berhasil dikirim.';
header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
