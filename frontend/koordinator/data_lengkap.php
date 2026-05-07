<?php include 'header.php'; ?>
<?php
$sortBy = $_GET['sort'] ?? 'nama_a';
$completeData = getCompleteGroupsData($sortBy);
?>

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
                    <div class="plot-filter-group">
                        <button class="btn btn-primary" onclick="window.location.href='export_data.php?action=export_excel'" style="padding: 8px 16px; background-color: #10B981; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            📥 Export Excel
                        </button>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; margin-left: auto;">
                        <label for="sort-data-lengkap" style="font-size: 13px; font-weight: 600; color: #334155; white-space: nowrap;">Urutkan:</label>
                        <select id="sort-data-lengkap" onchange="changeSortPage(this.value)" style="padding: 8px 12px; border: 1.5px solid #DDEAF5; border-radius: 6px; font-size: 13px; font-family: 'Inter', sans-serif; color: #333; background: white; cursor: pointer; outline: none;">
                            <option value="nama_a" <?= $sortBy === 'nama_a' ? 'selected' : '' ?>>📖 Nama Kelompok (A-Z)</option>
                            <option value="nama_z" <?= $sortBy === 'nama_z' ? 'selected' : '' ?>>📖 Nama Kelompok (Z-A)</option>
                            <option value="ketua_a" <?= $sortBy === 'ketua_a' ? 'selected' : '' ?>>👤 Nama Ketua (A-Z)</option>
                            <option value="ketua_z" <?= $sortBy === 'ketua_z' ? 'selected' : '' ?>>👤 Nama Ketua (Z-A)</option>
                            <option value="jumlah_mhs" <?= $sortBy === 'jumlah_mhs' ? 'selected' : '' ?>>👥 Jumlah Mahasiswa</option>
                        </select>
                    </div>
                </div>

                <!-- Tabel Data Lengkap -->
                <div class="card">
                    <div class="card-body p-0">
                        <div style="overflow-x: auto;">
                            <table class="table" id="tabel-data-lengkap" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th>Jml MHS</th>
                                        <th>Nomor Kelompok</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>NIM</th>
                                        <th>NO HP</th>
                                        <th>Lokasi Magang</th>
                                        <th>Alamat Lengkap</th>
                                        <th>Link Maps</th>
                                        <th>Progress Proposal</th>
                                        <th>Nama & Kontak Person</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-data-lengkap">
                                    <?php if (empty($completeData)): ?>
                                        <tr>
                                            <td colspan="10" style="text-align:center; padding: 20px; color:#6B7280;">Belum ada data kelompok.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($completeData as $row): ?>
                                        <?php
                                            $googleMapsLink = generateGoogleMapsLink($row['latitude'], $row['longitude']);
                                            $proposalStatus = ucfirst(str_replace('_', ' ', $row['status_proposal']));
                                            $statusBadge = match($row['status_proposal']) {
                                                'disetujui' => '<span class="badge" style="background-color: #10B981; color: white; padding: 4px 8px; border-radius: 4px;">✓ Disetujui</span>',
                                                'menunggu' => '<span class="badge" style="background-color: #FBBF24; color: #1F2937; padding: 4px 8px; border-radius: 4px;">⧖ Menunggu</span>',
                                                'ditolak' => '<span class="badge" style="background-color: #EF4444; color: white; padding: 4px 8px; border-radius: 4px;">✗ Ditolak</span>',
                                                default => '<span class="badge" style="background-color: #D1D5DB; color: #1F2937; padding: 4px 8px; border-radius: 4px;">- Belum Upload</span>'
                                            };
                                        ?>
                                        <tr class="data-row" data-search="<?= htmlspecialchars(strtolower($row['kelompok_nama'] . ' ' . $row['nama_mahasiswa'] . ' ' . $row['lokasi_magang'])) ?>">
                                            <td><?= $row['jumlah_mhs'] ?></td>
                                            <td><?= htmlspecialchars($row['kelompok_nama']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                            <td><?= htmlspecialchars($row['nim']) ?></td>
                                            <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                            <td><?= htmlspecialchars($row['lokasi_magang']) ?></td>
                                            <td><?= htmlspecialchars($row['alamat_lengkap']) ?></td>
                                            <td>
                                                <?php if ($googleMapsLink !== '-'): ?>
                                                    <a href="<?= htmlspecialchars($googleMapsLink) ?>" target="_blank" style="color: #3B82F6; text-decoration: none;">📍 Maps</a>
                                                <?php else: ?>
                                                    <span style="color: #9CA3AF;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $statusBadge ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['ketua_nama']) ?></strong><br>
                                                <small><?= htmlspecialchars($row['kontak_ketua']) ?></small><br>
                                                <small><?= htmlspecialchars($row['email_ketua']) ?></small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- end page-data-lengkap -->

<script>
function filterDataLengkap() {
    const searchInput = document.getElementById('search-data-lengkap').value.toLowerCase();
    const rows = document.querySelectorAll('#tbody-data-lengkap .data-row');
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        if (searchText.includes(searchInput)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include 'footer.php'; ?>
