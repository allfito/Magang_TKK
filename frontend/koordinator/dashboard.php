<?php include 'header.php'; ?>
<?php
$db = Database::getInstance()->getConnection();
// Auto-correct any groups having 'Pengurusan Surat Pengantar' progress but haven't submitted anything yet
$db->query("
    UPDATE kelompok k
    SET k.status_progress = 'Pengajuan Tempat'
    WHERE k.status_progress = 'Pengurusan Surat Pengantar'
      AND NOT EXISTS (SELECT 1 FROM pendaftaran_lokasi pl WHERE pl.kelompok_id = k.id)
      AND NOT EXISTS (SELECT 1 FROM proposal pr WHERE pr.kelompok_id = k.id)
      AND NOT EXISTS (SELECT 1 FROM bukti_diterima bd WHERE bd.kelompok_id = k.id)
");

$activeCount          = KoordinatorHelper::getActiveGroupCount();
$pendingLocationCount = KoordinatorHelper::getPendingLocationCount();
$pendingProposalCount = KoordinatorHelper::getPendingProposalCount();
$pendingBerkasCount   = KoordinatorHelper::getPendingBerkasCount();
$pendingBuktiCount    = KoordinatorHelper::getPendingBuktiCount();
$pendingGroups        = KoordinatorHelper::getGroupsPendingVerification();

// Query counts for progress statuses
$db = Database::getInstance()->getConnection();
$countsQuery = $db->query("
    SELECT 
        SUM(status_progress = 'Pengajuan Tempat') as pengajuan_tempat,
        SUM(status_progress = 'ACC Pembuatan Proposal') as acc_proposal,
        SUM(status_progress = 'Pengurusan Surat Pengantar') as pengurusan_surat,
        SUM(status_progress = 'Pengiriman Proposal') as pengiriman_proposal,
        SUM(status_progress = 'Surat Penerimaan Magang') as penerimaan_magang,
        SUM(status_progress = 'REVISI TEMPAT') as revisi_tempat,
        SUM(status_progress IN ('DI TOLAK', 'Pengajuan di Tolak Lokasi', 'Ditolak Perusahaan')) as ditolak
    FROM kelompok
");
$counts = $countsQuery->fetch_assoc();

$countPengajuanTempat = (int)($counts['pengajuan_tempat'] ?? 0);
$countAccProposal     = (int)($counts['acc_proposal'] ?? 0);
$countPengurusanSurat = (int)($counts['pengurusan_surat'] ?? 0);
$countPengiriman      = (int)($counts['pengiriman_proposal'] ?? 0);
$countPenerimaan      = (int)($counts['penerimaan_magang'] ?? 0);
$countRevisiTempat    = (int)($counts['revisi_tempat'] ?? 0);
$countDitolak         = (int)($counts['ditolak'] ?? 0);

$currentUser = KoordinatorHelper::getCurrentUser();
$namaUser    = ucwords(strtolower(trim($currentUser['nama'] ?? 'Koordinator')));
?>

<style>
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 20px;
        margin-bottom: 30px;
    }
    @media (max-width: 1200px) {
        .stat-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    @media (max-width: 768px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    @media (max-width: 480px) {
        .stat-grid {
            grid-template-columns: 1fr !important;
        }
    }
    .stat-card {
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }
    .stat-card.active-card {
        border-color: #00CFE8 !important;
        box-shadow: 0 8px 24px rgba(0, 207, 232, 0.3) !important;
        transform: translateY(-4px);
    }
    #tabel-kelompok th {
        background-color: #F1F5F9;
        color: #1E293B;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        border: 1px solid #E2E8F0;
        padding: 12px 10px;
        white-space: nowrap;
    }
    #tabel-kelompok td {
        font-size: 13px;
        border: 1px solid #E2E8F0;
        padding: 10px 12px;
        vertical-align: middle;
        color: #334155;
    }
    #tabel-kelompok tr:hover td {
        background-color: #F8FAFC;
    }
    .btn-maps {
        display: inline-block;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        background-color: #EFF6FF;
        color: #2563EB;
        border: 1px solid #BFDBFE;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-maps:hover {
        background-color: #DBEAFE;
        color: #1D4ED8;
    }
</style>

            <!-- PAGE: Dashboard -->
            <div id="page-dashboard" class="page active">

                <!-- Welcome Card -->
                <div class="welcome-card">
                    <img src="../../assets/default-avatar.svg" alt="Profile" class="profile-img">
                    <div class="welcome-text">
                        <h2>Selamat datang, <?= htmlspecialchars($namaUser) ?></h2>
                        <p>Koordinator Bidang &ndash; Teknik Komputer</p>
                    </div>
                </div>

                <!-- Stat Cards (Progress Status) -->
                <div class="stat-grid">
                    <div class="stat-card active-card" id="card-all" onclick="filterByProgress('ALL')" style="background: linear-gradient(135deg, #1C334D 0%, #2A486C 100%);">
                        <div class="stat-number" id="num-all"><?= $activeCount ?></div>
                        <div class="stat-label">Kelompok Aktif</div>
                    </div>
                    <div class="stat-card" id="card-pengajuan" onclick="filterByProgress('Pengajuan Tempat')">
                        <div class="stat-number" id="num-pengajuan"><?= $countPengajuanTempat ?></div>
                        <div class="stat-label">Pengajuan Tempat</div>
                    </div>
                    <div class="stat-card" id="card-acc" onclick="filterByProgress('ACC Pembuatan Proposal')">
                        <div class="stat-number" id="num-acc"><?= $countAccProposal ?></div>
                        <div class="stat-label">ACC Pembuatan Proposal</div>
                    </div>
                    <div class="stat-card" id="card-pengurusan" onclick="filterByProgress('Pengurusan Surat Pengantar')">
                        <div class="stat-number" id="num-pengurusan"><?= $countPengurusanSurat ?></div>
                        <div class="stat-label">Pengurusan Surat Pengantar</div>
                    </div>
                    <div class="stat-card" id="card-pengiriman" onclick="filterByProgress('Pengiriman Proposal')">
                        <div class="stat-number" id="num-pengiriman"><?= $countPengiriman ?></div>
                        <div class="stat-label">Pengiriman Proposal</div>
                    </div>
                    <div class="stat-card" id="card-penerimaan" onclick="filterByProgress('Surat Penerimaan Magang')">
                        <div class="stat-number" id="num-penerimaan"><?= $countPenerimaan ?></div>
                        <div class="stat-label">Surat Penerimaan Magang</div>
                    </div>
                    <div class="stat-card" id="card-revisi" onclick="filterByProgress('REVISI TEMPAT')" style="background: linear-gradient(135deg, #B45309 0%, #D97706 100%); box-shadow: 0 4px 16px rgba(217, 119, 6, 0.25);">
                        <div class="stat-number" id="num-revisi"><?= $countRevisiTempat ?></div>
                        <div class="stat-label">Revisi Tempat</div>
                    </div>
                    <div class="stat-card" id="card-ditolak" onclick="filterByProgress('TOLAK')" style="background: linear-gradient(135deg, #7F1D1D 0%, #B91C1C 100%); box-shadow: 0 4px 16px rgba(185, 28, 28, 0.25);">
                        <div class="stat-number" id="num-ditolak"><?= $countDitolak ?></div>
                        <div class="stat-label">Di Tolak</div>
                    </div>
                </div>

                <!-- Tabel Daftar Kelompok -->
                <div class="card">
                    <div class="card-header-plain" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; gap: 15px; flex-wrap: wrap;">
                        <h3 style="margin: 0;">Daftar Kelompok</h3>
                        <div style="display: flex; gap: 12px; align-items: center; margin-left: auto;">
                            <select id="filter-angkatan" onchange="filterKelompok()" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 8px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none; width: 220px; height: 38px; box-sizing: border-box;">
                                <option value="ALL">Semua Angkatan</option>
                                <option value="2024">Angkatan 2024</option>
                                <option value="2025">Angkatan 2025</option>
                                <option value="2026">Angkatan 2026</option>
                                <option value="2027">Angkatan 2027</option>
                                <option value="2028">Angkatan 2028</option>
                                <option value="2029">Angkatan 2029</option>
                                <option value="2030">Angkatan 2030</option>
                            </select>
                            <div class="plot-search-wrap" style="margin: 0; max-width: 250px; flex: 0 0 250px; height: 38px; box-sizing: border-box;">
                                <span class="plot-search-icon">&#128269;</span>
                                <input type="text" id="search-kelompok" class="plot-search-input" placeholder="Cari kelompok..." oninput="filterKelompok()">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0" style="overflow-x: auto;">
                        <table class="table" id="tabel-kelompok" style="border-collapse: collapse; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Jumlah MHS</th>
                                    <th style="width: 80px;">Nomor Kelompok</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>NIM</th>
                                    <th>NO HP mahasiswa</th>
                                    <th>Lokasi Magang</th>
                                    <th>Alamat Lengkap</th>
                                    <th>Link Google maps</th>
                                    <th>Progress</th>
                                    <th>NAMA DAN KONTAK PERSON</th>
                                    <th>Status Verifikasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingGroups)): ?>
                                    <tr>
                                        <td colspan="12" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada kelompok terdaftar.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $globalStudentCounter = 1; 
                                    foreach ($pendingGroups as $groupIndex => $group): 
                                        $names = !empty($group['nama_mahasiswa']) ? explode(', ', $group['nama_mahasiswa']) : [];
                                        $nims = !empty($group['nim']) ? explode(', ', $group['nim']) : [];
                                        $phones = !empty($group['no_hp']) ? explode(', ', $group['no_hp']) : [];
                                        $rowCount = max(1, count($names));
                                        
                                        $redirectPage = 'dashboard.php';
                                        if ($group['jenis_verifikasi'] === 'Lokasi Magang') $redirectPage = 'verifikasi_lokasi.php';
                                        elseif ($group['jenis_verifikasi'] === 'Proposal') $redirectPage = 'verifikasi_proposal.php';
                                        elseif ($group['jenis_verifikasi'] === 'Berkas') $redirectPage = 'verifikasi_berkas.php';
                                        elseif ($group['jenis_verifikasi'] === 'Bukti Diterima') $redirectPage = 'verifikasi_bukti.php';
                                        
                                        $isPending = ($group['status_kelompok'] === 'Menunggu');
                                        $isBelumMengajukan = ($group['status_kelompok'] === 'Belum Mengajukan');
                                        
                                        // Google Maps Link
                                        $googleMapsLink = KoordinatorHelper::generateGoogleMapsLink($group['latitude'], $group['longitude'], $group['alamat_lengkap']);
                                        
                                        // Dropdown options
                                        $options = [
                                            'DI TOLAK',
                                            'REVISI TEMPAT',
                                            'Pengajuan Tempat',
                                            'ACC Pembuatan Proposal',
                                            'Pengurusan Surat Pengantar',
                                            'Pengiriman Proposal',
                                            'Surat Penerimaan Magang',
                                            'Pengajuan di Tolak Lokasi',
                                            'Ditolak Perusahaan'
                                        ];
                                        
                                        $cpInfo = htmlspecialchars($group['cp_nama'] ?? '-');
                                        if (!empty($group['cp_tlp']) && $group['cp_tlp'] !== '-') {
                                            $cpInfo .= ' (' . htmlspecialchars($group['cp_tlp']) . ')';
                                        }

                                    ?>
                                        <?php 
                                        for ($i = 0; $i < $rowCount; $i++): 
                                            $studentNim = $nims[$i] ?? '';
                                            $studentAngkatan = 'unknown';
                                            if (!empty($studentNim)) {
                                                $cleanNim = preg_replace('/[^0-9]/', '', $studentNim);
                                                $searchArea = substr($cleanNim, 2);
                                                if (preg_match('/2[0-9]/', $searchArea, $matches)) {
                                                    $studentAngkatan = '20' . $matches[0];
                                                } elseif (preg_match('/2[0-9]/', $cleanNim, $matches)) {
                                                    $studentAngkatan = '20' . $matches[0];
                                                }
                                            }
                                        ?>
                                        <tr class="group-row" data-progress="<?= htmlspecialchars($group['status_progress']) ?>" data-group-id="<?= $groupIndex ?>" data-angkatan="<?= $studentAngkatan ?>">
                                            <!-- 1. Jumlah MHS (Running Student Counter) -->
                                            <td style="text-align: center; font-weight: 500;"><?= $globalStudentCounter++ ?></td>
                                            
                                            <!-- 2. Nomor Kelompok -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="text-align: center; font-weight: 600; vertical-align: middle; background-color: #F8FAFC; border-right: 1px solid #E2E8F0;"><?= $groupIndex + 1 ?></td>
                                            <?php endif; ?>
                                            
                                            <!-- 3. Nama Mahasiswa -->
                                            <td><?= htmlspecialchars($names[$i] ?? '-') ?></td>
                                            
                                            <!-- 4. NIM -->
                                            <td style="text-align: center;"><?= htmlspecialchars($nims[$i] ?? '-') ?></td>
                                            
                                            <!-- 5. No. HP Mahasiswa -->
                                            <td><?= htmlspecialchars($phones[$i] ?? '-') ?></td>
                                            
                                            <!-- 6. Lokasi Magang -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; font-weight: 500;"><?= htmlspecialchars($group['lokasi_magang']) ?></td>
                                            <?php endif; ?>
                                            
                                            <!-- 7. Alamat Lengkap -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; color: #475569; font-size: 12px;"><?= htmlspecialchars($group['alamat_lengkap']) ?></td>
                                            <?php endif; ?>
                                            
                                            <!-- 8. Link Google Maps -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; text-align: center;">
                                                    <?php if ($googleMapsLink !== '-'): ?>
                                                        <a href="<?= htmlspecialchars($googleMapsLink) ?>" target="_blank" class="btn-maps"><i class="fa-solid fa-map-location-dot"></i> Maps</a>
                                                    <?php else: ?>
                                                        <span style="color: #94A3B8;">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            
                                            <!-- 9. Progress (Static Label) -->
                                            <?php if ($i === 0): ?>
                                                <?php
                                                    $statusStyles = [
                                                        'DI TOLAK'                   => ['bg' => '#FEE2E2', 'color' => '#EF4444'],
                                                        'REVISI TEMPAT'              => ['bg' => '#FEF9C3', 'color' => '#CA8A04'],
                                                        'Pengajuan Tempat'           => ['bg' => '#E0F2FE', 'color' => '#0369A1'],
                                                        'ACC Pembuatan Proposal'     => ['bg' => '#E2E8F0', 'color' => '#475569'],
                                                        'Pengurusan Surat Pengantar' => ['bg' => '#FFEDD5', 'color' => '#C2410C'],
                                                        'Pengiriman Proposal'        => ['bg' => '#FFEDD5', 'color' => '#C2410C'],
                                                        'Surat Penerimaan Magang'    => ['bg' => '#D1FAE5', 'color' => '#065F46'],
                                                        'Pengajuan di Tolak Lokasi'  => ['bg' => '#FEE2E2', 'color' => '#EF4444'],
                                                        'Ditolak Perusahaan'         => ['bg' => '#FEE2E2', 'color' => '#EF4444'],
                                                    ];
                                                    $currentProgress = $group['status_progress'] ?: 'Pengajuan Tempat';
                                                    $currentStyle = $statusStyles[$currentProgress] ?? ['bg' => '#F1F5F9', 'color' => '#475569'];
                                                ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; text-align: center; border-left: 1px solid #E2E8F0;">
                                                    <span class="progress-badge" 
                                                          style="font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; display: inline-block; background-color: <?= $currentStyle['bg'] ?>; color: <?= $currentStyle['color'] ?>;">
                                                        <?= htmlspecialchars($currentProgress) ?>
                                                    </span>
                                                </td>
                                            <?php endif; ?>
                                            
                                            <!-- 10. Nama & Kontak Person -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; padding-left: 20px; padding-right: 20px;">
                                                    <div style="display: flex; flex-direction: column; gap: 8px; font-family: 'Inter', sans-serif;">
                                                        <div style="display: flex; align-items: center; color: #1C334D; font-weight: 700; font-size: 14px; white-space: nowrap;">
                                                            <i class="fa-solid fa-user" style="color: #7B8FA1; width: 16px; margin-right: 10px; font-size: 15px;"></i>
                                                            <span><?= htmlspecialchars($group['cp_nama'] ?: '-') ?></span>
                                                        </div>
                                                        <?php if (!empty($group['cp_tlp']) && $group['cp_tlp'] !== '-'): ?>
                                                            <div style="display: flex; align-items: center; color: #64748B; font-size: 13px; font-weight: 500; white-space: nowrap;">
                                                                <i class="fa-solid fa-phone" style="color: #7B8FA1; width: 16px; margin-right: 10px; font-size: 14px;"></i>
                                                                <span><?= htmlspecialchars($group['cp_tlp']) ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                            
                                            <!-- 11. Status Verifikasi -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; text-align: center;">
                                                     <?php if ($isPending): ?>
                                                         <a href="<?= $redirectPage ?>" class="badge badge-warning" style="white-space: normal; line-height: 1.4; text-decoration: none; display: inline-block; cursor: pointer; transition: transform 0.2s;">
                                                             <?= htmlspecialchars($group['jenis_verifikasi']) ?>
                                                         </a>
                                                     <?php elseif ($isBelumMengajukan): ?>
                                                         <span class="badge" style="background-color: #E2E8F0; color: #475569; border: 1px solid #CBD5E1;">Belum Mengajukan</span>
                                                     <?php elseif ($group['status_kelompok'] === 'Selesai'): ?>
                                                         <span class="badge badge-success-status" style="background-color: #D1FAE5; color: #065F46; border: 1px solid #10B981;">Selesai</span>
                                                     <?php else: ?>
                                                         <span style="color: #94A3B8;">-</span>
                                                     <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            
                                            <!-- 12. Aksi -->
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; text-align: center;">
                                                    <button class="btn-verifikasi" style="background-color: #00CFE8; border-color: #00CFE8; cursor: pointer; padding: 6px 12px; font-size: 13px;" onclick="window.location.href='data_lengkap.php'">Detail</button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endfor; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div id="pagination-controls" style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px 24px; border-top: 1px solid #E2E8F0; background: #F8FAFC;"></div>
                </div>

                <!-- Toast and AJAX script for inline progress update -->
                <script>
                    const STATUS_STYLES = {
                        'DI TOLAK': { bg: '#FEE2E2', color: '#EF4444' },
                        'REVISI TEMPAT': { bg: '#FEF9C3', color: '#CA8A04' },
                        'Pengajuan Tempat': { bg: '#E0F2FE', color: '#0369A1' },
                        'ACC Pembuatan Proposal': { bg: '#E2E8F0', color: '#475569' },
                        'Pengurusan Surat Pengantar': { bg: '#FFEDD5', color: '#C2410C' },
                        'Pengiriman Proposal': { bg: '#FFEDD5', color: '#C2410C' },
                        'Surat Penerimaan Magang': { bg: '#D1FAE5', color: '#065F46' },
                        'Pengajuan di Tolak Lokasi': { bg: '#FEE2E2', color: '#EF4444' },
                        'Ditolak Perusahaan': { bg: '#FEE2E2', color: '#EF4444' }
                    };

                    let activeProgressFilter = 'ALL';
                    let currentPage = 1;
                    const GROUPS_PER_PAGE = 5;

                    function applyFiltersAndPagination() {
                        const rows = document.querySelectorAll('#tabel-kelompok tbody tr');
                        const searchInput = document.getElementById('search-kelompok')?.value.toLowerCase().trim() || '';
                        
                        // Initialize counters
                        let counts = {
                            ALL: 0,
                            'Pengajuan Tempat': 0,
                            'ACC Pembuatan Proposal': 0,
                            'Pengurusan Surat Pengantar': 0,
                            'Pengiriman Proposal': 0,
                            'Surat Penerimaan Magang': 0,
                            'REVISI TEMPAT': 0,
                            TOLAK: 0
                        };

                        // Group rows by their data-group-id
                        const groups = {};
                        rows.forEach(row => {
                            if (row.classList.contains('group-row')) {
                                const groupId = row.dataset.groupId;
                                if (!groups[groupId]) {
                                    groups[groupId] = [];
                                }
                                groups[groupId].push(row);
                            }
                        });
                        
                        // Determine matching group IDs based on active filter and search text
                        const matchingGroupIds = [];
                        Object.keys(groups).forEach(groupId => {
                            const firstRow = groups[groupId][0];
                            if (firstRow) {
                                const prog = firstRow.dataset.progress;
                                
                                // Search filter
                                let matchesSearch = true;
                                if (searchInput !== '') {
                                    let groupText = '';
                                    groups[groupId].forEach(row => {
                                        groupText += ' ' + row.textContent.toLowerCase();
                                    });
                                    if (!groupText.includes(searchInput)) {
                                        matchesSearch = false;
                                    }
                                }
                                
                                // Angkatan filter
                                const angkatanFilter = document.getElementById('filter-angkatan')?.value || 'ALL';
                                let matchesAngkatan = true;
                                if (angkatanFilter !== 'ALL') {
                                    matchesAngkatan = groups[groupId].some(row => row.dataset.angkatan === angkatanFilter);
                                }
                                
                                if (matchesSearch && matchesAngkatan) {
                                    // Increment ALL count
                                    counts.ALL++;
                                    
                                    // Increment status specific count
                                    if (prog === 'DI TOLAK' || prog === 'Pengajuan di Tolak Lokasi' || prog === 'Ditolak Perusahaan') {
                                        counts.TOLAK++;
                                    } else if (counts[prog] !== undefined) {
                                        counts[prog]++;
                                    }
                                    
                                    // Now check if it matches the active progress filter for table visibility
                                    let matchesProgress = false;
                                    if (activeProgressFilter === 'ALL') {
                                        matchesProgress = true;
                                    } else if (activeProgressFilter === 'TOLAK') {
                                        matchesProgress = (prog === 'DI TOLAK' || prog === 'Pengajuan di Tolak Lokasi' || prog === 'Ditolak Perusahaan');
                                    } else {
                                        matchesProgress = (prog === activeProgressFilter);
                                    }
                                    
                                    if (matchesProgress) {
                                        matchingGroupIds.push(groupId);
                                    }
                                }
                            }
                        });
                        
                        // Update Stat Cards DOM
                        if (document.getElementById('num-all')) document.getElementById('num-all').textContent = counts.ALL;
                        if (document.getElementById('num-pengajuan')) document.getElementById('num-pengajuan').textContent = counts['Pengajuan Tempat'];
                        if (document.getElementById('num-acc')) document.getElementById('num-acc').textContent = counts['ACC Pembuatan Proposal'];
                        if (document.getElementById('num-pengurusan')) document.getElementById('num-pengurusan').textContent = counts['Pengurusan Surat Pengantar'];
                        if (document.getElementById('num-pengiriman')) document.getElementById('num-pengiriman').textContent = counts['Pengiriman Proposal'];
                        if (document.getElementById('num-penerimaan')) document.getElementById('num-penerimaan').textContent = counts['Surat Penerimaan Magang'];
                        if (document.getElementById('num-revisi')) document.getElementById('num-revisi').textContent = counts['REVISI TEMPAT'];
                        if (document.getElementById('num-ditolak')) document.getElementById('num-ditolak').textContent = counts.TOLAK;
                        
                        // Sort numerically to keep natural order
                        matchingGroupIds.sort((a, b) => parseInt(a, 10) - parseInt(b, 10));
                        
                        const totalMatchingGroups = matchingGroupIds.length;
                        const totalPages = Math.ceil(totalMatchingGroups / GROUPS_PER_PAGE) || 1;
                        
                        if (currentPage > totalPages) {
                            currentPage = totalPages;
                        }
                        if (currentPage < 1) {
                            currentPage = 1;
                        }
                        
                        const startIndex = (currentPage - 1) * GROUPS_PER_PAGE;
                        const endIndex = startIndex + GROUPS_PER_PAGE;
                        const visibleGroupIds = matchingGroupIds.slice(startIndex, endIndex);
                        
                        // Toggle display style for all rows
                        rows.forEach(row => {
                            if (row.classList.contains('group-row')) {
                                const groupId = row.dataset.groupId;
                                if (visibleGroupIds.includes(groupId)) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            }
                        });
                        
                        // Render no results message if needed
                        const noResultRow = document.getElementById('no-results-row-dashboard');
                        if (noResultRow) {
                            noResultRow.style.display = 'none';
                        }
                        
                        renderPaginationControls(totalPages);
                    }

                    function filterKelompok() {
                        currentPage = 1;
                        applyFiltersAndPagination();
                    }

                    function renderPaginationControls(totalPages) {
                        const container = document.getElementById('pagination-controls');
                        if (!container) return;
                        
                        container.innerHTML = '';
                        
                        if (totalPages <= 1) {
                            container.style.display = 'none';
                            return;
                        }
                        
                        container.style.display = 'flex';
                        
                        // Prev Button
                        const prevBtn = document.createElement('button');
                        prevBtn.innerHTML = '&laquo; Sebelumnya';
                        prevBtn.style.cssText = 'padding: 8px 14px; border: 1px solid #E2E8F0; border-radius: 6px; background: white; color: #475569; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; outline: none;';
                        if (currentPage === 1) {
                            prevBtn.disabled = true;
                            prevBtn.style.opacity = '0.5';
                            prevBtn.style.cursor = 'not-allowed';
                        } else {
                            prevBtn.addEventListener('click', () => {
                                currentPage--;
                                applyFiltersAndPagination();
                            });
                            prevBtn.addEventListener('mouseover', () => prevBtn.style.background = '#F8FAFC');
                            prevBtn.addEventListener('mouseout', () => prevBtn.style.background = 'white');
                        }
                        container.appendChild(prevBtn);
                        
                        // Page Buttons
                        for (let i = 1; i <= totalPages; i++) {
                            const pageBtn = document.createElement('button');
                            pageBtn.textContent = 'Slide ' + i;
                            pageBtn.style.cssText = 'padding: 8px 14px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; outline: none;';
                            
                            if (i === currentPage) {
                                pageBtn.style.background = '#1C334D';
                                pageBtn.style.color = 'white';
                                pageBtn.style.borderColor = '#1C334D';
                            } else {
                                pageBtn.style.background = 'white';
                                pageBtn.style.color = '#475569';
                                pageBtn.addEventListener('click', () => {
                                    currentPage = i;
                                    applyFiltersAndPagination();
                                });
                                pageBtn.addEventListener('mouseover', () => pageBtn.style.background = '#F8FAFC');
                                pageBtn.addEventListener('mouseout', () => pageBtn.style.background = 'white');
                            }
                            container.appendChild(pageBtn);
                        }
                        
                        // Next Button
                        const nextBtn = document.createElement('button');
                        nextBtn.innerHTML = 'Selanjutnya &raquo;';
                        nextBtn.style.cssText = 'padding: 8px 14px; border: 1px solid #E2E8F0; border-radius: 6px; background: white; color: #475569; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; outline: none;';
                        if (currentPage === totalPages) {
                            nextBtn.disabled = true;
                            nextBtn.style.opacity = '0.5';
                            nextBtn.style.cursor = 'not-allowed';
                        } else {
                            nextBtn.addEventListener('click', () => {
                                currentPage++;
                                applyFiltersAndPagination();
                            });
                            nextBtn.addEventListener('mouseover', () => nextBtn.style.background = '#F8FAFC');
                            nextBtn.addEventListener('mouseout', () => nextBtn.style.background = 'white');
                        }
                        container.appendChild(nextBtn);
                    }

                    document.addEventListener('DOMContentLoaded', () => {
                        document.querySelectorAll('.progress-select').forEach(select => {
                            applySelectStyles(select);
                        });
                        applyFiltersAndPagination();
                    });

                    function applySelectStyles(select) {
                        const val = select.value;
                        const style = STATUS_STYLES[val] || { bg: '#F1F5F9', color: '#475569' };
                        select.style.backgroundColor = style.bg;
                        select.style.color = style.color;
                    }

                    function updateProgress(select, kelompokId) {
                        applySelectStyles(select);
                        const progress = select.value;

                        // Disable temporarily
                        select.disabled = true;

                        const body = new URLSearchParams({ kelompok_id: kelompokId, progress: progress });

                        fetch('../../backend/actions/koordinator_update_progress.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: body.toString()
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message || 'Progress berhasil diperbarui.', 'success');
                                
                                // Update stat counts dynamically on screen
                                const oldVal = select.dataset.originalVal;
                                const newVal = progress;
                                if (oldVal !== newVal) {
                                    updateStatCount(oldVal, -1);
                                    updateStatCount(newVal, 1);
                                    select.dataset.originalVal = newVal;
                                    
                                    // Update table row data attribute
                                    const row = select.closest('tr');
                                    if (row) {
                                        row.dataset.progress = newVal;
                                    }
                                }
                            } else {
                                alert(data.message || 'Gagal memperbarui progress.');
                                select.value = select.dataset.originalVal;
                                applySelectStyles(select);
                            }
                        })
                        .catch(err => {
                            console.error('AJAX error:', err);
                            alert('Terjadi kesalahan koneksi saat memperbarui progress.');
                            select.value = select.dataset.originalVal;
                            applySelectStyles(select);
                        })
                        .finally(() => {
                            select.disabled = false;
                        });
                    }

                    function updateStatCount(statusName, change) {
                        let cardId = '';
                        if (statusName === 'Pengajuan Tempat') cardId = 'num-pengajuan';
                        else if (statusName === 'ACC Pembuatan Proposal') cardId = 'num-acc';
                        else if (statusName === 'Pengurusan Surat Pengantar') cardId = 'num-pengurusan';
                        else if (statusName === 'Pengiriman Proposal') cardId = 'num-pengiriman';
                        else if (statusName === 'Surat Penerimaan Magang') cardId = 'num-penerimaan';
                        else if (statusName === 'REVISI TEMPAT') cardId = 'num-revisi';
                        else if (statusName === 'DI TOLAK' || statusName === 'Pengajuan di Tolak Lokasi' || statusName === 'Ditolak Perusahaan') cardId = 'num-ditolak';
                        
                        if (cardId) {
                            const el = document.getElementById(cardId);
                            if (el) {
                                let val = parseInt(el.textContent, 10) || 0;
                                el.textContent = Math.max(0, val + change);
                            }
                        }
                    }

                    function filterByProgress(progressName) {
                        activeProgressFilter = progressName;
                        currentPage = 1;
                        
                        // Highlight active card
                        document.querySelectorAll('.stat-card').forEach(card => {
                            card.classList.remove('active-card');
                        });
                        
                        // Use event.currentTarget to reliably get the clicked card element
                        const clickedCard = window.event ? window.event.currentTarget : null;
                        if (clickedCard) {
                            clickedCard.classList.add('active-card');
                        } else {
                            // Find card by ID if triggered programmatically
                            let cardId = 'card-all';
                            if (progressName === 'Pengajuan Tempat') cardId = 'card-pengajuan';
                            else if (progressName === 'ACC Pembuatan Proposal') cardId = 'card-acc';
                            else if (progressName === 'Pengurusan Surat Pengantar') cardId = 'card-pengurusan';
                            else if (progressName === 'Pengiriman Proposal') cardId = 'card-pengiriman';
                            else if (progressName === 'Surat Penerimaan Magang') cardId = 'card-penerimaan';
                            else if (progressName === 'REVISI TEMPAT') cardId = 'card-revisi';
                            else if (progressName === 'TOLAK') cardId = 'card-ditolak';
                            
                            const card = document.getElementById(cardId);
                            if (card) card.classList.add('active-card');
                        }

                        applyFiltersAndPagination();
                    }

                    function showToast(msg, type) {
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px;';
                            document.body.appendChild(container);
                        }

                        const toast = document.createElement('div');
                        toast.textContent = msg;
                        toast.style.cssText = [
                            'padding:10px 18px',
                            'border-radius:6px',
                            'font-size:13px',
                            'font-family:Inter,sans-serif',
                            'font-weight:500',
                            'box-shadow:0 4px 12px rgba(0,0,0,.15)',
                            'opacity:0',
                            'transform:translateY(8px)',
                            'transition:opacity .25s,transform .25s',
                            type === 'success'
                                ? 'background:#D1FAE5;color:#065F46;border:1px solid #10B981'
                                : 'background:#FEE2E2;color:#991B1B;border:1px solid #EF4444'
                        ].join(';');

                        container.appendChild(toast);

                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                toast.style.opacity = '1';
                                toast.style.transform = 'translateY(0)';
                            });
                        });

                        setTimeout(() => {
                            toast.style.opacity = '0';
                            toast.style.transform = 'translateY(8px)';
                            setTimeout(() => { toast.remove(); }, 300);
                        }, 3000);
                    }
                </script>

            </div><!-- end page-dashboard -->

<?php include 'footer.php'; ?>