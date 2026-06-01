<?php include 'header.php'; ?>
<?php 
    $sortBy = $_GET['sort'] ?? 'tanggal_terbaru';
    $locations = KoordinatorHelper::getGroupsForLocationVerification($sortBy); 
?>

            <!-- PAGE: Verifikasi Lokasi -->
            <div id="page-verifikasi-lokasi" class="page active">
                <div class="page-title-bar">
                    <h1>Verifikasi Lokasi Magang</h1>
                    <span class="page-subtitle">Periksa dan setujui lokasi magang yang diajukan kelompok</span>
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
                            <input type="text" id="search-lokasi" placeholder="Cari lokasi..." oninput="applyFilters(true)" style="border: none; outline: none; font-size: 13px; font-family: 'Inter', sans-serif; width: 100%;">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body p-0">
                        <table id="tabel-lokasi" class="table" style="font-size: 13px;">
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
                                <tr id="no-results-row-verif" style="display: none;">
                                    <td colspan="5" style="text-align:center; padding: 50px 20px; color:#9CA3AF; font-size: 14px; line-height: 1.6;">
                                        <svg style="display: block; width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Belum ada pengajuan lokasi<br><span style="font-size: 13px;">atau tidak ada data yang cocok dengan filter</span>
                                    </td>
                                </tr>
                                <?php if (!empty($locations)): ?>
                                     <?php foreach ($locations as $location): ?>
                                         <?php 
                                             $statusClass = KoordinatorHelper::statusBadgeClass($location['status_verifikasi']); 
                                             $nims = !empty($location['nim']) ? explode(', ', $location['nim']) : [];
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
                                         <tr class="verif-row" data-angkatan="<?= $cohortsAttr ?>" data-status="<?= htmlspecialchars($location['status_verifikasi']) ?>">
                                            <td>
                                                <strong style="display:block; margin-bottom: 4px; font-size: 14px; color: #1E293B;"><?= htmlspecialchars($location['kelompok_nama']) ?></strong>
                                                <span style="color: #64748B;">Ketua: <?= htmlspecialchars($location['ketua_nama']) ?></span><br>
                                                <span style="color: #94A3B8; font-size: 11px;">Diajukan: <?= htmlspecialchars(KoordinatorHelper::formatDateIndo($location['created_at'])) ?></span>
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
                                            <td>
                                                 <span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($location['status_verifikasi'])) ?></span>
                                                 <?php if (!empty($location['catatan'])): ?>
                                                     <div style="font-size: 11px; color: #64748B; margin-top: 6px; font-style: italic; max-width: 150px; line-height: 1.4;">
                                                         <strong>Catatan:</strong> <?= htmlspecialchars($location['catatan']) ?>
                                                     </div>
                                                 <?php endif; ?>
                                             </td>
                                             <td style="width: 140px; vertical-align: middle; text-align: center;">
                                                 <div style="display:flex; flex-direction:column; gap:8px;">
                                                     <button type="button" class="btn btn-setuju" 
                                                             data-id="<?= $location['lokasi_id'] ?>"
                                                             data-kelompok="<?= htmlspecialchars($location['kelompok_nama'], ENT_QUOTES) ?>"
                                                             data-perusahaan="<?= htmlspecialchars($location['perusahaan'], ENT_QUOTES) ?>"
                                                             data-catatan="<?= htmlspecialchars($location['catatan'] ?? '', ENT_QUOTES) ?>"
                                                             style="width: 100%; background: #D1FAE5; color: #10B981; padding: 6px 12px; font-size: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: transform 0.15s, opacity 0.15s;" 
                                                             <?= $location['status_verifikasi'] === 'disetujui' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                                                         Setuju
                                                     </button>
                                                     <button type="button" class="btn btn-tolak" 
                                                             data-id="<?= $location['lokasi_id'] ?>"
                                                             data-kelompok="<?= htmlspecialchars($location['kelompok_nama'], ENT_QUOTES) ?>"
                                                             data-perusahaan="<?= htmlspecialchars($location['perusahaan'], ENT_QUOTES) ?>"
                                                             data-catatan="<?= htmlspecialchars($location['catatan'] ?? '', ENT_QUOTES) ?>"
                                                             style="width: 100%; background: #FEE2E2; color: #EF4444; padding: 6px 12px; font-size: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: transform 0.15s, opacity 0.15s;" 
                                                             <?= $location['status_verifikasi'] === 'ditolak' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                                                         Tolak
                                                     </button>
                                                 </div>
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
            </div><!-- end page-verifikasi-lokasi -->

            <!-- Modal Verifikasi Lokasi -->
            <div id="verifModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 10000; justify-content: center; align-items: center; transition: opacity 0.2s ease;">
                <div style="background: white; border-radius: 12px; width: 90%; max-width: 460px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                    <h3 id="modalTitle" style="margin-top: 0; margin-bottom: 8px; font-size: 18px; color: #0F172A; font-weight: 700; font-family: 'Inter', sans-serif;">Verifikasi Lokasi</h3>
                    <p id="modalSub" style="margin-top: 0; margin-bottom: 20px; font-size: 13px; color: #475569; line-height: 1.5; font-family: 'Inter', sans-serif;"></p>
                    
                    <form id="modalForm" method="POST" action="../../backend/actions/koordinator_verifikasi.php">
                        <input type="hidden" name="type" value="lokasi">
                        <input type="hidden" id="modalId" name="id" value="">
                        <input type="hidden" id="modalAction" name="action" value="">
                        
                        <div style="margin-bottom: 24px;">
                            <label for="modalCatatan" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; font-family: 'Inter', sans-serif;">Catatan Verifikasi (opsional):</label>
                            <textarea id="modalCatatan" name="catatan" placeholder="Tulis catatan, instruksi, atau alasan penolakan di sini..." style="width: 100%; height: 100px; padding: 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 13px; font-family: inherit; resize: vertical; box-sizing: border-box; outline: none; transition: border-color 0.15s, box-shadow 0.15s;"></textarea>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <button type="button" class="btn" style="background: #F1F5F9; color: #475569; padding: 10px 20px; font-size: 13px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif;" onclick="closeVerificationModal()">Batal</button>
                            <button type="submit" id="modalSubmitBtn" class="btn" style="color: white; padding: 10px 20px; font-size: 13px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif;">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>

            <style>
            @keyframes modalSlideUp {
                from { opacity: 0; transform: translateY(12px) scale(0.98); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .btn-setuju:hover { transform: scale(1.02); }
            .btn-tolak:hover { transform: scale(1.02); }
            </style>

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
                const searchInput = document.getElementById('search-lokasi')?.value.toLowerCase().trim() || '';
                const angkatanFilter = document.getElementById('filter-angkatan')?.value || 'ALL';
                const rows = document.querySelectorAll('#tabel-lokasi tbody tr.verif-row');
                
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
                const itemsPerPage = calculateItemsPerPage('#tabel-lokasi tbody tr.verif-row');
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
                
                document.querySelectorAll('.btn-setuju').forEach(btn => {
                    btn.addEventListener('click', () => {
                        openVerificationModal(btn.dataset.id, 'disetujui', btn.dataset.kelompok, btn.dataset.perusahaan, btn.dataset.catatan);
                    });
                });
                document.querySelectorAll('.btn-tolak').forEach(btn => {
                    btn.addEventListener('click', () => {
                        openVerificationModal(btn.dataset.id, 'ditolak', btn.dataset.kelompok, btn.dataset.perusahaan, btn.dataset.catatan);
                    });
                });
                
                const textarea = document.getElementById('modalCatatan');
                textarea.addEventListener('focus', () => {
                    textarea.style.borderColor = '#3B82F6';
                    textarea.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.15)';
                });
                textarea.addEventListener('blur', () => {
                    textarea.style.borderColor = '#CBD5E1';
                    textarea.style.boxShadow = 'none';
                });
            });

            function openVerificationModal(id, action, kelompok, perusahaan, catatan) {
                document.getElementById('modalId').value = id;
                document.getElementById('modalAction').value = action;
                document.getElementById('modalCatatan').value = catatan;
                
                const titleEl = document.getElementById('modalTitle');
                const subEl = document.getElementById('modalSub');
                const submitBtn = document.getElementById('modalSubmitBtn');
                
                if (action === 'disetujui') {
                    titleEl.textContent = "Setujui Pengajuan Lokasi";
                    subEl.innerHTML = `Apakah Anda yakin ingin menyetujui pengajuan lokasi magang untuk kelompok <strong>${kelompok}</strong> di <strong>${perusahaan}</strong>?`;
                    submitBtn.style.background = "#10B981";
                    submitBtn.textContent = "Setujui & Simpan";
                } else {
                    titleEl.textContent = "Tolak Pengajuan Lokasi";
                    subEl.innerHTML = `Apakah Anda yakin ingin menolak pengajuan lokasi magang untuk kelompok <strong>${kelompok}</strong> di <strong>${perusahaan}</strong>?`;
                    submitBtn.style.background = "#EF4444";
                    submitBtn.textContent = "Tolak & Simpan";
                }
                
                const modal = document.getElementById('verifModal');
                modal.style.display = 'flex';
            }

            function closeVerificationModal() {
                document.getElementById('verifModal').style.display = 'none';
            }

            // Close modal if clicked outside
            window.onclick = function(event) {
                const modal = document.getElementById('verifModal');
                if (event.target === modal) {
                    closeVerificationModal();
                }
            }
            </script>

<?php include 'footer.php'; ?>