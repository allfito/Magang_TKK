<?php include 'header.php'; ?>
<?php
$activeCount = KoordinatorHelper::getActiveGroupCount();
$pendingLocationCount = KoordinatorHelper::getPendingLocationCount();
$pendingProposalCount = KoordinatorHelper::getPendingProposalCount();
$pendingBerkasCount = KoordinatorHelper::getPendingBerkasCount();
$pendingBuktiCount = KoordinatorHelper::getPendingBuktiCount();
$pendingGroups = KoordinatorHelper::getGroupsPendingVerification();
?>

            <!-- PAGE: Dashboard -->
            <div id="page-dashboard" class="page active">

                <!-- Welcome Card -->
                <div class="welcome-card">
                    <img src="../../assets/default-avatar.svg" alt="Profile" class="profile-img">
                    <div class="welcome-text">
                        <h2>Selamat datang, Pak Sultan</h2>
                        <p>Koordinator Bidang &ndash; Teknik Komputer</p>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="stat-grid">
                    <div class="stat-card" onclick="window.location.href='dashboard.php'">
                        <div class="stat-number"><?= $activeCount ?></div>
                        <div class="stat-label">Kelompok Aktif</div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='verifikasi_lokasi.php'">
                        <div class="stat-number"><?= $pendingLocationCount ?></div>
                        <div class="stat-label">Verifikasi Lokasi</div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='verifikasi_proposal.php'">
                        <div class="stat-number"><?= $pendingProposalCount ?></div>
                        <div class="stat-label">Verifikasi Proposal</div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='verifikasi_berkas.php'">
                        <div class="stat-number"><?= $pendingBerkasCount ?></div>
                        <div class="stat-label">Verifikasi Berkas</div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='verifikasi_bukti.php'">
                        <div class="stat-number"><?= $pendingBuktiCount ?></div>
                        <div class="stat-label">Verifikasi Bukti</div>
                    </div>
                </div>

                <!-- Tabel Daftar Kelompok -->
                <div class="card">
                    <div class="card-header-plain">
                        <h3>Daftar Kelompok Perlu Verifikasi</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table" id="tabel-kelompok">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>Jumlah Anggota</th>
                                    <th>Jenis Verifikasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingGroups)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada kelompok yang menunggu verifikasi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingGroups as $group): ?>
                                    <?php
                                        $redirectPage = 'dashboard.php';
                                        if ($group['jenis_verifikasi'] === 'Lokasi Magang') $redirectPage = 'verifikasi_lokasi.php';
                                        elseif ($group['jenis_verifikasi'] === 'Proposal') $redirectPage = 'verifikasi_proposal.php';
                                        elseif ($group['jenis_verifikasi'] === 'Berkas') $redirectPage = 'verifikasi_berkas.php';
                                        elseif ($group['jenis_verifikasi'] === 'Bukti Diterima') $redirectPage = 'verifikasi_bukti.php';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($group['kelompok_nama']) ?></td>
                                        <td><?= htmlspecialchars($group['ketua_nama']) ?></td>
                                        <td><?= (int) KoordinatorHelper::getMembersCount($group['kelompok_id']) ?></td>
                                        <td><?= htmlspecialchars($group['jenis_verifikasi']) ?></td>
                                        <td><span class="badge badge-warning">Menunggu</span></td>
                                        <td><button class="btn-verifikasi" onclick="window.location.href='<?= $redirectPage ?>'">Verifikasi</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- end page-dashboard -->

<?php include 'footer.php'; ?>