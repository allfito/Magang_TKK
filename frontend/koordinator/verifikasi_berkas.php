<?php include 'header.php'; ?>
<?php $berkasGroups = getGroupsForBerkasVerification(); ?>

            <!-- PAGE: Verifikasi Berkas -->
            <div id="page-verifikasi-berkas" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Berkas</h1>
                    <span class="page-subtitle">Periksa kelengkapan berkas administrasi setiap kelompok</span>
                </div>
                <?php if (empty($berkasGroups)): ?>
                    <div class="card">
                        <div class="card-body" style="text-align:center; padding: 30px; color:#6B7280;">
                            Belum ada berkas anggota yang diajukan.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($berkasGroups as $berkas): ?>
                        <?php $statusClass = statusBadgeClass($berkas['status']); ?>
                        <details class="card" style="margin-bottom: 20px; border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <summary class="card-header-plain" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px; background: #fff; cursor: pointer; outline: none; list-style: none;">
                                <div>
                                    <h3 style="margin:0 0 5px 0; font-size: 16px; color: #1E293B;">Kelompok <?= htmlspecialchars($berkas['kelompok_nama']) ?></h3>
                                    <span style="font-size:13px; color:#64748B;">
                                        Ketua: <strong><?= htmlspecialchars($berkas['ketua_nama']) ?></strong> &bull; 
                                        Tanggal Upload: <?= htmlspecialchars(formatDateIndo($berkas['tanggal_upload'])) ?> &bull; 
                                        Status: <span class="badge <?= $statusClass ?>" style="font-size:11px;"><?= htmlspecialchars(ucfirst($berkas['status'])) ?></span>
                                    </span>
                                </div>
                                <div style="color: #94A3B8;">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </summary>
                            <div class="card-body p-0" style="border-top: 1px solid #E2E8F0;">
                                <?php 
                                $listBerkas = getBerkasByGroup((int)$berkas['kelompok_id']); 
                                if (empty($listBerkas)): 
                                ?>
                                    <div style="text-align:center; padding:20px; color:#6B7280;">Belum ada berkas yang diunggah</div>
                                <?php else: 
                                    // Group files by Mahasiswa (Anggota)
                                    $berkasByMahasiswa = [];
                                    foreach ($listBerkas as $b) {
                                        $key = $b['nim'] . ' - ' . $b['anggota_nama'];
                                        $berkasByMahasiswa[$key][] = $b;
                                    }
                                ?>
                                    <?php foreach ($berkasByMahasiswa as $mahasiswa => $files): ?>
                                        <div style="background-color: #F1F5F9; padding: 10px 20px; border-bottom: 1px solid #E2E8F0; border-top: 1px solid #E2E8F0;">
                                            <h4 style="margin: 0; font-size: 14px; color: #334155; font-weight: 600;">👨‍🎓 <?= htmlspecialchars($mahasiswa) ?></h4>
                                        </div>
                                        <table class="table" style="font-size:13px; margin:0; border-bottom: none;">
                                            <thead>
                                                <tr style="background-color:#F8FAFC;">
                                                    <th style="width: 25%; padding-left: 20px;">Jenis Berkas</th>
                                                    <th style="width: 25%;">File Berkas</th>
                                                    <th style="width: 15%;">Status</th>
                                                    <th style="width: 35%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($files as $b): ?>
                                                    <tr>
                                                        <td style="padding-left: 20px;"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $b['jenis_berkas']))) ?></td>
                                                        <td><a href="../../<?= htmlspecialchars($b['file_path']) ?>" target="_blank" style="color:#2563EB; text-decoration:none; font-weight:600;">Lihat File</a></td>
                                                        <td><span class="badge <?= statusBadgeClass($b['status_verifikasi']) ?>"><?= htmlspecialchars(ucfirst($b['status_verifikasi'])) ?></span></td>
                                                        <td>
                                                            <div style="display:flex; gap:8px;">
                                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                                    <input type="hidden" name="type" value="berkas_satuan">
                                                                    <input type="hidden" name="id" value="<?= $b['berkas_id'] ?>">
                                                                    <input type="hidden" name="action" value="disetujui">
                                                                    <button type="submit" class="btn" style="background:#D1FAE5; color:#10B981; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:4px; border:1px solid #10B981; cursor:pointer;" <?= $b['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                                </form>
                                                                <form method="POST" action="../../backend/koordinator/verifikasi.php" style="margin:0;">
                                                                    <input type="hidden" name="type" value="berkas_satuan">
                                                                    <input type="hidden" name="id" value="<?= $b['berkas_id'] ?>">
                                                                    <input type="hidden" name="action" value="ditolak">
                                                                    <button type="submit" class="btn" style="background:#FEE2E2; color:#EF4444; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:4px; border:1px solid #EF4444; cursor:pointer;" <?= $b['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div><!-- end page-verifikasi-berkas -->

<?php include 'footer.php'; ?>