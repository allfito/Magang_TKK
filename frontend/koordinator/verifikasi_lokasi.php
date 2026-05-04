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
                        <table class="table" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>Kelompok & Ketua</th>
                                    <th>Perusahaan & Bidang</th>
                                    <th>Alamat & Kontak</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($locations)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding: 30px; color:#6B7280;">Belum ada pengajuan lokasi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($locations as $location): ?>
                                        <?php $statusClass = statusBadgeClass($location['status_verifikasi']); ?>
                                        <tr>
                                            <td>
                                                <strong style="display:block; margin-bottom: 4px; font-size: 14px; color: #1E293B;"><?= htmlspecialchars($location['kelompok_nama']) ?></strong>
                                                <span style="color: #64748B;">Ketua: <?= htmlspecialchars($location['ketua_nama']) ?></span><br>
                                                <span style="color: #94A3B8; font-size: 11px;">Diajukan: <?= htmlspecialchars(formatDateIndo($location['created_at'])) ?></span>
                                            </td>
                                            <td>
                                                <strong style="display:block; margin-bottom: 4px; color: #1E293B;"><?= htmlspecialchars($location['perusahaan']) ?></strong>
                                                <span style="color: #64748B;"><?= htmlspecialchars($location['bidang']) ?></span>
                                            </td>
                                            <td style="max-width: 250px;">
                                                <div style="margin-bottom: 6px; line-height: 1.4; color: #1E293B;">
                                                    <?= htmlspecialchars($location['alamat']) ?>
                                                </div>
                                                <a href="https://maps.google.com/maps?q=<?= urlencode($location['alamat'] ?? 'Indonesia') ?>" target="_blank" style="display: inline-block; margin-bottom: 8px; font-size: 11px; font-weight: 600; color: #2563EB; text-decoration: none; background: #DBEAFE; padding: 4px 8px; border-radius: 4px;">&#128205; Buka di Google Maps</a>
                                                <div style="color: #64748B;">
                                                    CP: <?= htmlspecialchars($location['nama_pimpinan']) ?> (<?= htmlspecialchars($location['telepon']) ?>)
                                                </div>
                                            </td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($location['status_verifikasi'])) ?></span></td>
                                            <td>
                                                <div style="display:flex; flex-direction:column; gap:8px;">
                                                    <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                        <input type="hidden" name="type" value="lokasi">
                                                        <input type="hidden" name="id" value="<?= $location['lokasi_id'] ?>">
                                                        <input type="hidden" name="action" value="disetujui">
                                                        <button type="submit" class="btn" style="width: 100%; background: #D1FAE5; color: #10B981; padding: 6px 12px; font-size: 12px;" <?= $location['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                    </form>
                                                    <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                        <input type="hidden" name="type" value="lokasi">
                                                        <input type="hidden" name="id" value="<?= $location['lokasi_id'] ?>">
                                                        <input type="hidden" name="action" value="ditolak">
                                                        <button type="submit" class="btn" style="width: 100%; background: #FEE2E2; color: #EF4444; padding: 6px 12px; font-size: 12px;" <?= $location['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                    </form>
                                                </div>
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