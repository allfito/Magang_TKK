<?php
require_once __DIR__ . '/functions.php';
requireMahasiswaLogin();

$pageTitle = 'Dashboard Mahasiswa - SIMM';
$activePage = 'dashboard';

$user = currentMahasiswa();
$userName = $user ? htmlspecialchars($user['nama']) : 'Mahasiswa';
$userEmail = $user ? htmlspecialchars($user['email']) : '';

// Get kelompok data
$mysqli = require __DIR__ . '/../../backend/database.php';
$userId = (int) $_SESSION['user_id'];

// Cari kelompok (sebagai ketua atau anggota)
$kelompok = null;
$kelompokId = null;
$isKetua = false;
$stmt = $mysqli->prepare('SELECT k.id, k.nama, k.ketua_user_id, u.nama AS ketua_nama FROM kelompok k JOIN user u ON k.ketua_user_id = u.id WHERE k.ketua_user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $kelompok = $result->fetch_assoc();
    $kelompokId = $kelompok['id'];
    $isKetua = true;
}

// Get anggota list
$anggotaList = [];
if ($kelompokId) {
    $stmt = $mysqli->prepare('SELECT m.nama, m.nim, ak.peran, ak.status_berkas FROM anggota_kelompok ak JOIN mahasiswa m ON ak.mahasiswa_id = m.id WHERE ak.kelompok_id = ? ORDER BY ak.peran ASC, ak.created_at ASC');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $anggotaList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get pendaftaran status
$lokasiStatus = 'belum';
$proposalStatus = 'belum';
$berkasStatus = 'belum';
$buktiStatus = 'belum';
$plottingStatus = 'belum';

if ($kelompokId) {
    // Lokasi
    $stmt = $mysqli->prepare('SELECT status_verifikasi FROM pendaftaran_lokasi WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) $lokasiStatus = $r['status_verifikasi'];

    // Proposal
    $stmt = $mysqli->prepare('SELECT status_verifikasi FROM proposal WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) $proposalStatus = $r['status_verifikasi'];

    // Berkas
    $stmt = $mysqli->prepare('SELECT COUNT(*) AS total, SUM(ba.status_verifikasi = "disetujui") AS approved FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r && $r['total'] > 0) {
        $berkasStatus = ($r['approved'] == $r['total']) ? 'disetujui' : 'menunggu';
    }

    // Bukti
    $stmt = $mysqli->prepare('SELECT status_verifikasi FROM bukti_diterima WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) $buktiStatus = $r['status_verifikasi'];

    // Plotting
    $stmt = $mysqli->prepare('SELECT id FROM plotting WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) $plottingStatus = 'selesai';
}

function stepClass(string $status): string {
    if ($status === 'disetujui' || $status === 'selesai') return 'active';
    if ($status === 'menunggu') return 'pending';
    return '';
}

function stepLabel(string $status): string {
    if ($status === 'disetujui' || $status === 'selesai') return 'Disetujui';
    if ($status === 'menunggu') return 'Menunggu';
    if ($status === 'ditolak') return 'Ditolak';
    return 'Belum';
}

function berkasStatusBadge(string $status): string {
    return match ($status) {
        'lengkap' => '<span class="badge badge-success">Lengkap</span>',
        'pending' => '<span class="badge badge-warning">Pending</span>',
        default => '<span class="badge badge-danger">Belum</span>',
    };
}

require __DIR__ . '/header.php';
?>

            <!-- Welcome Card -->
            <div class="welcome-card">
                <img src="../../assets/default-avatar.svg" alt="Profile" class="profile-img">
                <div class="welcome-text">
                    <h2>Selamat Datang, <?= $userName ?></h2>
                    <p><?= $userEmail ?></p>
                </div>
            </div>

            <!-- Informasi Kelompok Magang -->
            <div class="card">
                <div class="card-header dark">
                    <h3>Informasi Kelompok Magang</h3>
                </div>
                <div class="card-body kel-info">
                    <?php if ($kelompok): ?>
                    <div class="kel-details">
                        <p>Kelompok: <?= htmlspecialchars($kelompok['nama']) ?></p>
                        <p>Ketua Kelompok: <?= htmlspecialchars($kelompok['ketua_nama']) ?><?= $isKetua ? ' (anda)' : '' ?></p>
                        <p>Jumlah anggota: <?= count($anggotaList) ?> orang</p>
                    </div>
                    <div class="kel-action">
                        <button class="btn btn-dark" onclick="window.location.href='kelompok.php'">Kelola Kelompok</button>
                    </div>
                    <?php else: ?>
                    <div class="kel-details">
                        <p>Anda belum memiliki kelompok magang.</p>
                    </div>
                    <div class="kel-action">
                        <button class="btn btn-dark" onclick="window.location.href='kelompok.php'">Buat Kelompok</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Pendaftaran Magang -->
            <div class="card">
                <div class="card-header light">
                    <h3>Status Pendaftaran Magang</h3>
                </div>
                <div class="card-body status-tracking">
                    <div class="stepper-container">
                        <div class="step <?= stepClass($lokasiStatus) ?>">
                            <div class="step-circle">1</div>
                            <div class="step-title">Lokasi Magang</div>
                            <div class="step-subtitle"><?= stepLabel($lokasiStatus) ?></div>
                        </div>
                        <div class="step <?= stepClass($proposalStatus) ?>">
                            <div class="step-circle">2</div>
                            <div class="step-title">Proposal</div>
                            <div class="step-subtitle"><?= stepLabel($proposalStatus) ?></div>
                        </div>
                        <div class="step <?= stepClass($berkasStatus) ?>">
                            <div class="step-circle">3</div>
                            <div class="step-title">Berkas Admin</div>
                            <div class="step-subtitle"><?= stepLabel($berkasStatus) ?></div>
                        </div>
                        <div class="step <?= stepClass($buktiStatus) ?>">
                            <div class="step-circle">4</div>
                            <div class="step-title">Bukti Diterima</div>
                            <div class="step-subtitle"><?= stepLabel($buktiStatus) ?></div>
                        </div>
                        <div class="step <?= stepClass($plottingStatus) ?>">
                            <div class="step-circle">5</div>
                            <div class="step-title">Plotting</div>
                            <div class="step-subtitle"><?= stepLabel($plottingStatus) ?></div>
                        </div>
                    </div>
                    <div class="status-action">
                        <button class="btn btn-light-blue" onclick="window.location.href='pendaftaran.php'">Lanjutkan Pendaftaran</button>
                    </div>
                </div>
            </div>

            <!-- Anggota Kelompok -->
            <?php if (!empty($anggotaList)): ?>
            <div class="card">
                <div class="card-header dark">
                    <h3>Anggota Kelompok</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Peran</th>
                                <th>Status Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($anggotaList as $anggota): ?>
                            <tr>
                                <td><?= htmlspecialchars($anggota['nama']) ?></td>
                                <td><?= htmlspecialchars($anggota['nim']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($anggota['peran'])) ?></td>
                                <td><?= berkasStatusBadge($anggota['status_berkas']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>