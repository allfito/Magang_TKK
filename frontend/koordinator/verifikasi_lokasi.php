<?php include 'header.php'; ?>
<?php $locations = getGroupsForLocationVerification(); ?>

            <!-- PAGE: Verifikasi Lokasi -->
            <div id="page-verifikasi-lokasi" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Lokasi Magang</h1>
                    <span class="page-subtitle">Periksa dan setujui lokasi magang yang diajukan kelompok</span>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>Lokasi Magang</th>
                                    <th>Tanggal Diajukan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($locations)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada pengajuan lokasi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($locations as $location): ?>
                                        <?php $statusClass = statusBadgeClass($location['status_verifikasi']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($location['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($location['ketua_nama']) ?></td>
                                            <td><?= htmlspecialchars($location['perusahaan']) ?></td>
                                            <td><?= htmlspecialchars(formatDateIndo($location['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($location['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="lokasi">
                                                    <input type="hidden" name="id" value="<?= $location['lokasi_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $location['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="lokasi">
                                                    <input type="hidden" name="id" value="<?= $location['lokasi_id'] ?>">
                                                    <input type="hidden" name="action" value="ditolak">
                                                    <button type="submit" class="btn-tolak" <?= $location['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- end page-verifikasi-lokasi -->

<?php include 'footer.php'; ?>