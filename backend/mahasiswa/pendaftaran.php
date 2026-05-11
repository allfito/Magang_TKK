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

// Cari kelompok milik user (hanya ketua yang bisa)
$stmt = $mysqli->prepare('SELECT id FROM kelompok WHERE ketua_user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if (! $result || $result->num_rows === 0) {
    $_SESSION['error'] = 'Anda belum memiliki kelompok atau bukan ketua.';
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

$mysqli->begin_transaction();
try {
    $stmtP = $mysqli->prepare('INSERT INTO perusahaan (nama, nama_pimpinan, bidang, telepon, alamat, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmtP->bind_param('sssssss', $perusahaan, $namaPimpinan, $bidang, $telepon, $alamat, $latitude, $longitude);
    if (! $stmtP->execute()) {
        throw new Exception('Gagal menyimpan perusahaan: ' . $stmtP->error);
    }
    $perusahaanId = $stmtP->insert_id;

    $stmtL = $mysqli->prepare('INSERT INTO pendaftaran_lokasi (kelompok_id, perusahaan_id, status_verifikasi, created_at) VALUES (?, ?, "menunggu", NOW())');
    $stmtL->bind_param('ii', $kelompokId, $perusahaanId);
    if (! $stmtL->execute()) {
        throw new Exception('Gagal menyimpan pendaftaran lokasi: ' . $stmtL->error);
    }

    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$_SESSION['success'] = 'Pendaftaran lokasi magang berhasil dikirim.';
header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
