<?php include 'header.php'; ?>
<?php $proposals = getGroupsForProposalVerification(); ?>

            <!-- PAGE: Verifikasi Proposal -->
            <div id="page-verifikasi-proposal" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Proposal</h1>
                    <span class="page-subtitle">Periksa proposal magang yang diajukan oleh setiap kelompok</span>
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
                                        <?php $statusClass = statusBadgeClass($proposal['status_verifikasi']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($proposal['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($proposal['ketua_nama']) ?></td>
                                            <td><a href="<?= htmlspecialchars($proposal['file_path']) ?>" class="link-file" target="_blank"><?= htmlspecialchars(basename($proposal['file_path'])) ?></a></td>
                                            <td><?= htmlspecialchars(formatDateIndo($proposal['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($proposal['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="proposal">
                                                    <input type="hidden" name="id" value="<?= $proposal['proposal_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $proposal['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
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