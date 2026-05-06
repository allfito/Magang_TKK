<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$mysqli = require __DIR__ . '/../database.php';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || ($_SESSION['role'] ?? '') !== 'korbid') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$kelompokId = (int) ($_POST['kelompok_id'] ?? 0);
$dosenPembimbing = trim($_POST['dosen_pembimbing'] ?? '');

if ($kelompokId <= 0 || $dosenPembimbing === '') {
    $_SESSION['error'] = 'Dosen pembimbing wajib diisi.';
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

// Check if already plotted
$stmt = $mysqli->prepare('SELECT id FROM plotting WHERE kelompok_id = ? LIMIT 1');
$stmt->bind_param('i', $kelompokId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Update existing
    $row = $result->fetch_assoc();
    $stmt = $mysqli->prepare('UPDATE plotting SET dosen_pembimbing = ? WHERE id = ?');
    $stmt->bind_param('si', $dosenPembimbing, $row['id']);
} else {
    // Insert new
    $stmt = $mysqli->prepare('INSERT INTO plotting (kelompok_id, lokasi, dosen_pembimbing, created_at) VALUES (?, "", ?, NOW())');
    $stmt->bind_param('is', $kelompokId, $dosenPembimbing);
}

if ($stmt->execute()) {
    $_SESSION['success'] = 'Plotting berhasil disimpan.';
} else {
    $_SESSION['error'] = 'Gagal menyimpan plotting: ' . $stmt->error;
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
