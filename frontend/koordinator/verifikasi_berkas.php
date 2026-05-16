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
                                                        <!-- Tombol Setuju Semua (AJAX) -->
                                                        <button
                                                            type="button"
                                                            class="btn btn-verifikasi"
                                                            data-type="berkas_mahasiswa"
                                                            data-id="<?= $anggotaId ?>"
                                                            data-action="disetujui"
                                                            data-scope="semua"
                                                            style="background:#D1FAE5; color:#10B981; padding:4px 10px; font-size:12px; font-weight:600; border-radius:4px; border:1px solid #10B981; cursor:pointer;"
                                                            onclick="event.stopPropagation();">
                                                            Setuju Semua
                                                        </button>
                                                        <!-- Tombol Tolak Semua (AJAX) -->
                                                        <button
                                                            type="button"
                                                            class="btn btn-verifikasi"
                                                            data-type="berkas_mahasiswa"
                                                            data-id="<?= $anggotaId ?>"
                                                            data-action="ditolak"
                                                            data-scope="semua"
                                                            style="background:#FEE2E2; color:#EF4444; padding:4px 10px; font-size:12px; font-weight:600; border-radius:4px; border:1px solid #EF4444; cursor:pointer;"
                                                            onclick="event.stopPropagation();">
                                                            Tolak Semua
                                                        </button>
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
                                                            <tr id="row-berkas-<?= $b['berkas_id'] ?>">
                                                                <td style="padding-left: 20px;"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $b['jenis_berkas']))) ?></td>
                                                                <td><a href="../../backend/helpers/serve_file.php?path=<?= urlencode($b['file_path']) ?>" target="_blank" style="color:#2563EB; text-decoration:none; font-weight:600;">Lihat File</a></td>
                                                                <td>
                                                                    <!-- Badge status dengan id unik agar bisa diupdate oleh JS -->
                                                                    <span
                                                                        id="badge-<?= $b['berkas_id'] ?>"
                                                                        class="badge <?= KoordinatorHelper::statusBadgeClass($b['status_verifikasi']) ?>">
                                                                        <?= htmlspecialchars(ucfirst($b['status_verifikasi'])) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div style="display:flex; gap:8px;">
                                                                        <!-- Tombol Setuju Satuan (AJAX) -->
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-verifikasi"
                                                                            data-type="berkas_satuan"
                                                                            data-id="<?= $b['berkas_id'] ?>"
                                                                            data-action="disetujui"
                                                                            data-scope="satuan"
                                                                            style="background:#D1FAE5; color:#10B981; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:4px; border:1px solid #10B981; cursor:pointer;"
                                                                            <?= $b['status_verifikasi'] === 'disetujui' ? 'disabled' : '' ?>>
                                                                            Setuju
                                                                        </button>
                                                                        <!-- Tombol Tolak Satuan (AJAX) -->
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-verifikasi"
                                                                            data-type="berkas_satuan"
                                                                            data-id="<?= $b['berkas_id'] ?>"
                                                                            data-action="ditolak"
                                                                            data-scope="satuan"
                                                                            style="background:#FEE2E2; color:#EF4444; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:4px; border:1px solid #EF4444; cursor:pointer;"
                                                                            <?= $b['status_verifikasi'] === 'ditolak' ? 'disabled' : '' ?>>
                                                                            Tolak
                                                                        </button>
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

<!-- ===================== AJAX SCRIPT ===================== -->
<script>
(function () {
    'use strict';

    /**
     * Peta kelas badge berdasarkan status
     * Sesuaikan dengan kelas CSS yang dipakai KoordinatorHelper::statusBadgeClass()
     */
    const BADGE_CLASSES = {
        disetujui : 'badge-success',   // ganti sesuai kelas aslinya
        ditolak   : 'badge-danger',    // ganti sesuai kelas aslinya
        menunggu  : 'badge-warning',   // ganti sesuai kelas aslinya
    };

    /** Semua kelas badge yang mungkin, agar bisa direset */
    const ALL_BADGE_CLASSES = Object.values(BADGE_CLASSES).join(' ');

    /**
     * Update badge status satu berkas satuan
     */
    function updateBadge(berkasId, action) {
        const badge = document.getElementById('badge-' + berkasId);
        if (!badge) return;

        badge.className = 'badge ' + (BADGE_CLASSES[action] ?? '');
        badge.textContent = action.charAt(0).toUpperCase() + action.slice(1);
    }

    /**
     * Update semua badge berkas dalam satu <details> mahasiswa
     * (dipakai setelah "Setuju Semua" / "Tolak Semua")
     *
     * Tombol "Semua" ada di dalam <summary>, sehingga closest('details')
     * menunjuk ke <details> mahasiswa itu sendiri — gunakan parentElement
     * dari <summary> untuk mengambil <details>, lalu cari konten di luar <summary>.
     */
    function updateAllBadgesInBlock(btnSemua, action) {
        // btnSemua → <summary> → <details.mahasiswa-dropdown>
        const summaryEl        = btnSemua.closest('summary');
        const detailsMahasiswa = summaryEl ? summaryEl.parentElement : null;

        if (!detailsMahasiswa) return;

        // Cari badge langsung dari <details> — hindari querySelector('div')
        // yang bisa menangkap div di dalam <summary> (penyebab badges count: 0)
        detailsMahasiswa.querySelectorAll('[id^="badge-"]').forEach(function (badge) {
            badge.className   = 'badge ' + (BADGE_CLASSES[action] ?? '');
            badge.textContent = action.charAt(0).toUpperCase() + action.slice(1);
        });

        // Nonaktifkan tombol satuan yang sudah sesuai, aktifkan yang lain
        detailsMahasiswa.querySelectorAll('.btn-verifikasi[data-scope="satuan"]').forEach(function (btn) {
            btn.disabled = (btn.dataset.action === action);
        });
    }

    /**
     * Kirim request AJAX ke koordinator_verifikasi.php
     */
    function kirimVerifikasi(btn, type, id, action, scope) {
        // Cegah double-klik
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = '...';

        const body = new URLSearchParams({ type, id, action });

        fetch('../../backend/actions/koordinator_verifikasi.php', {
            method : 'POST',
            headers: {
                'Content-Type'     : 'application/x-www-form-urlencoded',
                'X-Requested-With' : 'XMLHttpRequest',   // penanda AJAX
            },
            body: body.toString(),
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            if (data.success) {
                if (scope === 'satuan') {
                    // Update badge satu baris saja
                    updateBadge(id, action);

                    // Aktifkan kembali tombol lawan, nonaktifkan tombol ini
                    const row = document.getElementById('row-berkas-' + id);
                    if (row) {
                        row.querySelectorAll('.btn-verifikasi').forEach(function (b) {
                            b.disabled = (b.dataset.action === action);
                        });
                    }
                } else {
                    // "Setuju Semua" / "Tolak Semua" — update semua badge dalam blok
                    updateAllBadgesInBlock(btn, action);
                }

                showToast(data.message || 'Berhasil disimpan', 'success');
            } else {
                showToast(data.message || 'Gagal menyimpan', 'error');
                btn.disabled = false;
            }
        })
        .catch(function (err) {
            console.error('AJAX error:', err);
            showToast('Terjadi kesalahan koneksi.', 'error');
            btn.disabled = false;
        })
        .finally(function () {
            // Kembalikan teks tombol jika masih enabled
            if (!btn.disabled) btn.textContent = originalText;
            // Jika tombol disabled (berhasil), teks sudah tidak tampil karena disabled
            // tapi tetap kembalikan agar konsisten
            btn.textContent = originalText;
        });
    }

    /**
     * Toast notifikasi ringan tanpa library
     */
    function showToast(msg, type) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = [
                'position:fixed', 'bottom:24px', 'right:24px', 'z-index:9999',
                'display:flex', 'flex-direction:column', 'gap:8px',
            ].join(';');
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
                : 'background:#FEE2E2;color:#991B1B;border:1px solid #EF4444',
        ].join(';');

        container.appendChild(toast);

        // Animasi masuk
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                toast.style.opacity  = '1';
                toast.style.transform = 'translateY(0)';
            });
        });

        // Hilangkan setelah 3 detik
        setTimeout(function () {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateY(8px)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    }

    // ---- Event delegation: tangkap semua klik .btn-verifikasi ----
    // Gunakan capture phase (true) agar intercept sebelum browser proses toggle <details>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-verifikasi');
        if (!btn) return;

        // Cegah klik tombol ikut toggle <details> jika tombol ada di dalam <summary>
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const type   = btn.dataset.type;
        const id     = btn.dataset.id;
        const action = btn.dataset.action;
        const scope  = btn.dataset.scope;   // 'satuan' | 'semua'

        if (!type || !id || !action) return;

        kirimVerifikasi(btn, type, id, action, scope);
    }, true); // capture: true

})();
</script>
<!-- ================== END AJAX SCRIPT ================== -->

<?php include 'footer.php'; ?>