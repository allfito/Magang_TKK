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

$tempatDiterima = trim($_POST['tempat_diterima'] ?? '');

if ($tempatDiterima === '' || !isset($_FILES['surat_penerimaan']) || $_FILES['surat_penerimaan']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Tempat diterima dan file surat penerimaan wajib diisi.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$file = $_FILES['surat_penerimaan'];
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
$maxSize = 5 * 1024 * 1024;

if (!in_array($file['type'], $allowedTypes, true) || $file['size'] > $maxSize) {
    $_SESSION['error'] = 'File surat penerimaan harus PDF atau gambar dan maksimal 5MB.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$stmt = $mysqli->prepare('SELECT id FROM kelompok WHERE ketua_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if (! $result || $result->num_rows === 0) {
    $_SESSION['error'] = 'Anda belum memiliki kelompok.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$kelompok = $result->fetch_assoc();
$kelompokId = $kelompok['id'];

$uploadDir = __DIR__ . '/../../uploads/bukti/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('bukti_') . '_' . basename($file['name']);
$filepath = $uploadDir . $filename;

if (! move_uploaded_file($file['tmp_name'], $filepath)) {
    $_SESSION['error'] = 'Gagal mengunggah surat penerimaan.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$relativePath = 'uploads/bukti/' . $filename;
$stmt = $mysqli->prepare('INSERT INTO bukti_diterima (kelompok_id, tempat_diterima, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())');
$stmt->bind_param('iss', $kelompokId, $tempatDiterima, $relativePath);
if (! $stmt->execute()) {
    $_SESSION['error'] = 'Gagal menyimpan bukti diterima: ' . $stmt->error;
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$_SESSION['success'] = 'Bukti penerimaan berhasil diunggah.';
header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
