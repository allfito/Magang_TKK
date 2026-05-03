<?php include 'header.php'; ?>
<?php $buktiList = getGroupsForBuktiVerification(); ?>

            <!-- PAGE: Verifikasi Bukti Diterima -->
            <div id="page-verifikasi-bukti" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Bukti Diterima</h1>
                    <span class="page-subtitle">Periksa dan setujui bukti penerimaan magang yang diajukan kelompok</span>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table">
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
                                        <?php $statusClass = statusBadgeClass($bukti['status_verifikasi']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($bukti['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($bukti['ketua_nama']) ?></td>
                                            <td><?= htmlspecialchars($bukti['tempat_diterima']) ?></td>
                                            <td><a href="../../<?= htmlspecialchars($bukti['file_path']) ?>" class="link-file" target="_blank"><?= htmlspecialchars(basename($bukti['file_path'])) ?></a></td>
                                            <td><?= htmlspecialchars(formatDateIndo($bukti['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($bukti['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="bukti">
                                                    <input type="hidden" name="id" value="<?= $bukti['bukti_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $bukti['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
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
