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

$namaDosen = trim($_POST['nama_dosen'] ?? '');

if ($namaDosen === '') {
    $_SESSION['error'] = 'Nama dosen tidak boleh kosong.';
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$stmt = $mysqli->prepare('INSERT IGNORE INTO dosen (nama, created_at) VALUES (?, NOW())');
$stmt->bind_param('s', $namaDosen);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Dosen berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Dosen dengan nama tersebut sudah ada.';
    }
} else {
    $_SESSION['error'] = 'Gagal menambahkan dosen: ' . $stmt->error;
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
