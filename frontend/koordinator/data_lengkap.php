<?php include 'header.php'; ?>
<?php
$completeData = KoordinatorHelper::getCompleteGroupsData('pendaftaran');
$totalItems = count($completeData);
?>

<style>
    #tabel-data-lengkap {
        border-collapse: collapse;
        width: 100%;
        background-color: white;
    }
    #tabel-data-lengkap thead th {
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
    #tabel-data-lengkap tbody td {
        font-size: 13px;
        border: 1px solid #E2E8F0;
        padding: 10px 12px;
        vertical-align: middle;
        color: #334155;
    }
    #tabel-data-lengkap tbody tr:hover td {
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

<!-- PAGE: Data Lengkap -->
<div id="page-data-lengkap" class="page active">
    <div class="page-title-bar">
        <h1>Data Lengkap Magang</h1>
        <span class="page-subtitle">Informasi lengkap mahasiswa, lokasi magang, dan progress proposal</span>
    </div>

    <!-- Filter Bar -->
    <div class="plotting-toolbar">
        <div class="plot-search-wrap">
            <span class="plot-search-icon">&#128269;</span>
            <input type="text" id="search-data-lengkap" class="plot-search-input"
                placeholder="Cari nama kelompok, mahasiswa, atau lokasi..." oninput="filterDataLengkap()">
        </div>
        
        <div style="display: flex; gap: 12px; align-items: center; margin-left: auto;">
            <select id="filter-angkatan" onchange="filterDataLengkap()" style="padding: 8px 12px; border: 1.5px solid #E2E8F0; border-radius: 8px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none; width: 220px; height: 38px; box-sizing: border-box;">
                <option value="ALL">Semua Angkatan</option>
                <option value="2024">Angkatan 2024</option>
                <option value="2025">Angkatan 2025</option>
                <option value="2026">Angkatan 2026</option>
                <option value="2027">Angkatan 2027</option>
                <option value="2028">Angkatan 2028</option>
                <option value="2029">Angkatan 2029</option>
                <option value="2030">Angkatan 2030</option>
            </select>
            
            <button class="btn" onclick="exportDataLengkap()" 
                style="padding: 10px 20px; background-color: #10B981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; height: 38px; box-sizing: border-box;">
                📥 Export Excel
            </button>
        </div>
    </div>

    <!-- Info Stats -->
    <div id="info-stats" style="margin-bottom: 15px; font-size: 13px; color: #64748B;">
        Menampilkan kelompok <span id="info-start" style="font-weight: 600; color: #1E293B;">1</span>-<span id="info-end" style="font-weight: 600; color: #1E293B;">5</span> dari <span id="info-total" style="font-weight: 600; color: #1E293B;"><?= $totalItems ?></span> kelompok
    </div>

    <!-- Tabel Data Lengkap -->
    <div style="overflow-x: auto; background: white; border-radius: 8px;">
        <table id="tabel-data-lengkap" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;">Jumlah MHS</th>
                    <th style="width: 80px;">Nomor Kelompok</th>
                    <th style="width: 100px;">Nama Kelompok</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>NO HP mahasiswa</th>
                    <th>Lokasi Magang</th>
                    <th>Alamat Lengkap</th>
                    <th>Link Google maps</th>
                    <th>Progress</th>
                    <th>NAMA DAN KONTAK PERSON</th>
                </tr>
            </thead>
            <tbody id="tbody-data-lengkap">
                <?php if (empty($completeData)): ?>
                    <tr>
                        <td colspan="11" style="padding: 40px; color:#94A3B8; font-style: italic; text-align: center;">Belum ada data kelompok yang terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $globalStudentCounter = 1;
                    foreach ($completeData as $groupIndex => $row): 
                        // Split data mahasiswa
                        $names = !empty($row['nama_mahasiswa']) ? explode(', ', $row['nama_mahasiswa']) : [];
                        $nims = !empty($row['nim']) ? explode(', ', $row['nim']) : [];
                        $phones = !empty($row['no_hp']) ? explode(', ', $row['no_hp']) : [];
                        $rowCount = max(1, count($names));

                        $googleMapsLink = KoordinatorHelper::generateGoogleMapsLink($row['latitude'], $row['longitude'], $row['alamat_lengkap']);
                        
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
                        $currentProgress = $row['status_progress'] ?: 'Pengajuan Tempat';
                        $currentStyle = $statusStyles[$currentProgress] ?? ['bg' => '#F1F5F9', 'color' => '#475569'];
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
                        <tr class="group-row" 
                            data-group-id="<?= $groupIndex ?>" 
                            data-progress="<?= htmlspecialchars($row['status_progress']) ?>"
                            data-angkatan="<?= $studentAngkatan ?>"
                            data-search="<?= htmlspecialchars(strtolower($row['kelompok_nama'] . ' ' . ($row['nama_mahasiswa'] ?? '') . ' ' . ($row['lokasi_magang'] ?? '') . ' ' . ($row['nim'] ?? ''))) ?>">
                            
                            <!-- 1. Jumlah MHS (Running student counter) -->
                            <td style="text-align: center; font-weight: 500;"><?= $globalStudentCounter++ ?></td>
                            
                            <!-- 2. Nomor Kelompok -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="text-align: center; font-weight: 600; vertical-align: middle; background-color: #F8FAFC; border-right: 1px solid #E2E8F0;"><?= $groupIndex + 1 ?></td>
                            <?php endif; ?>
                            
                            <!-- 3. Nama Kelompok -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="text-align: center; font-weight: 600; vertical-align: middle; background-color: #F8FAFC; border-right: 1px solid #E2E8F0;"><?= htmlspecialchars($row['kelompok_nama']) ?></td>
                            <?php endif; ?>
                            
                            <!-- 3. Nama Mahasiswa -->
                            <td style="text-align: left;"><?= htmlspecialchars($names[$i] ?? '-') ?></td>
                            
                            <!-- 4. NIM -->
                            <td style="text-align: center;"><?= htmlspecialchars($nims[$i] ?? '-') ?></td>
                            
                            <!-- 5. NO HP mahasiswa -->
                            <td style="text-align: left;"><?= htmlspecialchars($phones[$i] ?? '-') ?></td>
                            
                            <!-- 6. Lokasi Magang -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; font-weight: 500; text-align: left;"><?= htmlspecialchars($row['lokasi_magang']) ?></td>
                            <?php endif; ?>
                            
                            <!-- 7. Alamat Lengkap -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; color: #475569; font-size: 12px; text-align: left;"><?= htmlspecialchars($row['alamat_lengkap']) ?></td>
                            <?php endif; ?>
                            
                            <!-- 8. Link Google maps -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; text-align: center;">
                                    <?php if ($googleMapsLink !== '-'): ?>
                                        <a href="<?= htmlspecialchars($googleMapsLink) ?>" target="_blank" class="btn-maps"><i class="fa-solid fa-map-location-dot"></i> Maps</a>
                                    <?php else: ?>
                                        <span style="color: #94A3B8;">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            
                            <!-- 9. Progress -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; text-align: center; border-left: 1px solid #E2E8F0;">
                                    <span class="progress-badge" 
                                          style="font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; display: inline-block; background-color: <?= $currentStyle['bg'] ?>; color: <?= $currentStyle['color'] ?>;">
                                        <?= htmlspecialchars($currentProgress) ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            
                            <!-- 10. NAMA DAN KONTAK PERSON -->
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; padding-left: 20px; padding-right: 20px; text-align: left;">
                                    <div style="display: flex; flex-direction: column; gap: 8px; font-family: 'Inter', sans-serif;">
                                        <div style="display: flex; align-items: center; color: #1C334D; font-weight: 700; font-size: 14px; white-space: nowrap;">
                                            <i class="fa-solid fa-user" style="color: #7B8FA1; width: 16px; margin-right: 10px; font-size: 15px;"></i>
                                            <span><?= htmlspecialchars($row['cp_nama'] ?: '-') ?></span>
                                        </div>
                                        <?php if (!empty($row['cp_tlp']) && $row['cp_tlp'] !== '-'): ?>
                                            <div style="display: flex; align-items: center; color: #64748B; font-size: 13px; font-weight: 500; white-space: nowrap;">
                                                <i class="fa-solid fa-phone" style="color: #7B8FA1; width: 16px; margin-right: 10px; font-size: 14px;"></i>
                                                <span><?= htmlspecialchars($row['cp_tlp']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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

<script>
let currentPage = 1;
const GROUPS_PER_PAGE = 5;

function applyFiltersAndPagination() {
    const searchInput = document.getElementById('search-data-lengkap').value.toLowerCase().trim();
    const angkatanFilter = document.getElementById('filter-angkatan').value;
    const rows = document.querySelectorAll('#tbody-data-lengkap tr.group-row');
    
    // Group rows by their data-group-id
    const groups = {};
    rows.forEach(row => {
        const groupId = row.dataset.groupId;
        if (!groups[groupId]) {
            groups[groupId] = [];
        }
        groups[groupId].push(row);
    });
    
    // Determine matching group IDs based on search and angkatan
    const matchingGroupIds = [];
    Object.keys(groups).forEach(groupId => {
        const firstRow = groups[groupId][0];
        if (firstRow) {
            let groupMatches = false;
            groups[groupId].forEach(row => {
                const searchText = row.dataset.search || '';
                if (searchText.includes(searchInput)) {
                    groupMatches = true;
                }
            });
            
            // Check angkatan filter
            let matchesAngkatan = true;
            if (angkatanFilter !== 'ALL') {
                matchesAngkatan = groups[groupId].some(row => row.dataset.angkatan === angkatanFilter);
            }
            
            if (groupMatches && matchesAngkatan) {
                matchingGroupIds.push(groupId);
            }
        }
    });
    
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
        const groupId = row.dataset.groupId;
        if (visibleGroupIds.includes(groupId)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update Info Stats
    const infoStartEl = document.getElementById('info-start');
    const infoEndEl = document.getElementById('info-end');
    const infoTotalEl = document.getElementById('info-total');
    if (infoStartEl && infoEndEl && infoTotalEl) {
        if (totalMatchingGroups === 0) {
            infoStartEl.textContent = '0';
            infoEndEl.textContent = '0';
        } else {
            infoStartEl.textContent = startIndex + 1;
            infoEndEl.textContent = Math.min(endIndex, totalMatchingGroups);
        }
        infoTotalEl.textContent = totalMatchingGroups;
    }
    
    // Render no results message if needed
    const noResultRow = document.getElementById('no-results-row');
    if (noResultRow) {
        noResultRow.style.display = 'none';
    }
    
    renderPaginationControls(totalPages);
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

function filterDataLengkap() {
    currentPage = 1;
    applyFiltersAndPagination();
}

function exportDataLengkap() {
    const angkatan = document.getElementById('filter-angkatan').value;
    window.location.href = 'export_data.php?action=export_excel&angkatan=' + angkatan;
}

document.addEventListener('DOMContentLoaded', () => {
    applyFiltersAndPagination();
});
</script>

<?php include 'footer.php'; ?>
