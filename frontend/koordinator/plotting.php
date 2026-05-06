<?php include 'header.php'; ?>
<?php
$plottingGroups = getGroupsForPlotting();
$plottingSummary = getPlottingSummary();
?>

            <!-- PAGE: Plotting -->
            <div id="page-plotting" class="page active">
                <div class="page-title-bar">
                    <h1>Plotting Kelompok</h1>
                    <span class="page-subtitle">Tetapkan lokasi magang dan dosen pembimbing untuk setiap kelompok</span>
                </div>

                <!-- Filter Bar -->
                <div class="plotting-toolbar">
                    <div class="plot-search-wrap">
                        <span class="plot-search-icon">&#128269;</span>
                        <input type="text" id="plot-search" class="plot-search-input"
                            placeholder="Cari nama kelompok atau ketua..." oninput="filterTabelPlotting()">
                    </div>
                    <div class="plot-filter-group">
                        <button class="plot-filter-btn active" id="filter-all"
                            onclick="filterStatus('all', this)">Semua</button>
                        <button class="plot-filter-btn" id="filter-selesai"
                            onclick="filterStatus('selesai', this)">Selesai</button>
                        <button class="plot-filter-btn" id="filter-menunggu"
                            onclick="filterStatus('menunggu', this)">Belum Diplot</button>
                    </div>
                </div>

                <!-- Tabel Plotting -->
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table" id="tabel-plotting">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>Jml. Anggota</th>
                                    <th>Dosen Pembimbing</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-plotting">
                                <?php if (empty($plottingGroups)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada kelompok untuk plotting.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($plottingGroups as $group): ?>
                                        <?php $statusClass = statusBadgeClass($group['status']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($group['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($group['ketua_nama']) ?></td>
                                            <td><?= (int) $group['anggota_count'] ?></td>
                                            <td class="col-dosen"><?= $group['dosen_pembimbing'] !== '' ? htmlspecialchars($group['dosen_pembimbing']) : '<em class="belum">Belum ditentukan</em>' ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= $group['status'] === 'selesai' ? 'Selesai' : 'Menunggu' ?></span></td>
                                            <td class="aksi-plot-group">
                                                <?php if ($group['status'] === 'selesai'): ?>
                                                    <button class="btn-edit-plot" onclick="bukaModalPlot(this, true, <?= $group['kelompok_id'] ?>)">&#9998; Edit</button>
                                                <?php else: ?>
                                                    <button class="btn-plot" onclick="bukaModalPlot(this, false, <?= $group['kelompok_id'] ?>)">&#43; Plot</button>
                                                <?php endif; ?>
                                                <button class="btn-detail-plot" onclick="bukaDetailKelompok(this)">Detail</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rekap Dosen Pembimbing -->
                <div class="card">
                    <div class="card-header-plain">
                        <h3>Rekapitulasi Dosen Pembimbing</h3>
                    </div>
                    <div class="card-body">
                        <div class="dosen-rekap-grid" id="dosen-rekap-grid">
                        <?php if (empty($plottingSummary)): ?>
                            <div style="grid-column:1/-1; padding:20px; text-align:center; color:#6B7280;">Belum ada data plotting untuk dosen pembimbing.</div>
                        <?php else: ?>
                            <?php foreach ($plottingSummary as $summary): ?>
                                <div class="dosen-rekap-card">
                                    <div class="dosen-rekap-title"><?= htmlspecialchars($summary['dosen_pembimbing']) ?></div>
                                    <div class="dosen-rekap-count"><?= (int) $summary['jumlah_kelompok'] ?> Kelompok</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    </div>
                </div>

            </div><!-- end page-plotting -->

<?php include 'footer.php'; ?>