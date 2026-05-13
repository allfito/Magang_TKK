<?php include 'header.php'; ?>
<?php 
    $sortBy = $_GET['sort'] ?? 'tanggal_terbaru';
    $buktiList = KoordinatorHelper::getGroupsForBuktiVerification($sortBy); 
?>

            <!-- PAGE: Verifikasi Bukti Diterima -->
            <div id="page-verifikasi-bukti" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Bukti Diterima</h1>
                    <span class="page-subtitle">Periksa dan setujui bukti penerimaan magang yang diajukan kelompok</span>
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
                    <input type="text" id="search-bukti" placeholder="Cari bukti..." style="padding: 6px 10px; border: 1px solid #DDEAF5; border-radius: 4px; font-size: 13px; font-family: 'Inter', sans-serif;"/>
                </div>
                
                <div class="card">
                    <div class="card-body p-0">
                        <table id="tabel-bukti" class="table">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>Tempat Diterima</th>
                                    <th>File Bukti</th>
                                    <th>Tanggal Diajukan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($buktiList)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada pengajuan bukti diterima.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($buktiList as $bukti): ?>
                                        <?php $statusClass = KoordinatorHelper::statusBadgeClass($bukti['status_verifikasi']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($bukti['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($bukti['ketua_nama']) ?></td>
                                            <td><?= htmlspecialchars($bukti['tempat_diterima']) ?></td>
                                            <td><a href="../../backend/helpers/serve_file.php?path=<?= urlencode($bukti['file_path']) ?>" class="link-file" target="_blank"><?= htmlspecialchars(basename($bukti['file_path'])) ?></a></td>
                                            <td><?= htmlspecialchars(KoordinatorHelper::formatDateIndo($bukti['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($bukti['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="bukti">
                                                    <input type="hidden" name="id" value="<?= $bukti['bukti_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $bukti['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="bukti">
                                                    <input type="hidden" name="id" value="<?= $bukti['bukti_id'] ?>">
                                                    <input type="hidden" name="action" value="ditolak">
                                                    <button type="submit" class="btn-tolak" <?= $bukti['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- end page-verifikasi-bukti -->

<?php include 'footer.php'; ?>
