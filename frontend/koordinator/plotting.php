<?php include 'header.php'; ?>
<?php
$sortBy = $_GET['sort'] ?? 'nama_a';
$plottingGroups = KoordinatorHelper::getGroupsForPlotting($sortBy);
$plottingSummary = KoordinatorHelper::getPlottingSummary();
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!-- PAGE: Plotting -->
            <div id="page-plotting" class="page active">
                <div class="page-title-bar">
                    <h1>Plotting Kelompok</h1>
                    <span class="page-subtitle">Tetapkan dosen pembimbing untuk setiap kelompok</span>
                </div>

                <?php if ($successMessage): ?>
                    <div style="background-color: #DEF7EC; color: #03543F; padding: 14px 20px; margin-bottom: 20px; border-radius: 10px; border: 1px solid #BCF0DA; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <i class="fas fa-check-circle" style="font-size: 16px; color: #31C48D;"></i>
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div style="background-color: #FDE8E8; color: #9B1C1C; padding: 14px 20px; margin-bottom: 20px; border-radius: 10px; border: 1px solid #FBD5D5; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <i class="fas fa-exclamation-circle" style="font-size: 16px; color: #F98080;"></i>
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

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
                    <div style="display: flex; gap: 8px; align-items: center; margin-left: auto;">
                        <button class="btn" style="background:#10B981; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:600; font-size:13px;" onclick="document.getElementById('modal-tambah-dosen').classList.add('open')">&#43; Tambah Dosen</button>
                        <label for="sort-plotting" style="font-size: 13px; font-weight: 600; color: #334155; white-space: nowrap; margin-left: 10px;">Urutkan:</label>
                        <select id="sort-plotting" onchange="changeSortPage(this.value)" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 6px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none;">
                            <option value="nama_a" <?= $sortBy === 'nama_a' ? 'selected' : '' ?>>📖 Nama Kelompok (A-Z)</option>
                            <option value="nama_z" <?= $sortBy === 'nama_z' ? 'selected' : '' ?>>📖 Nama Kelompok (Z-A)</option>
                            <option value="ketua_a" <?= $sortBy === 'ketua_a' ? 'selected' : '' ?>>👤 Nama Ketua (A-Z)</option>
                            <option value="ketua_z" <?= $sortBy === 'ketua_z' ? 'selected' : '' ?>>👤 Nama Ketua (Z-A)</option>
                            <option value="status_selesai" <?= $sortBy === 'status_selesai' ? 'selected' : '' ?>>✅ Status Sudah Diplot</option>
                        </select>
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
                                        <?php $statusClass = KoordinatorHelper::statusBadgeClass($group['status']); ?>
                                        <tr>
                                             <td><?= htmlspecialchars(ucwords(strtolower($group['kelompok_nama']))) ?></td>
                                             <td><?= htmlspecialchars(ucwords(strtolower($group['ketua_nama']))) ?></td>
                                             <td><?= (int) $group['anggota_count'] ?></td>
                                             <td class="col-dosen"><?= $group['dosen_pembimbing'] !== '' ? htmlspecialchars(ucwords(strtolower($group['dosen_pembimbing']))) : '<em class="belum">Belum ditentukan</em>' ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= $group['status'] === 'selesai' ? 'Selesai' : 'Menunggu' ?></span></td>
                                            <td class="aksi-plot-group">
                                                <?php if ($group['status'] === 'selesai'): ?>
                                                    <button class="btn-edit-plot" onclick="bukaModalPlot(this, true, <?= $group['kelompok_id'] ?>)">&#9998; Edit</button>
                                                    <button type="button" class="btn-hapus-plot" onclick="konfirmasiHapusPlot(<?= $group['kelompok_id'] ?>, '<?= htmlspecialchars($group['kelompok_nama'], ENT_QUOTES) ?>')" style="background:#EF4444; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:12px; font-weight:600; margin-left:4px;">Hapus</button>
                                                <?php else: ?>
                                                    <button class="btn-plot" onclick="bukaModalPlot(this, false, <?= $group['kelompok_id'] ?>)">&#43; Plot</button>
                                                <?php endif; ?>
                                                <?php
                                                $members = KoordinatorHelper::getGroupMembers($group['kelompok_id']);
                                                $membersJson = htmlspecialchars(json_encode($members), ENT_QUOTES, 'UTF-8');
                                                ?>
                                                <button class="btn-detail-plot" data-members="<?= $membersJson ?>" onclick="bukaDetailKelompok(this)">Detail</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rekap Dosen Pembimbing (Hidden as per screenshot design which merges it into modern cards) -->
                <div class="card" style="display: none;">
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

                <!-- Daftar Dosen Pembimbing -->
                <div class="card" style="margin-top: 24px;">
                    <div class="card-header-plain" style="border-bottom: 1.5px solid #F1F5F9; padding: 16px 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; font-family: 'Inter', sans-serif;">Daftar Dosen Pembimbing</h3>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="dosen-cards-container">
                            <?php 
                            $allDosenList = KoordinatorHelper::getAllDosen();
                            if (empty($allDosenList)): 
                            ?>
                                <div style="width: 100%; text-align: center; padding: 30px; color: #94A3B8;">
                                    Belum ada dosen yang terdaftar.
                                </div>
                            <?php else: ?>
                                <?php foreach ($allDosenList as $dosenItem): ?>
                                    <?php 
                                    $dosenNama = ucwords(strtolower($dosenItem['nama']));
                                    $bebanCount = 0;
                                    foreach ($plottingSummary as $sum) {
                                        if ($sum['dosen_pembimbing'] === $dosenNama) {
                                            $bebanCount = (int)$sum['jumlah_kelompok'];
                                            break;
                                        }
                                    }
                                    
                                    // Generate dynamic colors for circle avatar based on crc32 hash
                                    $avatarColors = ['#6366F1', '#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B', '#EF4444'];
                                    $colorHash = abs(crc32($dosenNama));
                                    $bgColor = $avatarColors[$colorHash % count($avatarColors)];
                                    $initial = strtoupper(substr(trim($dosenNama), 0, 1));
                                    ?>
                                    <div class="dosen-card-item">
                                        <div class="dosen-card-left">
                                            <div class="dosen-card-avatar" style="background: <?= $bgColor ?>;"><?= htmlspecialchars($initial) ?></div>
                                            <div class="dosen-card-info">
                                                <h4 class="dosen-card-name" title="<?= htmlspecialchars($dosenNama) ?>"><?= htmlspecialchars($dosenNama) ?></h4>
                                                <p class="dosen-card-details" style="margin-bottom: 2px;">NIP: <?= $dosenItem['nip'] ? htmlspecialchars($dosenItem['nip']) : '-' ?></p>
                                                <p class="dosen-card-details" style="margin-bottom: 2px;">Tlp: <?= $dosenItem['no_tlp'] ? htmlspecialchars($dosenItem['no_tlp']) : '-' ?></p>
                                                <p class="dosen-card-details" style="color: #6366F1; font-weight: 600; margin-top: 4px;"><?= $bebanCount ?> kelompok dibimbing</p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-hapus-dosen-card" onclick="konfirmasiHapusDosen(<?= $dosenItem['id'] ?>, '<?= htmlspecialchars($dosenItem['nama'], ENT_QUOTES) ?>', <?= $bebanCount ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div><!-- end page-plotting -->

<?php include 'footer.php'; ?>