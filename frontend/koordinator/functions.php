<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require koordinator login
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'korbid') {
    header('Location: ../../frontend/auth/login.php');
    exit;
}

function dbKoordinator(): mysqli
{
    return require __DIR__ . '/../../backend/database.php';
}

function getActiveGroupCount(): int
{
    $mysqli = dbKoordinator();
    $sql = "SELECT COUNT(*) AS total FROM kelompok";
    $row = $mysqli->query($sql)->fetch_assoc();
    return (int) ($row['total'] ?? 0);
}

function getPendingLocationCount(): int
{
    $mysqli = dbKoordinator();
    $sql = "SELECT COUNT(*) AS total FROM pendaftaran_lokasi WHERE status_verifikasi = 'menunggu'";
    $row = $mysqli->query($sql)->fetch_assoc();
    return (int) ($row['total'] ?? 0);
}

function getPendingProposalCount(): int
{
    $mysqli = dbKoordinator();
    $sql = "SELECT COUNT(*) AS total FROM proposal WHERE status_verifikasi = 'menunggu'";
    $row = $mysqli->query($sql)->fetch_assoc();
    return (int) ($row['total'] ?? 0);
}

function getPendingBerkasCount(): int
{
    $mysqli = dbKoordinator();
    $sql = "SELECT COUNT(DISTINCT a.kelompok_id) AS total
            FROM anggota_kelompok a
            JOIN berkas_anggota b ON a.id = b.anggota_id
            WHERE b.status_verifikasi = 'menunggu'";
    $row = $mysqli->query($sql)->fetch_assoc();
    return (int) ($row['total'] ?? 0);
}

function getPendingBuktiCount(): int
{
    $mysqli = dbKoordinator();
    $sql = "SELECT COUNT(*) AS total FROM bukti_diterima WHERE status_verifikasi = 'menunggu'";
    $row = $mysqli->query($sql)->fetch_assoc();
    return (int) ($row['total'] ?? 0);
}

function getGroupsPendingVerification(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT DISTINCT k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
            CASE
                WHEN EXISTS(SELECT 1 FROM pendaftaran_lokasi pl WHERE pl.kelompok_id = k.id AND pl.status_verifikasi = 'menunggu') THEN 'Lokasi Magang'
                WHEN EXISTS(SELECT 1 FROM proposal pr WHERE pr.kelompok_id = k.id AND pr.status_verifikasi = 'menunggu') THEN 'Proposal'
                WHEN EXISTS(SELECT 1 FROM anggota_kelompok a JOIN berkas_anggota b ON a.id = b.anggota_id WHERE a.kelompok_id = k.id AND b.status_verifikasi = 'menunggu') THEN 'Berkas'
                WHEN EXISTS(SELECT 1 FROM bukti_diterima bd WHERE bd.kelompok_id = k.id AND bd.status_verifikasi = 'menunggu') THEN 'Bukti Diterima'
                ELSE 'Info Kelompok'
            END AS jenis_verifikasi
            FROM kelompok k
            JOIN user u ON k.ketua_id = u.id
            WHERE EXISTS(SELECT 1 FROM pendaftaran_lokasi pl WHERE pl.kelompok_id = k.id AND pl.status_verifikasi = 'menunggu')
               OR EXISTS(SELECT 1 FROM proposal pr WHERE pr.kelompok_id = k.id AND pr.status_verifikasi = 'menunggu')
               OR EXISTS(SELECT 1 FROM anggota_kelompok a JOIN berkas_anggota b ON a.id = b.anggota_id WHERE a.kelompok_id = k.id AND b.status_verifikasi = 'menunggu')
               OR EXISTS(SELECT 1 FROM bukti_diterima bd WHERE bd.kelompok_id = k.id AND bd.status_verifikasi = 'menunggu')
            ORDER BY k.nama ASC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getMembersCount(int $kelompokId): int
{
    $mysqli = dbKoordinator();
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM anggota_kelompok WHERE kelompok_id = ?");
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int) ($row['total'] ?? 0);
}

function getGroupsForLocationVerification(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT pl.id AS lokasi_id, k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
            pl.perusahaan, pl.bidang, pl.alamat, pl.nama_pimpinan, pl.telepon, pl.status_verifikasi, pl.created_at
            FROM pendaftaran_lokasi pl
            JOIN kelompok k ON pl.kelompok_id = k.id
            JOIN user u ON k.ketua_id = u.id
            ORDER BY pl.created_at DESC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getGroupsForProposalVerification(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT pr.id AS proposal_id, k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
            pr.file_path, pr.status_verifikasi, pr.created_at
            FROM proposal pr
            JOIN kelompok k ON pr.kelompok_id = k.id
            JOIN user u ON k.ketua_id = u.id
            ORDER BY pr.created_at DESC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getGroupsForBerkasVerification(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
            COUNT(b.id) AS jumlah_berkas,
            MAX(b.created_at) AS tanggal_upload,
            CASE
                WHEN SUM(b.status_verifikasi = 'menunggu') > 0 THEN 'menunggu'
                WHEN SUM(b.status_verifikasi = 'ditolak') > 0 THEN 'ditolak'
                ELSE 'disetujui'
            END AS status
            FROM anggota_kelompok a
            JOIN berkas_anggota b ON a.id = b.anggota_id
            JOIN kelompok k ON a.kelompok_id = k.id
            JOIN user u ON k.ketua_id = u.id
            GROUP BY k.id, k.nama, u.nama
            ORDER BY tanggal_upload DESC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getBerkasByGroup(int $kelompokId): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT a.nama AS anggota_nama, a.nim, b.id AS berkas_id, b.jenis_berkas, b.file_path, b.status_verifikasi
            FROM anggota_kelompok a
            JOIN berkas_anggota b ON a.id = b.anggota_id
            WHERE a.kelompok_id = ?
            ORDER BY a.nama ASC, b.jenis_berkas ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getGroupsForBuktiVerification(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT bd.id AS bukti_id, k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
            bd.tempat_diterima, bd.file_path, bd.status_verifikasi, bd.created_at
            FROM bukti_diterima bd
            JOIN kelompok k ON bd.kelompok_id = k.id
            JOIN user u ON k.ketua_id = u.id
            ORDER BY bd.created_at DESC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getGroupsForPlotting(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT k.id AS kelompok_id, k.nama AS kelompok_nama, u.nama AS ketua_nama,
            COALESCE(p.lokasi, '') AS lokasi, COALESCE(p.dosen_pembimbing, '') AS dosen_pembimbing,
            COUNT(a.id) AS anggota_count,
            CASE WHEN p.id IS NULL THEN 'menunggu' ELSE 'selesai' END AS status
            FROM kelompok k
            LEFT JOIN plotting p ON k.id = p.kelompok_id
            LEFT JOIN anggota_kelompok a ON a.kelompok_id = k.id
            JOIN user u ON k.ketua_id = u.id
            GROUP BY k.id, k.nama, u.nama, p.lokasi, p.dosen_pembimbing, p.id
            ORDER BY k.nama ASC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getPlottingSummary(): array
{
    $mysqli = dbKoordinator();
    $sql = "SELECT p.dosen_pembimbing, COUNT(*) AS jumlah_kelompok
            FROM plotting p
            GROUP BY p.dosen_pembimbing
            ORDER BY jumlah_kelompok DESC";
    $result = $mysqli->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function formatDateIndo(string $datetime): string
{
    $timestamp = strtotime($datetime);
    return $timestamp ? date('d M Y', $timestamp) : '-';
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'disetujui' => 'badge-success-status',
        'ditolak' => 'badge-danger',
        'selesai' => 'badge-success-status',
        default => 'badge-warning',
    };
}
