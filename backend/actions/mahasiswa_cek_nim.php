<?php
require_once __DIR__ . '/../helpers/MahasiswaHelper.php';
require_once __DIR__ . '/../models/Mahasiswa.php';

MahasiswaHelper::requireLogin();

header('Content-Type: application/json');

$nim = trim($_GET['nim'] ?? '');

if ($nim === '') {
    echo json_encode(['status' => 'error', 'message' => 'NIM wajib diisi.']);
    exit;
}

$mahasiswaModel = new Mahasiswa(Database::getInstance()->getConnection());
$kelompok = $mahasiswaModel->findKelompokByNim($nim);

if ($kelompok) {
    echo json_encode([
        'status' => 'exists',
        'message' => "Mahasiswa dengan NIM " . htmlspecialchars($nim) . " sudah terdaftar di kelompok lain (" . htmlspecialchars($kelompok['nama']) . ")."
    ]);
} else {
    echo json_encode(['status' => 'available']);
}
