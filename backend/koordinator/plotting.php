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

// Check if dosen exists, or insert new
$stmtDosen = $mysqli->prepare('SELECT id FROM dosen WHERE nama = ? LIMIT 1');
$stmtDosen->bind_param('s', $dosenPembimbing);
$stmtDosen->execute();
$resDosen = $stmtDosen->get_result();

if ($resDosen && $resDosen->num_rows > 0) {
    $dosenId = $resDosen->fetch_assoc()['id'];
} else {
    $stmtInsert = $mysqli->prepare('INSERT INTO dosen (nama, created_at) VALUES (?, NOW())');
    $stmtInsert->bind_param('s', $dosenPembimbing);
    $stmtInsert->execute();
    $dosenId = $stmtInsert->insert_id;
}

// Check if already plotted
$stmt = $mysqli->prepare('SELECT id FROM plotting WHERE kelompok_id = ? LIMIT 1');
$stmt->bind_param('i', $kelompokId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Update existing
    $row = $result->fetch_assoc();
    $stmt = $mysqli->prepare('UPDATE plotting SET dosen_id = ? WHERE id = ?');
    $stmt->bind_param('ii', $dosenId, $row['id']);
} else {
    // Insert new
    $stmt = $mysqli->prepare('INSERT INTO plotting (kelompok_id, dosen_id, created_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('ii', $kelompokId, $dosenId);
}

if ($stmt->execute()) {
    $_SESSION['success'] = 'Plotting berhasil disimpan.';
} else {
    $_SESSION['error'] = 'Gagal menyimpan plotting: ' . $stmt->error;
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
