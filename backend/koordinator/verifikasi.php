<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/dashboard.php');
    exit;
}

$mysqli = require __DIR__ . '/../database.php';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || ($_SESSION['role'] ?? '') !== 'korbid') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if (!in_array($action, ['disetujui', 'ditolak'], true) || $id <= 0) {
    $_SESSION['error'] = 'Aksi tidak valid.';
    header('Location: ../../frontend/koordinator/dashboard.php');
    exit;
}

$redirectPage = 'dashboard.php';

switch ($type) {
    case 'lokasi':
        $stmt = $mysqli->prepare('UPDATE pendaftaran_lokasi SET status_verifikasi = ? WHERE id = ?');
        $stmt->bind_param('si', $action, $id);
        $redirectPage = 'verifikasi_lokasi.php';
        break;

    case 'proposal':
        $stmt = $mysqli->prepare('UPDATE proposal SET status_verifikasi = ? WHERE id = ?');
        $stmt->bind_param('si', $action, $id);
        $redirectPage = 'verifikasi_proposal.php';
        break;

    case 'berkas':
        // Update all berkas for the given kelompok_id
        $stmt = $mysqli->prepare('UPDATE berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id SET ba.status_verifikasi = ? WHERE ak.kelompok_id = ?');
        $stmt->bind_param('si', $action, $id);
        $stmt->execute();
        
        // Also update anggota_kelompok status_berkas
        $berkasStatus = ($action === 'disetujui') ? 'lengkap' : 'belum';
        $stmt2 = $mysqli->prepare('UPDATE anggota_kelompok SET status_berkas = ? WHERE kelompok_id = ?');
        $stmt2->bind_param('si', $berkasStatus, $id);
        $stmt2->execute();
        
        $redirectPage = 'verifikasi_berkas.php';
        // Mock a statement to bypass the execute check below, since we already executed
        $stmt = $mysqli->prepare('SELECT 1');
        break;

    case 'bukti':
        $stmt = $mysqli->prepare('UPDATE bukti_diterima SET status_verifikasi = ? WHERE id = ?');
        $stmt->bind_param('si', $action, $id);
        $redirectPage = 'verifikasi_bukti.php';
        break;

    default:
        $_SESSION['error'] = 'Tipe verifikasi tidak valid.';
        header('Location: ../../frontend/koordinator/dashboard.php');
        exit;
}

if ($stmt->execute()) {
    // If action is ditolak, let's also update kelompok status
    if ($action === 'ditolak') {
        // Need to get kelompok_id for lokasi and proposal
        $kelId = $id;
        if ($type === 'lokasi') {
            $kStmt = $mysqli->prepare('SELECT kelompok_id FROM pendaftaran_lokasi WHERE id = ?');
            $kStmt->bind_param('i', $id);
            $kStmt->execute();
            $r = $kStmt->get_result()->fetch_assoc();
            if ($r) $kelId = $r['kelompok_id'];
        } elseif ($type === 'proposal') {
            $kStmt = $mysqli->prepare('SELECT kelompok_id FROM proposal WHERE id = ?');
            $kStmt->bind_param('i', $id);
            $kStmt->execute();
            $r = $kStmt->get_result()->fetch_assoc();
            if ($r) $kelId = $r['kelompok_id'];
        } elseif ($type === 'bukti') {
            $kStmt = $mysqli->prepare('SELECT kelompok_id FROM bukti_diterima WHERE id = ?');
            $kStmt->bind_param('i', $id);
            $kStmt->execute();
            $r = $kStmt->get_result()->fetch_assoc();
            if ($r) $kelId = $r['kelompok_id'];
        }
        $updK = $mysqli->prepare('UPDATE kelompok SET status = "ditolak" WHERE id = ?');
        $updK->bind_param('i', $kelId);
        $updK->execute();
    } elseif ($action === 'disetujui') {
        // If action is disetujui, we can set kelompok status to aktif
        $kelId = $id;
        if ($type === 'lokasi') {
            $kStmt = $mysqli->prepare('SELECT kelompok_id FROM pendaftaran_lokasi WHERE id = ?');
            $kStmt->bind_param('i', $id);
            $kStmt->execute();
            $r = $kStmt->get_result()->fetch_assoc();
            if ($r) $kelId = $r['kelompok_id'];
        } elseif ($type === 'proposal') {
            $kStmt = $mysqli->prepare('SELECT kelompok_id FROM proposal WHERE id = ?');
            $kStmt->bind_param('i', $id);
            $kStmt->execute();
            $r = $kStmt->get_result()->fetch_assoc();
            if ($r) $kelId = $r['kelompok_id'];
        } elseif ($type === 'bukti') {
            $kStmt = $mysqli->prepare('SELECT kelompok_id FROM bukti_diterima WHERE id = ?');
            $kStmt->bind_param('i', $id);
            $kStmt->execute();
            $r = $kStmt->get_result()->fetch_assoc();
            if ($r) $kelId = $r['kelompok_id'];
        }
        $updK = $mysqli->prepare('UPDATE kelompok SET status = "aktif" WHERE id = ? AND status != "ditolak"');
        $updK->bind_param('i', $kelId);
        $updK->execute();
    }
    
    $_SESSION['success'] = 'Verifikasi berhasil diperbarui.';
} else {
    $_SESSION['error'] = 'Gagal memperbarui verifikasi: ' . $stmt->error;
}

header('Location: ../../frontend/koordinator/' . $redirectPage);
exit;
