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

// Function to validate file based on extension and MIME type
function validateFileType($filename, $mimeType) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowedExtensions = ['pdf', 'jpeg', 'jpg', 'png'];
    
    // MIME type whitelist (allowing variations)
    $allowedMimes = [
        'application/pdf',
        'application/x-pdf',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];
    
    return in_array($ext, $allowedExtensions, true) && in_array($mimeType, $allowedMimes, true);
}

foreach ($anggotaList as $index => $anggota) {
    foreach ($types as $type) {
        $fieldName = "berkas_{$index}_{$type}";
        
        // Skip if field not provided or has error
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        
        // Handle upload errors
        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file = $_FILES[$fieldName];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validate file size
        if ($file['size'] > $maxSize) {
            continue;
        }

        // Validate file type
        if (!validateFileType($file['name'], $file['type'])) {
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
            if ($update->execute()) {
                $uploaded++;
            }
        } else {
            $insert = $mysqli->prepare('INSERT INTO berkas_anggota (anggota_id, jenis_berkas, file_path, status_verifikasi, created_at) VALUES (?, ?, ?, "menunggu", NOW())');
            $insert->bind_param('iss', $anggota['id'], $type, $relativePath);
            if ($insert->execute()) {
                $uploaded++;
            }
        }
    }
}

if ($uploaded > 0) {
    $_SESSION['success'] = "Berhasil mengunggah $uploaded berkas.";
} else {
    $_SESSION['error'] = 'Tidak ada berkas yang berhasil diunggah. Pastikan Anda memilih file dengan format yang benar (PDF, JPEG, PNG).';
}

header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
