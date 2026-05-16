<?php include 'header.php'; ?>
<?php
$sortBy = $_GET['sort'] ?? 'nama_a';
$completeData = KoordinatorHelper::getCompleteGroupsData($sortBy);

// Pagination Logic
$itemsPerPage = 5;
$totalItems = count($completeData);
$totalPages = max(1, ceil($totalItems / $itemsPerPage));
$currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$currentPage = max(1, min($totalPages, $currentPage));
$offset = ($currentPage - 1) * $itemsPerPage;

$displayData = array_slice($completeData, $offset, $itemsPerPage);

// Helper to build URL with current params
function buildPageUrl($pageNum) {
    $params = $_GET;
    $params['p'] = $pageNum;
    return '?' . http_build_query($params);
}
?>

<style>
    /* Styling khusus untuk tabel data lengkap sesuai screenshot */
    #tabel-data-lengkap {
        border-collapse: collapse;
        width: 100%;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    #tabel-data-lengkap thead th {
        background-color: #6388AF; /* Steel Blue dari screenshot */
        color: white;
        font-weight: 600;
        font-size: 12px;
        text-align: center;
        padding: 15px 10px;
        border: none;
        vertical-align: middle;
    }

    #tabel-data-lengkap tbody tr {
        border-bottom: 1px solid #E2E8F0;
    }

    #tabel-data-lengkap tbody td {
        vertical-align: middle;
        text-align: center;
        color: #334155;
        font-size: 11px;
        padding: 0; /* Reset padding untuk mendukung split view */
        border: 1px solid #E2E8F0;
    }

    /* Kontainer untuk data yang bersifat list (mahasiswa) */
    .split-container {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .split-item {
        padding: 12px 10px;
        border-bottom: 1px solid #E2E8F0;
        min-height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
    }

    .split-item:last-child {
        border-bottom: none;
    }

    /* Kontainer untuk data tunggal (lokasi, dll) agar tetap ada padding */
    .single-content {
        padding: 12px 10px;
        line-height: 1.5;
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-disetujui { background-color: #10B981; color: white; }
    .badge-menunggu { background-color: #FBBF24; color: #1F2937; }
    .badge-ditolak { background-color: #EF4444; color: white; }
    .badge-belum { background-color: #94A3B8; color: white; }

    .btn-maps {
        display: inline-block;
        padding: 5px 10px;
        background-color: #DBEAFE;
        color: #1E40AF;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        font-size: 10px;
        transition: all 0.2s;
    }

    .btn-maps:hover {
        background-color: #BFDBFE;
    }

    .text-left { text-align: left !important; justify-content: flex-start !important; }
    .plot-search-wrap { flex: 1; max-width: 400px; }
    
    /* Toolbar styling */
    .plotting-toolbar {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Pagination Styling */
    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 25px;
        padding-bottom: 20px;
    }

    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        background: white;
        border: 1px solid #E2E8F0;
        color: #64748B;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
    }

    .page-link:hover {
        border-color: #6388AF;
        color: #6388AF;
        background: #F8FAFC;
    }

    .page-link.active {
        background: #6388AF;
        color: white;
        border-color: #6388AF;
    }

    .page-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
        background: #F1F5F9;
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
        
        <button class="btn" onclick="window.location.href='export_data.php?action=export_excel'" 
            style="padding: 10px 20px; background-color: #10B981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            📥 Export Excel
        </button>

        <div style="display: flex; gap: 12px; align-items: center; margin-left: auto;">
            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 11px; font-weight: 600; color: #64748B; margin-bottom: 2px;">Urutkan:</label>
                <select id="sort-data-lengkap" onchange="changeSortPage(this.value)" style="padding: 8px 12px; border: 1.5px solid #E2E8F0; border-radius: 6px; font-size: 12px; color: #334155; background: white; cursor: pointer; outline: none;">
                    <option value="nama_a" <?= $sortBy === 'nama_a' ? 'selected' : '' ?>>📖 Nama Kelompok (A-Z)</option>
                    <option value="nama_z" <?= $sortBy === 'nama_z' ? 'selected' : '' ?>>📖 Nama Kelompok (Z-A)</option>
                    <option value="ketua_a" <?= $sortBy === 'ketua_a' ? 'selected' : '' ?>>👤 Nama Ketua (A-Z)</option>
                    <option value="ketua_z" <?= $sortBy === 'ketua_z' ? 'selected' : '' ?>>👤 Nama Ketua (Z-A)</option>
                    <option value="jumlah_mhs" <?= $sortBy === 'jumlah_mhs' ? 'selected' : '' ?>>👥 Jumlah Mahasiswa</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Info Stats -->
    <div style="margin-bottom: 15px; font-size: 13px; color: #64748B;">
        Menampilkan kelompok <span style="font-weight: 600; color: #1E293B;"><?= $offset + 1 ?>-<?= min($offset + $itemsPerPage, $totalItems) ?></span> dari <span style="font-weight: 600; color: #1E293B;"><?= $totalItems ?></span> kelompok
    </div>

    <!-- Tabel Data Lengkap -->
    <div style="overflow-x: auto; background: white; border-radius: 8px;">
        <table id="tabel-data-lengkap">
            <thead>
                <tr>
                    <th style="width: 60px;">Jml MHS</th>
                    <th style="width: 120px;">Nama Kelompok</th>
                    <th style="width: 180px;">Nama Mahasiswa</th>
                    <th style="width: 120px;">NIM</th>
                    <th style="width: 130px;">NO HP</th>
                    <th style="width: 150px;">Lokasi Magang</th>
                    <th style="width: 200px;">Alamat Lengkap</th>
                    <th style="width: 100px;">Link Maps</th>
                    <th style="width: 140px;">Progress Proposal</th>
                    <th style="width: 180px;">Kontak Person Industri</th>
                </tr>
            </thead>
            <tbody id="tbody-data-lengkap">
                <?php if (empty($displayData)): ?>
                    <tr>
                        <td colspan="10" style="padding: 40px; color:#94A3B8; font-style: italic;">Belum ada data kelompok yang terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($displayData as $row): ?>
                    <?php
                        $googleMapsLink = KoordinatorHelper::generateGoogleMapsLink($row['latitude'], $row['longitude'], $row['alamat_lengkap']);
                        
                        // Split data mahasiswa
                        $names = explode(', ', $row['nama_mahasiswa'] ?? '');
                        $nims = explode(', ', $row['nim'] ?? '');
                        $phones = explode(', ', $row['no_hp'] ?? '');
                        
                        $statusBadge = match($row['status_proposal']) {
                            'disetujui' => '<span class="badge-status badge-disetujui">✓ Disetujui</span>',
                            'menunggu' => '<span class="badge-status badge-menunggu">⧖ Menunggu</span>',
                            'ditolak' => '<span class="badge-status badge-ditolak">✗ Ditolak</span>',
                            default => '<span class="badge-status badge-belum">Belum Upload</span>'
                        };
                    ?>
                    <tr class="data-row" data-search="<?= htmlspecialchars(strtolower($row['kelompok_nama'] . ' ' . $row['nama_mahasiswa'] . ' ' . $row['lokasi_magang'])) ?>">
                        <td><div class="single-content" style="font-weight: 700;"><?= $row['jumlah_mhs'] ?></div></td>
                        <td><div class="single-content" style="font-weight: 600;"><?= htmlspecialchars($row['kelompok_nama']) ?></div></td>
                        
                        <!-- Kolom Mahasiswa (Split) -->
                        <td>
                            <div class="split-container">
                                <?php foreach($names as $name): ?>
                                    <div class="split-item text-left"><?= htmlspecialchars($name) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div class="split-container">
                                <?php foreach($nims as $nim): ?>
                                    <div class="split-item"><?= htmlspecialchars($nim) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div class="split-container">
                                <?php foreach($phones as $phone): ?>
                                    <div class="split-item"><?= htmlspecialchars($phone) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </td>

                        <td><div class="single-content"><?= htmlspecialchars($row['lokasi_magang']) ?></div></td>
                        
                        <?php
                            $alamat_words = explode(' ', $row['alamat_lengkap'] ?? '');
                            $alamat_short = count($alamat_words) > 8 ? implode(' ', array_slice($alamat_words, 0, 8)) . '...' : ($row['alamat_lengkap'] ?? '');
                        ?>
                        <td title="<?= htmlspecialchars($row['alamat_lengkap'] ?? '') ?>">
                            <div class="single-content text-left"><?= htmlspecialchars($alamat_short) ?></div>
                        </td>

                        <td>
                            <div class="single-content">
                                <?php if ($googleMapsLink !== '-'): ?>
                                    <a href="<?= htmlspecialchars($googleMapsLink) ?>" target="_blank" class="btn-maps">📍 Maps</a>
                                <?php else: ?>
                                    <span style="color: #CBD5E1;">-</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td><div class="single-content"><?= $statusBadge ?></div></td>
                        
                        <td class="text-left">
                            <div class="single-content">
                                <strong style="color: #1E293B; font-size: 12px;"><i class="fas fa-user-tie" style="margin-right: 5px; color: #6388AF;"></i><?= htmlspecialchars($row['cp_nama']) ?></strong><br>
                                <span style="color: #64748B; font-size: 11px;"><i class="fas fa-phone-alt" style="margin-right: 5px; opacity: 0.7;"></i><?= htmlspecialchars($row['cp_tlp']) ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-container">
        <!-- Previous -->
        <a href="<?= buildPageUrl($currentPage - 1) ?>" class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>">
            &laquo; Prev
        </a>

        <?php
            // Calculate range of pages to show
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $startPage + 4);
            if ($endPage - $startPage < 4) $startPage = max(1, $endPage - 4);
        ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="<?= buildPageUrl($i) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <!-- Next -->
        <a href="<?= buildPageUrl($currentPage + 1) ?>" class="page-link <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
            Next &raquo;
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
function filterDataLengkap() {
    const searchInput = document.getElementById('search-data-lengkap').value.toLowerCase();
    const rows = document.querySelectorAll('#tbody-data-lengkap .data-row');
    
    // Note: Search currently only filters items on the current page because of PHP pagination.
    // To search everything, we would need to do search in backend or load all data and paginate via JS.
    // Given the request for pagination, backend pagination is usually preferred for large data.
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
