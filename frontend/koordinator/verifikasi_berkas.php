<?php include 'header.php'; ?>
<?php 
    $sortBy = $_GET['sort'] ?? 'tanggal_terbaru';
    $berkasGroups = KoordinatorHelper::getGroupsForBerkasVerification($sortBy); 
?>

            <!-- PAGE: Verifikasi Berkas -->
            <div id="page-verifikasi-berkas" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Berkas</h1>
                    <span class="page-subtitle">Periksa kelengkapan berkas administrasi setiap kelompok</span>
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
                    <input type="text" id="search-berkas" placeholder="Cari berkas..." style="padding: 6px 10px; border: 1px solid #DDEAF5; border-radius: 4px; font-size: 13px; font-family: 'Inter', sans-serif;" />
                </div>
                
                <?php if (empty($berkasGroups)): ?>
                    <div class="card">
                        <div class="card-body" style="text-align:center; padding: 30px; color:#6B7280;">
                            Belum ada berkas anggota yang diajukan.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($berkasGroups as $berkas): ?>
                        <?php $statusClass = KoordinatorHelper::statusBadgeClass($berkas['status']); ?>
                        <details class="card grupo-dropdown" style="margin-bottom: 20px; border-radius: 8px; overflow: hidden; border: 2px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <summary class="card-header-plain" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px; background: linear-gradient(90deg, #F8FBFE 0%, #F1F5F9 100%); cursor: pointer; outline: none; list-style: none; transition: all 0.25s ease; border-radius: 6px; margin: 2px;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-folder-open" style="color: #3B82F6; font-size: 16px;"></i>
                                        <h3 style="margin:0 0 5px 0; font-size: 16px; color: #1E293B;">Kelompok <?= htmlspecialchars($berkas['kelompok_nama']) ?></h3>
                                    </div>
                                    <span style="font-size:13px; color:#64748B; display: block; margin-left: 26px;">
                                        Ketua: <strong><?= htmlspecialchars($berkas['ketua_nama']) ?></strong> &bull; 
                                        Tanggal Upload: <?= htmlspecialchars(KoordinatorHelper::formatDateIndo($berkas['tanggal_upload'])) ?> &bull; 
                                        Status: <span class="badge <?= $statusClass ?>" style="font-size:11px;"><?= htmlspecialchars(ucfirst($berkas['status'])) ?></span>
                                    </span>
                                </div>
                                <div style="color: #3B82F6; display: flex; align-items: center; gap: 8px; flex-shrink: 0; margin-left: 10px;">
                                    <span style="font-size: 12px; color: #64748B; font-weight: 500;">Lihat</span>
                                    <i class="fas fa-chevron-down" style="font-size: 18px; transition: transform 0.3s ease; transform-origin: center;"></i>
                                </div>
                            </summary>
                            <div class="card-body p-0" style="border-top: 1px solid #E2E8F0;">
                                <?php 
                                $listBerkas = KoordinatorHelper::getBerkasByGroup((int)$berkas['kelompok_id']); 
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
                                        <details class="mahasiswa-dropdown" style="border-bottom: 1px solid #E2E8F0;">
                                            <summary style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px; background-color: #F1F5F9; cursor: pointer; outline: none; list-style: none; user-select: none; transition: all 0.2s ease;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-user-circle" style="color: #0EA5E9; font-size: 16px;"></i>
                                                    <h4 style="margin: 0; font-size: 14px; color: #334155; font-weight: 600;"><?= htmlspecialchars($mahasiswa) ?></h4>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 15px;">
                                                    <?php $anggotaId = $files[0]['anggota_id']; ?>
                                                    <div style="display:flex; gap:8px;">
                                                        <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                            <input type="hidden" name="type" value="berkas_mahasiswa">
                                                            <input type="hidden" name="id" value="<?= $anggotaId ?>">
                                                            <input type="hidden" name="action" value="disetujui">
                                                            <button type="submit" class="btn" style="background:#D1FAE5; color:#10B981; padding:4px 10px; font-size:12px; font-weight:600; border-radius:4px; border:1px solid #10B981; cursor:pointer;" onclick="event.stopPropagation();">Setuju Semua</button>
                                                        </form>
                                                        <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                            <input type="hidden" name="type" value="berkas_mahasiswa">
                                                            <input type="hidden" name="id" value="<?= $anggotaId ?>">
                                                            <input type="hidden" name="action" value="ditolak">
                                                            <button type="submit" class="btn" style="background:#FEE2E2; color:#EF4444; padding:4px 10px; font-size:12px; font-weight:600; border-radius:4px; border:1px solid #EF4444; cursor:pointer;" onclick="event.stopPropagation();">Tolak Semua</button>
                                                        </form>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <i class="fas fa-chevron-down" style="color: #64748B; transition: transform 0.3s ease; display: inline-block;"></i>
                                                    </div>
                                                </div>
                                            </summary>
                                            <div style="padding: 0;">
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
                                                                <td><a href="../../backend/helpers/serve_file.php?path=<?= urlencode($b['file_path']) ?>" target="_blank" style="color:#2563EB; text-decoration:none; font-weight:600;">Lihat File</a></td>
                                                                <td><span class="badge <?= KoordinatorHelper::statusBadgeClass($b['status_verifikasi']) ?>"><?= htmlspecialchars(ucfirst($b['status_verifikasi'])) ?></span></td>
                                                                <td>
                                                                    <div style="display:flex; gap:8px;">
                                                                        <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                                            <input type="hidden" name="type" value="berkas_satuan">
                                                                            <input type="hidden" name="id" value="<?= $b['berkas_id'] ?>">
                                                                            <input type="hidden" name="action" value="disetujui">
                                                                            <button type="submit" class="btn" style="background:#D1FAE5; color:#10B981; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:4px; border:1px solid #10B981; cursor:pointer;" <?= $b['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                                        </form>
                                                                        <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
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
                                            </div>
                                        </details>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div><!-- end page-verifikasi-berkas -->

<?php include 'footer.php'; ?>