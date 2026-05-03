<?php include 'header.php'; ?>
<?php $berkasGroups = getGroupsForBerkasVerification(); ?>

            <!-- PAGE: Verifikasi Berkas -->
            <div id="page-verifikasi-berkas" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Berkas</h1>
                    <span class="page-subtitle">Periksa kelengkapan berkas administrasi setiap kelompok</span>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>Jml. Berkas</th>
                                    <th>Tanggal Upload</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($berkasGroups)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada berkas anggota yang diajukan.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($berkasGroups as $berkas): ?>
                                        <?php $statusClass = statusBadgeClass($berkas['status']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($berkas['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($berkas['ketua_nama']) ?></td>
                                            <td><?= (int) $berkas['jumlah_berkas'] ?> / 5 berkas</td>
                                            <td><?= htmlspecialchars(formatDateIndo($berkas['tanggal_upload'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($berkas['status'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="berkas">
                                                    <input type="hidden" name="id" value="<?= $berkas['kelompok_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $berkas['status'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="berkas">
                                                    <input type="hidden" name="id" value="<?= $berkas['kelompok_id'] ?>">
                                                    <input type="hidden" name="action" value="ditolak">
                                                    <button type="submit" class="btn-tolak" <?= $berkas['status'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- end page-verifikasi-berkas -->

<?php include 'footer.php'; ?>