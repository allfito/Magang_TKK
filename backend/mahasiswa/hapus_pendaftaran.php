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

$type = $_POST['type'] ?? '';

// Get kelompok ID (hanya ketua)
$stmt = $mysqli->prepare('SELECT id FROM kelompok WHERE ketua_user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$kel = $stmt->get_result()->fetch_assoc();
if (!$kel) {
    $_SESSION['error'] = 'Anda belum memiliki kelompok atau bukan ketua.';
    header('Location: ../../frontend/mahasiswa/pendaftaran.php');
    exit;
}
$kelompokId = $kel['id'];

if ($type === 'lokasi') {
    $stmt = $mysqli->prepare('DELETE FROM pendaftaran_lokasi WHERE kelompok_id = ?');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $_SESSION['success'] = 'Silakan ajukan ulang lokasi magang.';
} elseif ($type === 'proposal') {
    $stmt = $mysqli->prepare('DELETE FROM proposal WHERE kelompok_id = ?');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $_SESSION['success'] = 'Silakan ajukan ulang proposal kelompok.';
} elseif ($type === 'berkas') {
    // Delete berkas
    $stmt = $mysqli->prepare('DELETE ba FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    
    // Update anggota_kelompok status
    $stmt2 = $mysqli->prepare('UPDATE anggota_kelompok SET status_berkas = "belum" WHERE kelompok_id = ?');
    $stmt2->bind_param('i', $kelompokId);
    $stmt2->execute();
    
    $_SESSION['success'] = 'Silakan ajukan ulang berkas anggota.';
} elseif ($type === 'bukti') {
    $stmt = $mysqli->prepare('DELETE FROM bukti_diterima WHERE kelompok_id = ?');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $_SESSION['success'] = 'Silakan ajukan ulang bukti diterima.';
}

header('Location: ../../frontend/mahasiswa/pendaftaran.php');
exit;
