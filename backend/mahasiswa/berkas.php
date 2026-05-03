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

$stmt = $mysqli->prepare('SELECT id FROM anggota_kelompok WHERE kelompok_id = ? ORDER BY created_at ASC');
$stmt->bind_param('i', $kelompokId);
$stmt->execute();
$anggotaResult = $stmt->get_result();
$anggotaList = $anggotaResult->fetch_all(MYSQLI_ASSOC);

if (count($anggotaList) === 0) {
    $_SESSION['error'] = 'Tidak ada anggota kelompok untuk mengupload berkas.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}

$types = ['formulir', 'ktm', 'transkrip', 'pas_foto', 'cv'];
$uploaded = 0;
$uploadDir = __DIR__ . '/../../uploads/berkas/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

foreach ($anggotaList as $index => $anggota) {
    foreach ($types as $type) {
        $fieldName = "berkas_{$index}_{$type}";
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file = $_FILES[$fieldName];
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes, true) || $file['size'] > $maxSize) {
            continue;
        }

        $filename = uniqid('berkas_') . '_' . $type . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            continue;
        }

        $relativePath = 'uploads/berkas/' . $filename;
        $stmt = $mysqli->prepare('SELECT id FROM berkas_anggota WHERE anggota_id = ? AND jenis_berkas = ? LIMIT 1');
        $stmt->bind_param('is', $anggota['id'], $type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $update = $mysqli->prepare('UPDATE berkas_anggota SET file_path = ?, status_verifikasi = "menunggu", created_at = NOW() WHERE id = ?');
            $update->bind_param('si', $relativePath, $row['id']);
            $update->execute();
        } else {
            $insert = $mysqli->prepare('INSERT INTO berkas_anggota (anggota_id, jenis_berkas, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())');
            $insert->bind_param('iss', $anggota['id'], $type, $relativePath);
            $insert->execute();
        }

        $uploaded++;
    }
}

if ($uploaded > 0) {
    $_SESSION['success'] = "Berhasil mengunggah $uploaded berkas.";
} else {
    $_SESSION['error'] = 'Tidak ada berkas yang berhasil diunggah.';
}

header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
