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
$namaKelompok = trim($_POST['nama_kelompok'] ?? '');

if ($namaKelompok === '') {
    $_SESSION['error'] = 'Nama kelompok wajib diisi.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

if (!is_array($anggota) || count($anggota) < 3 || count($anggota) > 4) {
    $_SESSION['error'] = 'Kelompok harus terdiri dari 3 sampai 4 anggota.';
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

if (count($anggotaValid) < 3) {
    $_SESSION['error'] = 'Setidaknya ketua dan dua anggota harus diisi dengan lengkap.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

$stmt = $mysqli->prepare('SELECT id FROM kelompok WHERE ketua_user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $_SESSION['error'] = 'Anda sudah memiliki kelompok.';
    header('Location: ../../frontend/mahasiswa/kelompok.php');
    exit;
}

// Use the submitted nama kelompok
// $namaKelompok is already populated and validated above

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare('INSERT INTO kelompok (nama, ketua_user_id, created_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('si', $namaKelompok, $userId);
    if (! $stmt->execute()) {
        throw new RuntimeException('Gagal membuat kelompok: ' . $stmt->error);
    }

    $kelompokId = $stmt->insert_id;

    foreach ($anggotaValid as $index => $member) {
        $peran = $index === 0 ? 'ketua' : 'anggota';
        $email = ''; // email not provided in form

        // Check if mahasiswa exists
        $stmtMahasiswa = $mysqli->prepare('SELECT id FROM mahasiswa WHERE nim = ?');
        $stmtMahasiswa->bind_param('s', $member['nim']);
        $stmtMahasiswa->execute();
        $resMahasiswa = $stmtMahasiswa->get_result();
        
        if ($resMahasiswa && $resMahasiswa->num_rows > 0) {
            $mahasiswaId = $resMahasiswa->fetch_assoc()['id'];
        } else {
            // Insert new mahasiswa
            $stmtInsertMhs = $mysqli->prepare('INSERT INTO mahasiswa (nim, nama, no_tlp, created_at) VALUES (?, ?, ?, NOW())');
            $stmtInsertMhs->bind_param('sss', $member['nim'], $member['nama'], $member['no_tlp']);
            if (! $stmtInsertMhs->execute()) {
                throw new RuntimeException('Gagal menyimpan biodata mahasiswa: ' . $stmtInsertMhs->error);
            }
            $mahasiswaId = $stmtInsertMhs->insert_id;
        }

        $insert = $mysqli->prepare('INSERT INTO anggota_kelompok (kelompok_id, mahasiswa_id, peran, status_berkas, created_at) VALUES (?, ?, ?, "pending", NOW())');
        $insert->bind_param('iis', $kelompokId, $mahasiswaId, $peran);

        if (! $insert->execute()) {
            throw new RuntimeException('Gagal menyimpan relasi anggota: ' . $insert->error);
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
