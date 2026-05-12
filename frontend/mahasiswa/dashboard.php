<?php
require_once __DIR__ . '/../../backend/helpers/MahasiswaHelper.php';
MahasiswaHelper::requireLogin();

$pageTitle = 'Dashboard Mahasiswa - SIMM';
$activePage = 'dashboard';

$user = MahasiswaHelper::currentUser();
$userName = $user ? htmlspecialchars($user['nama']) : 'Mahasiswa';
$userEmail = $user ? htmlspecialchars($user['email']) : '';

// Get dashboard data via Controller
require_once __DIR__ . '/../../backend/controllers/MahasiswaDashboardController.php';

$userId = (int) $_SESSION['user_id'];
$controller = new MahasiswaDashboardController();
$dashboardData = $controller->getDashboardData($userId);

$kelompok = $dashboardData['kelompok'];
$kelompokId = $dashboardData['kelompokId'];
$isKetua = $dashboardData['isKetua'];
$anggotaList = $dashboardData['anggotaList'];
$lokasiStatus = $dashboardData['lokasiStatus'];
$proposalStatus = $dashboardData['proposalStatus'];
$berkasStatus = $dashboardData['berkasStatus'];
$buktiStatus = $dashboardData['buktiStatus'];
$plottingStatus = $dashboardData['plottingStatus'];

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