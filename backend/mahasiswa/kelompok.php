<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

$mysqli = require __DIR__ . '/../database.php';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$anggota = $_POST['anggota'] ?? [];

if (!is_array($anggota) || count($anggota) < 2 || count($anggota) > 4) {
    $_SESSION['error'] = 'Kelompok harus terdiri dari 2 sampai 4 anggota.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

$anggotaValid = [];
foreach ($anggota as $index => $member) {
    $nama = trim($member['nama'] ?? '');
    $nim = trim($member['nim'] ?? '');
    $telepon = trim($member['no_tlp'] ?? '');

    if ($nama === '' || $nim === '') {
        continue;
    }

    $anggotaValid[] = [
        'nama' => $nama,
        'nim' => $nim,
        'no_tlp' => $telepon,
    ];
}

if (count($anggotaValid) < 2) {
    $_SESSION['error'] = 'Setidaknya ketua dan satu anggota harus diisi dengan lengkap.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

$stmt = $mysqli->prepare('SELECT id FROM kelompok WHERE ketua_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $_SESSION['error'] = 'Anda sudah memiliki kelompok.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

// Auto-generate nama kelompok from ketua name
$namaKelompok = 'Kelompok ' . $anggotaValid[0]['nama'];

$mysqli->begin_transaction();
try {
    // Set status langsung "aktif" karena tidak perlu verifikasi
    $stmt = $mysqli->prepare('INSERT INTO kelompok (nama, ketua_id, status, created_at) VALUES (?, ?, "aktif", NOW())');
    $stmt->bind_param('si', $namaKelompok, $userId);
    if (! $stmt->execute()) {
        throw new RuntimeException('Gagal membuat kelompok: ' . $stmt->error);
    }

    $kelompokId = $stmt->insert_id;

    foreach ($anggotaValid as $index => $member) {
        $peran = $index === 0 ? 'ketua' : 'anggota';
        $mahasiswaId = $index === 0 ? $userId : null;
        $email = ''; // email not required

        $insert = $mysqli->prepare('INSERT INTO anggota_kelompok (kelompok_id, mahasiswa_id, nama, nim, email, no_tlp, peran, status_berkas, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, "pending", NOW())');
        $insert->bind_param('iisssss', $kelompokId, $mahasiswaId, $member['nama'], $member['nim'], $email, $member['no_tlp'], $peran);

        if (! $insert->execute()) {
            throw new RuntimeException('Gagal menyimpan anggota: ' . $insert->error);
        }
    }

    $mysqli->commit();
    $_SESSION['success'] = 'Kelompok berhasil dibuat.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
} catch (Throwable $e) {
    $mysqli->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}
