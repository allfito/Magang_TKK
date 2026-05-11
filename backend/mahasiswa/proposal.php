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

$judul = trim($_POST['judul'] ?? '');

if ($judul === '' || !isset($_FILES['proposal']) || $_FILES['proposal']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Judul proposal dan file PDF wajib diisi.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$file = $_FILES['proposal'];
$allowedTypes = ['application/pdf'];
$maxSize = 5 * 1024 * 1024;

if (! in_array($file['type'], $allowedTypes, true)) {
    $_SESSION['error'] = 'File proposal harus berupa PDF.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

if ($file['size'] > $maxSize) {
    $_SESSION['error'] = 'Ukuran file proposal maksimal 5MB.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

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

$stmt = $mysqli->prepare('SELECT id FROM proposal WHERE kelompok_id = ? LIMIT 1');
$stmt->bind_param('i', $kelompokId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = 'Proposal kelompok sudah diunggah.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/proposals/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('proposal_') . '_' . basename($file['name']);
$filepath = $uploadDir . $filename;

if (! move_uploaded_file($file['tmp_name'], $filepath)) {
    $_SESSION['error'] = 'Gagal mengunggah file proposal.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$relativePath = 'uploads/proposals/' . $filename;
$stmt = $mysqli->prepare('INSERT INTO proposal (kelompok_id, judul, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())');
$stmt->bind_param('iss', $kelompokId, $judul, $relativePath);
if (! $stmt->execute()) {
    $_SESSION['error'] = 'Gagal menyimpan proposal: ' . $stmt->error;
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$_SESSION['success'] = 'Proposal berhasil diunggah.';
header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
