<?php include 'header.php'; ?>
<?php 
    $sortBy = $_GET['sort'] ?? 'tanggal_terbaru';
    $buktiList = KoordinatorHelper::getGroupsForBuktiVerification($sortBy); 
?>

            <!-- PAGE: Verifikasi Bukti Diterima -->
            <div id="page-verifikasi-bukti" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Bukti Diterima</h1>
                    <span class="page-subtitle">Periksa dan setujui bukti penerimaan magang yang diajukan kelompok</span>
                </div>
                
                <!-- Sort & Filter Controls -->
                <div style="margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <label for="sort-select" style="font-size: 13px; font-weight: 600; color: #334155;">Urutkan:</label>
                        <select id="sort-select" onchange="changeSortPage(this.value)" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 6px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none; height: 38px;">
                            <option value="tanggal_terbaru" <?= $sortBy === 'tanggal_terbaru' ? 'selected' : '' ?>>📅 Tanggal Terbaru</option>
                            <option value="tanggal_terlama" <?= $sortBy === 'tanggal_terlama' ? 'selected' : '' ?>>📅 Tanggal Terlama</option>
                            <option value="nama_a" <?= $sortBy === 'nama_a' ? 'selected' : '' ?>>📖 Nama Kelompok (A-Z)</option>
                            <option value="nama_z" <?= $sortBy === 'nama_z' ? 'selected' : '' ?>>📖 Nama Kelompok (Z-A)</option>
                            <option value="ketua_a" <?= $sortBy === 'ketua_a' ? 'selected' : '' ?>>👤 Nama Ketua (A-Z)</option>
                            <option value="ketua_z" <?= $sortBy === 'ketua_z' ? 'selected' : '' ?>>👤 Nama Ketua (Z-A)</option>
                            <option value="status_menunggu" <?= $sortBy === 'status_menunggu' ? 'selected' : '' ?>>⏳ Status Menunggu Duluan</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 12px; align-items: center; margin-left: auto;">
                        <select id="filter-angkatan" onchange="applyFilters(true)" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 8px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none; width: 220px; height: 38px; box-sizing: border-box;">
                            <option value="ALL">Semua Angkatan</option>
                            <option value="2024">Angkatan 2024</option>
                            <option value="2025">Angkatan 2025</option>
                            <option value="2026">Angkatan 2026</option>
                            <option value="2027">Angkatan 2027</option>
                            <option value="2028">Angkatan 2028</option>
                            <option value="2029">Angkatan 2029</option>
                            <option value="2030">Angkatan 2030</option>
                        </select>
                        
                        <div class="plot-search-wrap" style="margin: 0; max-width: 250px; flex: 0 0 250px; height: 38px; box-sizing: border-box; display: flex; align-items: center; border: 1.5px solid #DDEAF5; border-radius: 8px; padding: 0 10px; background: white;">
                            <span class="plot-search-icon" style="color: #94A3B8; margin-right: 8px;">&#128269;</span>
                            <input type="text" id="search-bukti" placeholder="Cari bukti..." oninput="applyFilters(true)" style="border: none; outline: none; font-size: 13px; font-family: 'Inter', sans-serif; width: 100%;">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body p-0">
                        <table id="tabel-bukti" class="table">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua</th>
                                    <th>Tempat Diterima</th>
                                    <th>File Bukti</th>
                                    <th>Tanggal Diajukan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($buktiList)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding: 50px 20px; color:#9CA3AF; font-size: 14px;">
                                            <svg style="display: block; width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Belum ada pengajuan bukti diterima
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($buktiList as $bukti): ?>
                                        <?php 
                                            $statusClass = KoordinatorHelper::statusBadgeClass($bukti['status_verifikasi']); 
                                            $nims = !empty($bukti['nim']) ? explode(', ', $bukti['nim']) : [];
                                            $cohorts = [];
                                            foreach ($nims as $nim) {
                                                $cleanNim = preg_replace('/[^0-9]/', '', $nim);
                                                $searchArea = substr($cleanNim, 2);
                                                if (preg_match('/2[0-9]/', $searchArea, $matches)) {
                                                    $cohorts[] = '20' . $matches[0];
                                                } elseif (preg_match('/2[0-9]/', $cleanNim, $matches)) {
                                                    $cohorts[] = '20' . $matches[0];
                                                }
                                            }
                                            $cohortsAttr = implode(',', array_unique($cohorts));
                                        ?>
                                        <tr class="verif-row" data-angkatan="<?= $cohortsAttr ?>">
                                            <td><?= htmlspecialchars($bukti['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($bukti['ketua_nama']) ?></td>
                                            <td><?= htmlspecialchars($bukti['tempat_diterima']) ?></td>
                                            <td><a href="../../backend/helpers/serve_file.php?path=<?= urlencode($bukti['file_path']) ?>" class="link-file" target="_blank"><?= htmlspecialchars(basename($bukti['file_path'])) ?></a></td>
                                            <td><?= htmlspecialchars(KoordinatorHelper::formatDateIndo($bukti['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($bukti['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="bukti">
                                                    <input type="hidden" name="id" value="<?= $bukti['bukti_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $bukti['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="bukti">
                                                    <input type="hidden" name="id" value="<?= $bukti['bukti_id'] ?>">
                                                    <input type="hidden" name="action" value="ditolak">
                                                    <button type="submit" class="btn-tolak" <?= $bukti['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div id="pagination-controls" style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px 24px; border-top: 1px solid #E2E8F0; background: #F8FAFC;"></div>
                </div>
            </div><!-- end page-verifikasi-bukti -->

            <script>
            let currentPage = 1;
            const ITEMS_PER_PAGE = 5;

            function applyFilters(resetPage = false) {
                if (resetPage === true) {
                    currentPage = 1;
                }
                const searchInput = document.getElementById('search-bukti')?.value.toLowerCase().trim() || '';
                const angkatanFilter = document.getElementById('filter-angkatan')?.value || 'ALL';
                const rows = document.querySelectorAll('#tabel-bukti tbody tr.verif-row');
                
                const matchingRows = [];
                rows.forEach(row => {
                    let matchesSearch = true;
                    if (searchInput !== '') {
                        const text = row.textContent.toLowerCase();
                        if (!text.includes(searchInput)) {
                            matchesSearch = false;
                        }
                    }
                    
                    let matchesAngkatan = true;
                    if (angkatanFilter !== 'ALL') {
                        const rowCohorts = (row.dataset.angkatan || '').split(',');
                        if (!rowCohorts.includes(angkatanFilter)) {
                            matchesAngkatan = false;
                        }
                    }
                    
                    if (matchesSearch && matchesAngkatan) {
                        matchingRows.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const totalMatching = matchingRows.length;
                const totalPages = Math.ceil(totalMatching / ITEMS_PER_PAGE) || 1;
                
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;
                
                const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
                const endIndex = startIndex + ITEMS_PER_PAGE;
                
                matchingRows.forEach((row, index) => {
                    if (index >= startIndex && index < endIndex) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const noResultRow = document.getElementById('no-results-row-verif');
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
                        applyFilters();
                    });
                    prevBtn.addEventListener('mouseover', () => prevBtn.style.background = '#F8FAFC');
                    prevBtn.addEventListener('mouseout', () => prevBtn.style.background = 'white');
                }
                container.appendChild(prevBtn);
                
                const pageInfo = document.createElement('span');
                pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
                pageInfo.style.cssText = 'color: #475569; font-size: 13px; font-weight: 600; margin: 0 12px;';

                const pageNumbers = document.createElement('div');
                pageNumbers.style.display = 'flex';
                pageNumbers.style.gap = '6px';

                const pages = [];
                if (totalPages <= 7) {
                    for (let page = 1; page <= totalPages; page++) pages.push(page);
                } else {
                    pages.push(1);
                    if (currentPage > 4) pages.push('...');
                    const start = Math.max(2, currentPage - 1);
                    const end = Math.min(totalPages - 1, currentPage + 1);
                    for (let page = start; page <= end; page++) pages.push(page);
                    if (currentPage < totalPages - 3) pages.push('...');
                    pages.push(totalPages);
                }

                pages.forEach(item => {
                    if (item === '...') {
                        const dot = document.createElement('span');
                        dot.textContent = '...';
                        dot.style.cssText = 'padding: 8px 10px; color: #64748B; font-size: 13px; display: inline-flex; align-items: center;';
                        pageNumbers.appendChild(dot);
                        return;
                    }
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = item;
                    pageBtn.style.cssText = 'padding: 8px 12px; border: 1px solid #E2E8F0; border-radius: 6px; background: white; color: #475569; cursor: pointer; font-size: 13px; font-weight: 600;';
                    if (item === currentPage) {
                        pageBtn.style.background = '#2563EB';
                        pageBtn.style.color = 'white';
                        pageBtn.style.borderColor = '#2563EB';
                        pageBtn.disabled = true;
                        pageBtn.style.cursor = 'default';
                    } else {
                        pageBtn.addEventListener('click', () => {
                            currentPage = item;
                            applyFilters();
                        });
                    }
                    pageNumbers.appendChild(pageBtn);
                });

                container.appendChild(pageNumbers);
                container.appendChild(pageInfo);
                
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
                        applyFilters();
                    });
                    nextBtn.addEventListener('mouseover', () => nextBtn.style.background = '#F8FAFC');
                    nextBtn.addEventListener('mouseout', () => nextBtn.style.background = 'white');
                }
                container.appendChild(nextBtn);
            }

            document.addEventListener('DOMContentLoaded', () => {
                applyFilters();
            });
            </script>

<?php include 'footer.php'; ?>
