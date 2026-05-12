<?php include 'header.php'; ?>
<?php 
    $sortBy = $_GET['sort'] ?? 'tanggal_terbaru';
    $proposals = KoordinatorHelper::getGroupsForProposalVerification($sortBy); 
?>

            <!-- PAGE: Verifikasi Proposal -->
            <div id="page-verifikasi-proposal" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Proposal</h1>
                    <span class="page-subtitle">Periksa proposal magang yang diajukan oleh setiap kelompok</span>
                </div>
                
                <!-- Sort Controls -->
                <div style="margin-bottom: 20px; display: flex; gap: 12px; align-items: center;">
                    <label for="sort-select" style="font-size: 13px; font-weight: 600; color: #334155;">Urutkan:</label>
                    <select id="sort-select" onchange="changeSortPage(this.value)" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 6px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none;">
                        <option value="tanggal_terbaru" <?= $sortBy === 'tanggal_terbaru' ? 'selected' : '' ?>>📅 Tanggal Terbaru</option>
                        <option value="tanggal_terlama" <?= $sortBy === 'tanggal_terlama' ? 'selected' : '' ?>>📅 Tanggal Terlama</option>
                        <option value="nama_a" <?= $sortBy === 'nama_a' ? 'selected' : '' ?>>📖 Nama Kelompok (A-Z)</option>
                        <option value="nama_z" <?= $sortBy === 'nama_z' ? 'selected' : '' ?>>📖 Nama Kelompok (Z-A)</option>
                        <option value="ketua_a" <?= $sortBy === 'ketua_a' ? 'selected' : '' ?>>👤 Nama Ketua (A-Z)</option>
                        <option value="ketua_z" <?= $sortBy === 'ketua_z' ? 'selected' : '' ?>>👤 Nama Ketua (Z-A)</option>
                        <option value="status_menunggu" <?= $sortBy === 'status_menunggu' ? 'selected' : '' ?>>⏳ Status Menunggu Duluan</option>
                    </select>
                    <input type="text" id="search-proposal" placeholder="Cari proposal..." style="padding: 6px 10px; border: 1px solid #DDEAF5; border-radius: 4px; font-size: 13px; font-family: 'Inter', sans-serif;"/>
                </div>
                
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>File Proposal</th>
                                    <th>Tanggal Diajukan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($proposals)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada proposal yang diajukan.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($proposals as $proposal): ?>
                                        <?php $statusClass = KoordinatorHelper::statusBadgeClass($proposal['status_verifikasi']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($proposal['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($proposal['ketua_nama']) ?></td>
                                            <td><a href="<?= htmlspecialchars($proposal['file_path']) ?>" class="link-file" target="_blank"><?= htmlspecialchars(basename($proposal['file_path'])) ?></a></td>
                                            <td><?= htmlspecialchars(KoordinatorHelper::formatDateIndo($proposal['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($proposal['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="proposal">
                                                    <input type="hidden" name="id" value="<?= $proposal['proposal_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $proposal['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="proposal">
                                                    <input type="hidden" name="id" value="<?= $proposal['proposal_id'] ?>">
                                                    <input type="hidden" name="action" value="ditolak">
                                                    <button type="submit" class="btn-tolak" <?= $proposal['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- end page-verifikasi-proposal -->

<?php include 'footer.php'; ?>