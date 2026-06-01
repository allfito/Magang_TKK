<?php include 'header.php'; ?>
<?php 
    $sortBy = $_GET['sort'] ?? 'tanggal_terbaru';
    $proposals = KoordinatorHelper::getGroupsForProposalVerification($sortBy); 
?>

            <!-- PAGE: Verifikasi Proposal -->
            <div id="page-verifikasi-proposal" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Proposal</h1>
                    <span class="page-subtitle">Periksa proposal magang yang diajukan oleh setiap kelompok</span>
                </div>
                
                <!-- Sort & Filter Controls -->
                <div style="margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <label for="filter-status" style="font-size: 13px; font-weight: 600; color: #334155;">Status:</label>
                        <select id="filter-status" onchange="applyFilters(true)" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 6px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none; height: 38px; width: 180px;">
                            <option value="ALL">Semua Status</option>
                            <option value="sudah">Sudah</option>
                            <option value="belum">Belum</option>
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
                            <input type="text" id="search-proposal" placeholder="Cari proposal..." oninput="applyFilters(true)" style="border: none; outline: none; font-size: 13px; font-family: 'Inter', sans-serif; width: 100%;">
                        </div>
                    </div>
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
                                <tr id="no-results-row-verif" style="display: none;">
                                    <td colspan="6" style="text-align:center; padding: 50px 20px; color:#9CA3AF; font-size: 14px; line-height: 1.6;">
                                        <svg style="display: block; width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Belum ada proposal yang diajukan<br><span style="font-size: 13px;">atau tidak ada data yang cocok dengan filter</span>
                                    </td>
                                </tr>
                                <?php if (!empty($proposals)): ?>
                                     <?php foreach ($proposals as $proposal): ?>
                                         <?php 
                                             $statusClass = KoordinatorHelper::statusBadgeClass($proposal['status_verifikasi']); 
                                             $nims = !empty($proposal['nim']) ? explode(', ', $proposal['nim']) : [];
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
                                         <tr class="verif-row" data-angkatan="<?= $cohortsAttr ?>" data-status="<?= htmlspecialchars($proposal['status_verifikasi']) ?>">
                                            <td><?= htmlspecialchars($proposal['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($proposal['ketua_nama']) ?></td>
                                            <td><a href="../../backend/helpers/serve_file.php?path=<?= urlencode($proposal['file_path']) ?>" class="link-file" target="_blank"><?= htmlspecialchars(basename($proposal['file_path'])) ?></a></td>
                                            <td><?= htmlspecialchars(KoordinatorHelper::formatDateIndo($proposal['created_at'])) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($proposal['status_verifikasi'])) ?></span></td>
                                            <td class="aksi-group" style="display:flex; gap:8px;">
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
                                                    <input type="hidden" name="type" value="proposal">
                                                    <input type="hidden" name="id" value="<?= $proposal['proposal_id'] ?>">
                                                    <input type="hidden" name="action" value="disetujui">
                                                    <button type="submit" class="btn-setuju" <?= $proposal['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>Setuju</button>
                                                </form>
                                                <form method="POST" action="../../backend/actions/koordinator_verifikasi.php" style="margin:0;">
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
                    <!-- Pagination Controls -->
                    <div id="pagination-controls" style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px 24px; border-top: 1px solid #E2E8F0; background: #F8FAFC;"></div>
                </div>
            </div><!-- end page-verifikasi-proposal -->

            <script>
            let currentPage = 1;

            function calculateItemsPerPage(itemSelector, minItems = 5) {
                const items = Array.from(document.querySelectorAll(itemSelector));
                if (!items.length) return minItems;
                const pageWrapper = document.querySelector('.page.active') || items[0].closest('.page');
                const topOffset = pageWrapper ? pageWrapper.getBoundingClientRect().top : 0;
                const availableHeight = Math.max(window.innerHeight - topOffset - 240, 240);
                const sampleItems = items.filter(item => item.offsetHeight > 0).slice(0, 3);
                const measureItems = sampleItems.length ? sampleItems : items.slice(0, 3);
                const averageHeight = measureItems.reduce((sum, item) => sum + item.getBoundingClientRect().height, 0) / measureItems.length || 60;
                const count = Math.max(minItems, Math.floor(availableHeight / averageHeight));
                return Math.min(count, 20);
            }

            function applyFilters(resetPage = false) {
                if (resetPage === true) {
                    currentPage = 1;
                }
                const searchInput = document.getElementById('search-proposal')?.value.toLowerCase().trim() || '';
                const angkatanFilter = document.getElementById('filter-angkatan')?.value || 'ALL';
                const rows = document.querySelectorAll('#page-verifikasi-proposal tbody tr.verif-row');
                
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
                    
                    let matchesStatus = true;
                    const statusFilter = document.getElementById('filter-status')?.value || 'ALL';
                    if (statusFilter !== 'ALL') {
                        const rowStatus = (row.dataset.status || '').toLowerCase();
                        if (statusFilter === 'sudah') {
                            if (rowStatus !== 'disetujui') {
                                matchesStatus = false;
                            }
                        } else if (statusFilter === 'belum') {
                            if (rowStatus === 'disetujui') {
                                matchesStatus = false;
                            }
                        }
                    }
                    
                    if (matchesSearch && matchesAngkatan && matchesStatus) {
                        matchingRows.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const totalMatching = matchingRows.length;
                const itemsPerPage = calculateItemsPerPage('#page-verifikasi-proposal tbody tr.verif-row');
                const totalPages = Math.ceil(totalMatching / itemsPerPage) || 1;
                
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;
                
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                
                matchingRows.forEach((row, index) => {
                    if (index >= startIndex && index < endIndex) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const noResultRow = document.getElementById('no-results-row-verif');
                if (noResultRow) {
                    noResultRow.style.display = (totalMatching === 0) ? '' : 'none';
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