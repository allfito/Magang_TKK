<?php
require_once __DIR__ . '/../../backend/helpers/MahasiswaHelper.php';
MahasiswaHelper::requireLogin();

// Load existing data via Controller (must be before header outputs HTML)
require_once __DIR__ . '/../../backend/controllers/MahasiswaPendaftaranViewController.php';

$pageTitle = 'Pendaftaran Magang - SIMM';
$activePage = 'pendaftaran';
require __DIR__ . '/header.php';

$userId = (int) $_SESSION['user_id'];
$controller = new MahasiswaPendaftaranViewController();
$pendaftaranData = $controller->getPendaftaranData($userId);

$kelompokId = $pendaftaranData['kelompokId'];
$anggotaList = $pendaftaranData['anggotaList'];
$lokasiDb = $pendaftaranData['lokasi'];
$proposalDb = $pendaftaranData['proposal'];
$buktiDb = $pendaftaranData['bukti'];

$lokasi = $lokasiDb ?: ($_SESSION['form_data']['lokasi'] ?? null);
$proposal = $proposalDb ?: ($_SESSION['form_data']['proposal'] ?? null);
$bukti = $buktiDb ?: ($_SESSION['form_data']['bukti'] ?? null);
$plotting = $pendaftaranData['plotting'];
$berkasData = $pendaftaranData['berkasData'];
$berkasFilePaths = $pendaftaranData['berkasFilePaths'];
$berkasStatusMap = $pendaftaranData['berkasStatusMap'];
$berkasUploadDate = $pendaftaranData['berkasUploadDate'];
$berkasStatus = $pendaftaranData['berkasStatus'];

$openFormBerkas = false;
if (isset($_SESSION['open_form_berkas'])) {
    $openFormBerkas = true;
    unset($_SESSION['open_form_berkas']);
}

// Format upload date for display
$berkasUploadFormatted = '';
if ($berkasUploadDate) {
    $bulanNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $dt = new DateTime($berkasUploadDate);
    $berkasUploadFormatted = $dt->format('j') . ' ' . $bulanNames[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
}

// Determine if berkas form should be editable (menunggu or ditolak can edit)
// Note: $berkasEditable is set after step variables below

$step1Done = ($lokasiDb !== null);
$step1Status = $lokasiDb['status_verifikasi'] ?? 'belum';

$step2Open = ($step1Status === 'disetujui');
$step2Done = ($proposalDb !== null);
$step2Status = $proposalDb['status_verifikasi'] ?? 'belum';

$step3Open = ($step2Status === 'disetujui');
$step3Done = ($berkasStatus !== 'belum');
$step3Status = $berkasStatus;
$berkasEditable = ($step3Done && ($berkasStatus === 'menunggu' || $berkasStatus === 'ditolak'));

$step4Open = ($step3Status === 'disetujui');
$step4Done = ($buktiDb !== null);
$step4Status = $buktiDb['status_verifikasi'] ?? 'belum';

$step5Open = ($step4Status === 'disetujui');
$step5Done = ($plotting !== null);

function stepBadge($status) {
    if ($status === 'disetujui') return '<span class="badge badge-success-status" style="background:#28C76F;color:white;">Disetujui</span>';
    if ($status === 'ditolak') return '<span class="badge badge-danger">Ditolak</span>';
    return '<span class="badge badge-warning">Menunggu Verifikasi</span>';
}

$successMsg = $_SESSION['success'] ?? '';
$errorMsg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>



            <div id="accordion">

                        <!-- Tahap 1 -->
                        <div class="tahap-container active" id="step-1">
                            <div class="t-header t-header-dark tahap-hdr-flex" id="header-1">
                                <div class="tahap-hdr-left">
                                    <div class="tahap-hdr-icon">
                                        <svg viewBox="0 0 24 24" fill="white" width="16" height="16"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                    </div>
                                    Tahap 1 : Lokasi magang <?= $step1Done ? '&#10003;' : '' ?>
                                </div>
                                <?php if ($step1Done): ?>
                                <span class="tahap-status-pill pill-<?= $step1Status === 'disetujui' ? 'success' : ($step1Status === 'ditolak' ? 'danger' : 'warning') ?>">
                                    <?php if ($step1Status === 'disetujui'): ?>
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    Disetujui
                                    <?php elseif ($step1Status === 'ditolak'): ?>Ditolak
                                    <?php else: ?>Menunggu Verifikasi<?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Form -->
                            <div class="t-body t-form" id="form-1" style="<?= $step1Done ? 'display:none;' : '' ?>">
                                <form class="profile-form" id="form-el-1" method="POST" action="../../backend/actions/mahasiswa_pendaftaran.php" onsubmit="return isiCompleted(1)">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Perusahaan <span style="color:#EA5455">*</span></label>
                                            <input type="text" name="perusahaan" id="inp-perusahaan" placeholder="Nama perusahaan" value="<?= htmlspecialchars($lokasi['perusahaan'] ?? '') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Nama Pimpinan <span style="color:#EA5455">*</span></label>
                                            <input type="text" id="inp-pimpinan" name="nama_pimpinan"
                                                placeholder="Nama pimpinan / contact person" value="<?= htmlspecialchars($lokasi['nama_pimpinan'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Bidang <span style="color:#EA5455">*</span></label>
                                            <input type="text" id="inp-bidang" name="bidang" placeholder="Bidang perusahaan" value="<?= htmlspecialchars($lokasi['bidang'] ?? '') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Telepon <span style="color:#EA5455">*</span></label>
                                            <input type="text" id="inp-telepon" name="telepon" placeholder="Nomor telepon" required maxlength="15" value="<?= htmlspecialchars($lokasi['telepon'] ?? '') ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Alamat <span style="color:#EA5455">*</span></label>
                                            <input type="text" name="alamat" id="inp-alamat" placeholder="Alamat lengkap perusahaan" value="<?= htmlspecialchars($lokasi['alamat'] ?? '') ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-row" style="margin-top: 15px;">
                                        <div class="form-group" style="width: 100%;">
                                            <label>Peta Lokasi</label>
                                            <div style="margin-bottom: 8px; display: flex; gap: 8px;">
                                                <input type="text" id="map-search" placeholder="Cari lokasi di peta..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                                <button type="button" id="btn-search-map" class="btn btn-dark" style="padding: 8px 15px;">Cari</button>
                                            </div>
                                            <div id="map-container" style="width: 100%; height: 300px; border-radius: 8px; border: 1px solid #ddd; z-index: 1;"></div>
                                            <small style="color: #6e6b7b; display: block; margin-top: 5px;">Klik pada peta untuk memilih lokasi, atau gunakan fitur pencarian di atas.</small>
                                        </div>
                                    </div>



                                    <p id="err-1" style="color:#EA5455;font-size:12px;display:none;margin-bottom:8px;">
                                        &#9888; Harap isi semua field yang wajib diisi.</p>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-dark">Simpan Lokasi Magang</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Completed -->
                            <div id="completed-1" style="<?= $step1Done ? '' : 'display:none;' ?>">
                                <div class="lokasi-completed-wrap">
                                    <!-- 3-column info grid -->
                                    <div class="lokasi-info-grid">
                                        <!-- Card 1 -->
                                        <div class="info-card">
                                            <div class="info-card-title">
                                                <div class="info-card-icon-wrap"><svg viewBox="0 0 24 24" fill="#4A6D8C" width="16" height="16"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg></div>
                                                Informasi Perusahaan &amp; Lokasi
                                            </div>
                                            <div class="info-row-item">
                                                <svg class="info-row-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7V3H2v18h20V7H12z"/></svg>
                                                <span class="info-row-label">Perusahaan</span>
                                                <span class="info-row-value" id="v-perusahaan"><?= htmlspecialchars($lokasi['perusahaan'] ?? '-') ?></span>
                                            </div>
                                            <div class="info-row-item">
                                                <svg class="info-row-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.85.5 13.5.5c-1.36 0-2.5 1.14-2.5 2.5H6c-1.1 0-2 .9-2 2v13h18V8c0-1.1-.9-2-2-2z"/></svg>
                                                <span class="info-row-label">Bidang</span>
                                                <span class="info-row-value" id="v-bidang"><?= htmlspecialchars($lokasi['bidang'] ?? '-') ?></span>
                                            </div>
                                            <div class="info-row-item">
                                                <svg class="info-row-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                                <span class="info-row-label">Alamat</span>
                                                <span class="info-row-value" id="v-alamat"><?= htmlspecialchars($lokasi['alamat'] ?? '-') ?></span>
                                            </div>
                                        </div>
                                        <!-- Card 2 -->
                                        <div class="info-card">
                                            <div class="info-card-title">
                                                <div class="info-card-icon-wrap"><svg viewBox="0 0 24 24" fill="#4A6D8C" width="16" height="16"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                                                Kontak
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">Contact Person</span>
                                                <span class="info-field-value" id="v-pimpinan"><?= htmlspecialchars($lokasi['nama_pimpinan'] ?? '-') ?></span>
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">Telepon</span>
                                                <span class="info-field-value" id="v-telepon"><?= htmlspecialchars($lokasi['telepon'] ?? '-') ?></span>
                                            </div>
                                        </div>
                                        <!-- Card 3 -->
                                        <div class="info-card">
                                            <div class="info-card-title">
                                                <div class="info-card-icon-wrap"><svg viewBox="0 0 24 24" fill="#4A6D8C" width="16" height="16"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg></div>
                                                Informasi Tambahan
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">Tanggal Disetujui</span>
                                                <span class="info-field-value"><?php
                                                    $tgl = '-';
                                                    if ($step1Status === 'disetujui' && !empty($lokasi['updated_at'])) {
                                                        $bn = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                        $dt = new DateTime($lokasi['updated_at']);
                                                        $tgl = $dt->format('j') . ' ' . $bn[(int)$dt->format('n')-1] . ' ' . $dt->format('Y');
                                                    } echo $tgl; ?></span>
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">Catatan</span>
                                                <span class="info-field-value"><?= htmlspecialchars($lokasi['catatan'] ?? '-') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Map -->
                                    <div class="lokasi-map-section">
                                        <a href="https://maps.google.com/?q=<?= urlencode($lokasi['alamat'] ?? '') ?>" target="_blank" class="btn-google-maps">
                                            Buka di Google Maps
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                                        </a>
                                        <iframe width="100%" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="display:block;"
                                            src="https://maps.google.com/maps?q=<?= urlencode($lokasi['alamat'] ?? 'Indonesia') ?>&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                                    </div>
                                    <?php if ($step1Status === 'menunggu' || $step1Status === 'ditolak'): ?>
                                    <div style="margin-top:16px;display:flex;gap:10px;">
                                        <?php if ($step1Status === 'menunggu'): ?>
                                        <button type="button" class="btn btn-dark" onclick="editTahap(1)" style="display:inline-flex;align-items:center;gap:6px;">&#9998; Edit Lokasi</button>
                                        <?php endif; ?>
                                        
                                        <?php if ($step1Status === 'ditolak'): ?>
                                        <form method="POST" action="../../backend/actions/mahasiswa_hapus.php" style="display:inline;">
                                            <input type="hidden" name="type" value="lokasi">
                                            <button type="submit" class="btn" style="background:#EA5455;color:white;">Ajukan Ulang</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tahap 2 -->
                        <div class="tahap-container <?= $step2Open ? 'active' : 'locked' ?>" id="step-2">
                            <div class="t-header <?= $step2Open ? 't-header-dark' : 't-header-white' ?> tahap-hdr-flex" id="header-2">
                                <div class="tahap-hdr-left">
                                    <div class="tahap-hdr-icon">
                                        <svg viewBox="0 0 24 24" fill="<?= $step2Open ? 'white' : '#4A6D8C' ?>" width="16" height="16"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                    </div>
                                    Tahap 2 : Proposal Kelompok <?= $step2Done ? '&#10003;' : '' ?>
                                </div>
                                <?php if ($step2Done): ?>
                                <span class="tahap-status-pill pill-<?= $step2Status === 'disetujui' ? 'success' : ($step2Status === 'ditolak' ? 'danger' : 'warning') ?>">
                                    <?php if ($step2Status === 'disetujui'): ?>
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    Disetujui
                                    <?php elseif ($step2Status === 'ditolak'): ?>Ditolak
                                    <?php else: ?>Menunggu Verifikasi<?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="t-body t-form" id="form-2" style="<?= ($step2Open && !$step2Done) ? '' : 'display: none;' ?>">
                                <form class="profile-form" id="form-el-2" method="POST" action="../../backend/actions/mahasiswa_proposal.php" enctype="multipart/form-data" onsubmit="return isiCompleted(2)">
                                    <div class="form-group">
                                        <label>Judul Proposal <span style="color:#EA5455">*</span></label>
                                        <input type="text" name="judul" id="inp-judul" placeholder="Masukkan judul proposal" value="<?= htmlspecialchars($proposal['judul'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>File Proposal (PDF) <span style="color:#EA5455">*</span></label>
                                        <div class="file-input-row">
                                            <input type="file" name="proposal" id="file-tahap2" accept=".pdf" <?= empty($proposal['file_path']) ? 'required' : '' ?> style="display: none;"
                                                onchange="document.getElementById('filename-tahap2').value = this.files[0] ? this.files[0].name : ''">
                                            <button type="button" class="btn btn-dark"
                                                onclick="document.getElementById('file-tahap2').click()">Pilih
                                                File</button>
                                            <input type="text" id="filename-tahap2" readonly
                                                placeholder="<?= !empty($proposal['file_path']) ? htmlspecialchars(basename($proposal['file_path'])) : 'Pilih file pdf...' ?>" value="<?= !empty($proposal['file_path']) ? htmlspecialchars(basename($proposal['file_path'])) : '' ?>">
                                        </div>
                                    </div>
                                    <p id="err-2" style="color:#EA5455;font-size:12px;display:none;margin-bottom:8px;">
                                        &#9888; Harap isi judul dan pilih file proposal.</p>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-dark">Upload Proposal</button>
                                    </div>
                                </form>
                            </div>

                            <div id="completed-2" style="<?= $step2Done ? '' : 'display: none;' ?>">
                                <div class="lokasi-completed-wrap">
                                    <div class="lokasi-info-grid" style="grid-template-columns: 1fr;">
                                        <div class="info-card">
                                            <div class="info-card-title">
                                                <div class="info-card-icon-wrap"><svg viewBox="0 0 24 24" fill="#4A6D8C" width="16" height="16"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg></div>
                                                Detail Proposal
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">Judul Proposal</span>
                                                <span class="info-field-value" id="v-judul"><?= htmlspecialchars($proposal['judul'] ?? '-') ?></span>
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">File</span>
                                                <span class="info-field-value" id="v-file-proposal" style="display:flex; align-items:center;">
                                                    <?= htmlspecialchars(basename($proposal['file_path'] ?? '-')) ?>
                                                    <?php if (!empty($proposal['file_path'])): ?>
                                                    <a href="../../backend/helpers/serve_file.php?path=<?= urlencode($proposal['file_path']) ?>" target="_blank" style="margin-left: 10px; color: #10B981; text-decoration: none; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border: 1px solid #10B981; border-radius: 4px; background: #D1FAE5; transition: all 0.2s;">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                        Lihat File
                                                    </a>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($step2Status === 'menunggu' || $step2Status === 'ditolak'): ?>
                                    <div style="margin-top:14px;display:flex;gap:10px;">
                                        <?php if ($step2Status === 'menunggu'): ?>
                                        <button type="button" class="btn btn-dark" onclick="editTahap(2)" style="display:inline-flex;align-items:center;gap:6px;">&#9998; Edit Proposal</button>
                                        <?php endif; ?>

                                        <?php if ($step2Status === 'ditolak'): ?>
                                        <form method="POST" action="../../backend/actions/mahasiswa_hapus.php" style="display:inline;">
                                            <input type="hidden" name="type" value="proposal">
                                            <button type="submit" class="btn" style="background:#EA5455;color:white;">Ajukan Ulang</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tahap 3 -->
                        <div class="tahap-container <?= $step3Open ? 'active' : 'locked' ?>" id="step-3">
                            <div class="t-header <?= $step3Open ? 't-header-dark' : 't-header-white' ?> tahap-hdr-flex" id="header-3">
                                <div class="tahap-hdr-left">
                                    <div class="tahap-hdr-icon">
                                        <svg viewBox="0 0 24 24" fill="<?= $step3Open ? 'white' : '#4A6D8C' ?>" width="16" height="16"><path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"/></svg>
                                    </div>
                                    Tahap 3 : Berkas Anggota <?= $step3Done ? '&#10003;' : '' ?>
                                </div>
                                <?php if ($step3Done): ?>
                                <span class="tahap-status-pill pill-<?= $step3Status === 'disetujui' ? 'success' : ($step3Status === 'ditolak' ? 'danger' : 'warning') ?>">
                                    <?php if ($step3Status === 'disetujui'): ?>
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    Disetujui
                                    <?php elseif ($step3Status === 'ditolak'): ?>Ditolak
                                    <?php else: ?>Menunggu Verifikasi<?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="t-body t-form" id="form-3" style="<?= (($step3Open && !$step3Done) || $openFormBerkas) ? '' : 'display: none;' ?>">
                                <form class="profile-form" id="form-el-3" method="POST" action="../../backend/actions/mahasiswa_berkas.php" enctype="multipart/form-data" onsubmit="return isiCompleted(3)">

                                <?php foreach ($anggotaList as $idx => $anggota): 
                                    $initial = strtoupper(substr($anggota['nama'], 0, 1));
                                    $avatarClass = strtolower($anggota['peran']) === 'ketua' ? 'ketua' : 'anggota';
                                    $f_formulir = isset($berkasData[$anggota['id']]['formulir']);
                                    $f_ktm = isset($berkasData[$anggota['id']]['ktm']);
                                    $f_transkrip = isset($berkasData[$anggota['id']]['transkrip']);
                                    $f_foto = isset($berkasData[$anggota['id']]['pas_foto']);
                                    $f_cv = isset($berkasData[$anggota['id']]['cv']);
                                    
                                    $uploadedCount = ($f_formulir ? 1 : 0) + ($f_ktm ? 1 : 0) + ($f_transkrip ? 1 : 0) + ($f_foto ? 1 : 0) + ($f_cv ? 1 : 0);
                                ?>
                                <div class="member-acc-card <?= $idx === 0 ? 'open' : '' ?>" id="mcard-<?= $idx ?>">
                                    <div class="member-acc-header" onclick="toggleMemberAcc(<?= $idx ?>)">
                                        <div class="member-acc-left">
                                            <div class="acc-avatar <?= $avatarClass ?>"><?= $initial ?></div>
                                            <div class="acc-info">
                                                <h4><?= htmlspecialchars($anggota['nama']) ?></h4>
                                                <p><?= ucfirst(htmlspecialchars($anggota['peran'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="member-acc-right">
                                            <div class="acc-status" id="mstatus-<?= $idx ?>" <?= $uploadedCount === 5 ? 'style="color:#28C76F;"' : '' ?>><?= $uploadedCount ?>/5 berkas</div>
                                            <div class="acc-chevron">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="member-acc-body">
                                        <input type="hidden" name="anggota_id_<?= $idx ?>" value="<?= $anggota['id'] ?>">
                                        <div class="file-grid">
                                            <?php 
                                                $fileTypes = [
                                                    ['key' => 'formulir', 'label' => 'Formulir Pendaftaran', 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>', 'accept' => '.pdf'],
                                                    ['key' => 'ktm', 'label' => 'Scan KTM', 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>'],
                                                    ['key' => 'transkrip', 'label' => 'Transkrip Nilai', 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>'],
                                                    ['key' => 'pas_foto', 'label' => 'Pas Foto', 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>', 'accept' => 'image/*'],
                                                    ['key' => 'cv', 'label' => 'CV', 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>', 'accept' => '.pdf'],
                                                ];
                                                foreach ($fileTypes as $fidx => $f): 
                                                    $isUploaded = isset($berkasData[$anggota['id']][$f['key']]);
                                            ?>
                                            <div class="file-card">
                                                <div class="fc-icon"><?= $f['icon'] ?></div>
                                                <div class="fc-title"><?= $f['label'] ?></div>
                                                <?php 
                                                    $indivStatus = $berkasStatusMap[$anggota['id']][$f['key']] ?? 'menunggu';
                                                    $isRejected = ($isUploaded && $indivStatus === 'ditolak');
                                                ?>
                                                <div class="fc-status <?= $isUploaded ? 'uploaded' : '' ?>" 
                                                     id="fstatus-m<?= $idx ?>-<?= $fidx ?>" 
                                                     data-rejected="<?= $isRejected ? '1' : '0' ?>"
                                                     style="flex-direction: column; align-items: center; gap: 2px;">
                                                    <?php if($isUploaded): 
                                                        $fName = $berkasData[$anggota['id']][$f['key']];
                                                        $fNameDisp = strlen($fName) > 18 ? substr($fName, 0, 15) . '...' : $fName;
                                                        $indivStatus = $berkasStatusMap[$anggota['id']][$f['key']] ?? 'menunggu';
                                                    ?>
                                                    <div style="display: flex; align-items: center; gap: 4px;">
                                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> 
                                                        <span><?= htmlspecialchars($fNameDisp) ?></span>
                                                    </div>
                                                    
                                                    <?php if ($indivStatus === 'ditolak'): ?>
                                                    <div style="color:#EA5455; font-size:10px; font-weight:800; display:flex; align-items:center; gap:2px;">
                                                        <svg viewBox="0 0 24 24" width="10" height="10" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg> DITOLAK
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php else: ?>
                                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg> Belum diunggah
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <input type="file" name="berkas_<?= $idx ?>_<?= $f['key'] ?>" id="file-m<?= $idx ?>-<?= $fidx ?>" <?= isset($f['accept']) ? 'accept="'.$f['accept'].'"' : '' ?> style="display:none" onchange="handleFileGrid('m<?= $idx ?>-<?= $fidx ?>', this, <?= $idx ?>)">
                                                
                                                <button type="button" class="btn btn-dark fc-btn" onclick="document.getElementById('file-m<?= $idx ?>-<?= $fidx ?>').click()">
                                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/></svg>
                                                    <span id="fbtn-m<?= $idx ?>-<?= $fidx ?>"><?= $isUploaded ? 'Ganti File' : 'Upload' ?></span>
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <p id="err-3" style="color:#EA5455;font-size:12px;display:none;margin:12px 0 4px;">
                                    &#9888; Setiap anggota wajib mengupload minimal 1 berkas.</p>
                                <div class="form-actions" style="margin-top:16px;">
                                    <button type="submit" class="btn btn-dark"><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="vertical-align: middle; margin-right: 4px;"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/></svg> Simpan Semua Berkas</button>
                                </div>
                                </form>
                            </div>

                            <!-- Completed -->
                            <div id="completed-3" style="<?= ($step3Done && !$openFormBerkas) ? '' : 'display: none;' ?>">
                                <div class="lokasi-completed-wrap" style="padding: 16px 24px 20px;">
                                <?php if ($step3Done && $kelompokId): ?>
                                <?php
                                $berkasOrder = ['formulir', 'ktm', 'transkrip', 'pas_foto', 'cv'];
                                $berkasLabelsFull = [
                                    'formulir' => 'Formulir Pendaftaran',
                                    'ktm'      => 'Scan KTM',
                                    'transkrip'=> 'Transkrip Nilai',
                                    'pas_foto' => 'Pas Foto',
                                    'cv'       => 'CV',
                                ];
                                $bakColors = ['#FF6B6B','#4ECDC4','#45B7D1','#96CEB4','#A29BFE','#FECA57','#74B9FF','#FD79A8'];
                                ?>
                                <div class="bak-accordion">
                                <?php foreach ($anggotaList as $idx => $anggota):
                                    $aBerkas = $berkasData[$anggota['id']] ?? [];
                                    $aFilePaths = $berkasFilePaths[$anggota['id']] ?? [];
                                    $uploadedCount = count($aBerkas);
                                    $totalBerkas = 5;
                                    $pct = ($uploadedCount / $totalBerkas) * 100;
                                    $cColor = $uploadedCount === $totalBerkas ? '#28C76F' : ($uploadedCount > 0 ? '#FF9F43' : '#EA5455');
                                    $avColor = $bakColors[$idx % count($bakColors)];
                                    $isFirst = ($idx === 0);
                                ?>
                                <div class="bak-item">
                                    <div class="bak-hdr" onclick="toggleBak(<?= $anggota['id'] ?>)">
                                        <div class="bak-avatar" style="background:<?= $avColor ?>">
                                            <?= strtoupper(mb_substr($anggota['nama'], 0, 1)) ?>
                                        </div>
                                        <div class="bak-meta">
                                            <div class="bak-name-row">
                                                <span class="bak-name"><?= htmlspecialchars($anggota['nama']) ?></span>
                                                <span class="bak-role-badge"><?= ucfirst(htmlspecialchars($anggota['peran'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="bak-right">
                                            <div class="bak-progress-info">
                                                <span class="bak-count-text" style="color:<?= $cColor ?>"><?= $uploadedCount ?>/<?= $totalBerkas ?> berkas</span>
                                                <div class="bak-progress-bar"><div class="bak-progress-fill" style="width:<?= $pct ?>%;background:<?= $cColor ?>"></div></div>
                                            </div>
                                            <div class="bak-chevron" id="bak-chevron-<?= $anggota['id'] ?>">
                                                <svg viewBox="0 0 24 24" fill="#7B8FA1" width="18" height="18"><path id="bak-chvpath-<?= $anggota['id'] ?>" d="<?= $isFirst ? 'M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z' : 'M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z' ?>"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bak-body" id="bak-body-<?= $anggota['id'] ?>" style="<?= $isFirst ? '' : 'display:none;' ?>">
                                        <div class="bak-body-inner">
                                            <p class="bak-body-label">Berkas yang Diperlukan (<?= $totalBerkas ?>)</p>
                                            <div class="bak-grid">
                                            <?php foreach ($berkasOrder as $num => $jenis):
                                                $hasFile = isset($aBerkas[$jenis]);
                                                $filePath = $aFilePaths[$jenis] ?? '';
                                                $fileUrl  = '../../backend/helpers/serve_file.php?path=' . urlencode($filePath);
                                            ?>
                                                <div class="bak-card">
                                                    <div class="bak-card-icon">
                                                        <svg viewBox="0 0 24 24" fill="#7C83FD" width="22" height="22"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                                    </div>
                                                    <div class="bak-card-name"><?= ($num+1) . '. ' . $berkasLabelsFull[$jenis] ?></div>
                                                    <?php if ($hasFile): 
                                                        $fStatus = $berkasStatusMap[$anggota['id']][$jenis] ?? 'menunggu';
                                                        $statusColor = ($fStatus === 'disetujui') ? '#28C76F' : (($fStatus === 'ditolak') ? '#EA5455' : '#FF9F43');
                                                        $statusBg = ($fStatus === 'disetujui') ? '#D1FAE5' : (($fStatus === 'ditolak') ? '#FEE2E2' : '#FEF3C7');
                                                        $statusIconPath = ($fStatus === 'disetujui') 
                                                            ? 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z' 
                                                            : (($fStatus === 'ditolak') ? 'M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z' : 'M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z');
                                                    ?>
                                                    <div class="bak-card-status" style="color:<?= $statusColor ?>; border:1px solid <?= $statusColor ?>; background:<?= $statusBg ?>; display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600; margin-bottom:10px;">
                                                        <svg viewBox="0 0 24 24" fill="<?= $statusColor ?>" width="13" height="13"><path d="<?= $statusIconPath ?>"/></svg>
                                                        <?= ucfirst($fStatus) ?>
                                                    </div>
                                                    <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" class="bak-card-action bak-action-view">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                        Lihat File
                                                    </a>
                                                    <?php else: ?>
                                                    <div class="bak-card-status bak-status-pending">
                                                        <svg viewBox="0 0 24 24" fill="#A0B2C0" width="13" height="13"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
                                                        Belum diunggah
                                                    </div>
                                                    <span class="bak-card-action bak-action-upload">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/></svg>
                                                        Upload
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                </div><!-- /bak-accordion -->

                                <?php if ($berkasUploadFormatted): ?>
                                <div class="bak-footer">
                                    <svg viewBox="0 0 24 24" fill="#7B8FA1" width="15" height="15"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                                    Tanggal Upload Terakhir: <strong><?= $berkasUploadFormatted ?></strong>
                                </div>
                                <?php endif; ?>

                                <?php if ($berkasStatus === 'menunggu' || $berkasStatus === 'ditolak'): ?>
                                <div style="margin-top:14px;display:flex;gap:10px;">
                                    <?php if ($berkasStatus === 'menunggu'): ?>
                                    <button type="button" class="btn btn-dark" onclick="editBerkas()" style="display:inline-flex;align-items:center;gap:6px;">&#9998; Edit Berkas</button>
                                    <?php endif; ?>

                                    <?php if ($berkasStatus === 'ditolak'): ?>
                                    <form method="POST" action="../../backend/actions/mahasiswa_hapus.php" style="display:inline;">
                                        <input type="hidden" name="type" value="berkas">
                                        <button type="submit" class="btn" style="background:#EA5455;color:white;">Ajukan Ulang</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                                </div><!-- /lokasi-completed-wrap -->
                            </div><!-- /completed-3 -->
                        </div><!-- /step-3 -->

                        <!-- Tahap 4 -->
                        <div class="tahap-container <?= $step4Open ? 'active' : 'locked' ?>" id="step-4">
                            <div class="t-header <?= $step4Open ? 't-header-dark' : 't-header-white' ?> tahap-hdr-flex" id="header-4">
                                <div class="tahap-hdr-left">
                                    <div class="tahap-hdr-icon">
                                        <svg viewBox="0 0 24 24" fill="<?= $step4Open ? 'white' : '#4A6D8C' ?>" width="16" height="16"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.85.5 13.5.5c-1.36 0-2.5 1.14-2.5 2.5H6c-1.1 0-2 .9-2 2v13h18V8c0-1.1-.9-2-2-2zm-8 12H6v-2h6v2zm2-6H6v-2h8v2zm2-4H6V8h10v2z"/></svg>
                                    </div>
                                    Tahap 4 : Bukti Diterima <?= $step4Done ? '&#10003;' : '' ?>
                                </div>
                                <?php if ($step4Done): ?>
                                <span class="tahap-status-pill pill-<?= $step4Status === 'disetujui' ? 'success' : ($step4Status === 'ditolak' ? 'danger' : 'warning') ?>">
                                    <?php if ($step4Status === 'disetujui'): ?>
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    Disetujui
                                    <?php elseif ($step4Status === 'ditolak'): ?>Ditolak
                                    <?php else: ?>Menunggu Verifikasi<?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="t-body t-form" id="form-4" style="<?= ($step4Open && !$step4Done) ? '' : 'display: none;' ?>">
                                <form class="profile-form" id="form-el-4" method="POST" action="../../backend/actions/mahasiswa_bukti.php" enctype="multipart/form-data" onsubmit="return isiCompleted(4)">
                                    <div class="form-group">
                                        <label>Tempat Diterima <span style="color:#EA5455">*</span></label>
                                        <input type="text" id="inp-tempat" name="tempat_diterima"
                                            placeholder="Nama perusahaan / instansi yang menerima" value="<?= htmlspecialchars($bukti['tempat_diterima'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Surat Penerimaan (PDF) <span style="color:#EA5455">*</span></label>
                                        <div class="file-input-row">
                                            <input type="file" name="surat_penerimaan" id="file-tahap4" accept=".pdf"
                                                style="display: none;" <?= empty($bukti['file_path']) ? 'required' : '' ?>
                                                onchange="document.getElementById('filename-tahap4').value = this.files[0] ? this.files[0].name : ''">
                                            <button type="button" class="btn btn-dark"
                                                onclick="document.getElementById('file-tahap4').click()">Pilih
                                                File</button>
                                            <input type="text" id="filename-tahap4" readonly
                                                placeholder="<?= !empty($bukti['file_path']) ? htmlspecialchars(basename($bukti['file_path'])) : 'Pilih file surat penerimaan...' ?>" value="<?= !empty($bukti['file_path']) ? htmlspecialchars(basename($bukti['file_path'])) : '' ?>">
                                        </div>
                                    </div>
                                    <p id="err-4" style="color:#EA5455;font-size:12px;display:none;margin-bottom:8px;">
                                        &#9888; Harap isi tempat diterima dan upload surat penerimaan.</p>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-dark">Upload Bukti Diterima</button>
                                    </div>
                                </form>
                            </div>

                            <div id="completed-4" style="<?= $step4Done ? '' : 'display: none;' ?>">
                                <div class="lokasi-completed-wrap">
                                    <div class="lokasi-info-grid" style="grid-template-columns: 1fr;">
                                        <div class="info-card">
                                            <div class="info-card-title">
                                                <div class="info-card-icon-wrap"><svg viewBox="0 0 24 24" fill="#4A6D8C" width="16" height="16"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.85.5 13.5.5c-1.36 0-2.5 1.14-2.5 2.5H6c-1.1 0-2 .9-2 2v13h18V8c0-1.1-.9-2-2-2zm-8 12H6v-2h6v2zm2-6H6v-2h8v2zm2-4H6V8h10v2z"/></svg></div>
                                                Detail Bukti Diterima
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">Tempat Diterima</span>
                                                <span class="info-field-value" id="v-tempat"><?= htmlspecialchars($bukti['tempat_diterima'] ?? '-') ?></span>
                                            </div>
                                            <div class="info-field-item">
                                                <span class="info-field-label">File Surat</span>
                                                <span class="info-field-value" id="v-file-bukti" style="display:flex; align-items:center;">
                                                    <?= htmlspecialchars(basename($bukti['file_path'] ?? '-')) ?>
                                                    <?php if (!empty($bukti['file_path'])): ?>
                                                    <a href="../../backend/helpers/serve_file.php?path=<?= urlencode($bukti['file_path']) ?>" target="_blank" style="margin-left: 10px; color: #10B981; text-decoration: none; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border: 1px solid #10B981; border-radius: 4px; background: #D1FAE5; transition: all 0.2s;">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                        Lihat File
                                                    </a>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($step4Status === 'menunggu' || $step4Status === 'ditolak'): ?>
                                    <div style="margin-top:14px;display:flex;gap:10px;">
                                        <?php if ($step4Status === 'menunggu'): ?>
                                        <button type="button" class="btn btn-dark" onclick="editTahap(4)" style="display:inline-flex;align-items:center;gap:6px;">&#9998; Edit Bukti</button>
                                        <?php endif; ?>

                                        <?php if ($step4Status === 'ditolak'): ?>
                                        <form method="POST" action="../../backend/actions/mahasiswa_hapus.php" style="display:inline;">
                                            <input type="hidden" name="type" value="bukti">
                                            <button type="submit" class="btn" style="background:#EA5455;color:white;">Ajukan Ulang</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tahap 5 -->
                        <div class="tahap-container <?= $step5Open ? 'active' : 'locked' ?>" id="step-5">
                            <div class="t-header <?= $step5Open ? 't-header-dark' : 't-header-white' ?> tahap-hdr-flex" id="header-5">
                                <div class="tahap-hdr-left">
                                    <div class="tahap-hdr-icon">
                                        <svg viewBox="0 0 24 24" fill="<?= $step5Open ? 'white' : '#4A6D8C' ?>" width="16" height="16"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    </div>
                                    Tahap 5 : Plotting Dosen <?= $step5Done ? '&#10003;' : '' ?>
                                </div>
                                <?php if ($step5Done): ?>
                                <span class="tahap-status-pill pill-success">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    Selesai
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="t-body t-form" id="form-5" style="<?= ($step5Open) ? '' : 'display: none;' ?>">
                                <?php if (!$step5Done): ?>
                                <div class="plotting-status-card">
                                    <div class="psc-header">
                                        <div class="psc-icon-wrapper pending">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                                        </div>
                                        <div class="psc-title-area">
                                            <h4 class="psc-title">Menunggu Plotting Koordinator</h4>
                                            <p class="psc-subtitle">Estimasi waktu: 1-2 hari kerja</p>
                                        </div>
                                        <div class="psc-badge pending">Menunggu</div>
                                    </div>
                                    <div class="psc-body">
                                        <p>Kelompok Anda sedang menunggu penentuan dosen pembimbing oleh koordinator bidang.</p>
                                    </div>
                                </div>
                                <?php else: 
                                    $tglPlotting = '-';
                                    if (!empty($plotting['created_at'])) {
                                        $dt = new DateTime($plotting['created_at']);
                                        $bulanNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                        $tglPlotting = $dt->format('j') . ' ' . $bulanNames[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
                                    }
                                    $namaDosen = $plotting['dosen_pembimbing'] ?? 'Belum ditentukan';
                                    $inisialDosen = strtoupper(substr($namaDosen, 0, 1));
                                    $perusahaanName = $lokasi['perusahaan'] ?? '-';
                                ?>
                                <div class="plotting-success-wrapper">
                                    <div class="plotting-cards-grid">
                                        <!-- Card 1: Dosen -->
                                        <div class="p-card">
                                            <div class="p-card-header">
                                                <div class="p-card-icon user-icon">
                                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                                </div>
                                                <span class="p-card-title">Dosen Pembimbing</span>
                                            </div>
                                            <div class="p-card-body">
                                                <div class="dosen-profile">
                                                    <div class="dosen-avatar"><?= $inisialDosen ?></div>
                                                    <div class="dosen-info">
                                                        <h5><?= htmlspecialchars($namaDosen) ?></h5>
                                                        <p>Dosen Pembimbing Magang</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card 2: Info Plotting -->
                                        <div class="p-card">
                                            <div class="p-card-header">
                                                <div class="p-card-icon loc-icon">
                                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                                </div>
                                                <span class="p-card-title">Info Plotting</span>
                                            </div>
                                            <div class="p-card-body">
                                                <div class="p-info-row">
                                                    <span class="p-info-label">Lokasi Magang</span>
                                                    <span class="p-info-value"><?= htmlspecialchars($perusahaanName) ?></span>
                                                </div>
                                                <div class="p-info-row" style="margin-top: 14px;">
                                                    <span class="p-info-label">Tanggal Plotting</span>
                                                    <span class="p-info-value"><?= $tglPlotting ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="plotting-success-alert">
                                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                        Proses pendaftaran magang Anda telah selesai. Selamat!
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

            </div>

        </main>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Update map when address changes
        (function() {
            const mapContainer = document.getElementById('map-container');
            if (!mapContainer) return;

            // Inisialisasi Peta (Leaflet dengan style Google Maps)
            const map = L.map('map-container').setView([-2.5489, 118.0149], 5); // Default Indonesia
            L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '&copy; Google Maps',
                maxZoom: 20
            }).addTo(map);

            // Fix map rendering issue when inside a container that might have sizing changes
            setTimeout(() => { map.invalidateSize(); }, 500);

            let marker = null;
            const alamatInput = document.getElementById('inp-alamat');
            const searchInput = document.getElementById('map-search');
            const btnSearch = document.getElementById('btn-search-map');

            // Fungsi untuk update marker
            function setMarker(lat, lng, address) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(map);
                }
                map.setView([lat, lng], 16);
                if (address && alamatInput) {
                    alamatInput.value = address;
                }
            }

            // Reverse Geocoding saat peta diklik
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                // Ambil alamat dari Nominatim
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name;
                        setMarker(lat, lng, address);
                        if (searchInput) searchInput.value = address;
                    })
                    .catch(err => console.error("Geocoding error:", err));
            });

            // Pencarian Geocoding
            function searchMap() {
                const query = searchInput.value;
                if (!query) return;

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = data[0].lat;
                            const lon = data[0].lon;
                            const address = data[0].display_name;
                            setMarker(lat, lon, address);
                            if (alamatInput) alamatInput.value = address;
                        } else {
                            alert("Lokasi tidak ditemukan.");
                        }
                    })
                    .catch(err => console.error("Search error:", err));
            }

            if (btnSearch) btnSearch.addEventListener('click', searchMap);
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchMap();
                    }
                });
            }

            // Listen if user manually types in the main alamat field
            if (alamatInput) {
                alamatInput.addEventListener('change', function() {
                    const val = this.value.trim();
                    if(val !== '') {
                        searchInput.value = val;
                        searchMap();
                    }
                });
            }
        })();

        // ===== Format tanggal hari ini =====
        function hariIni() {
            return new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        // Validasi & isi data completed per tahap
        function isiCompleted(step) {
            if (step === 1) {
                const p = v => document.getElementById(v)?.value.trim();
                const perusahaan = p('inp-perusahaan'), pimpinan = p('inp-pimpinan'),
                    bidang = p('inp-bidang'), telepon = p('inp-telepon'), alamat = p('inp-alamat');
                if (!perusahaan || !pimpinan || !bidang || !telepon || !alamat) {
                    document.getElementById('err-1').style.display = 'block';
                    return false;
                }
                document.getElementById('err-1').style.display = 'none';
                document.getElementById('v-perusahaan').textContent = perusahaan;
                document.getElementById('v-pimpinan').textContent = pimpinan;
                document.getElementById('v-bidang').textContent = bidang;
                document.getElementById('v-telepon').textContent = telepon;
                document.getElementById('v-alamat').textContent = alamat;


                return true;
            }
            if (step === 2) {
                const judul = document.getElementById('inp-judul')?.value.trim();
                const file = document.getElementById('filename-tahap2')?.value.trim();
                if (!judul || !file) {
                    document.getElementById('err-2').style.display = 'block';
                    return false;
                }
                document.getElementById('err-2').style.display = 'none';
                document.getElementById('v-judul').textContent = judul;
                document.getElementById('v-file-proposal').textContent = file;
                return true;
            }
            if (step === 3) {
                const memberCount = document.querySelectorAll('.member-acc-card').length;
                let allHaveFile = true;
                let hasRejected = false;

                for (let mi = 0; mi < memberCount; mi++) {
                    let hasAnyUploaded = false;
                    for (let fi = 0; fi < 5; fi++) {
                        const statusEl = document.getElementById(`fstatus-m${mi}-${fi}`);
                        if (statusEl) {
                            if (statusEl.dataset.rejected === '1') hasRejected = true;
                            if (statusEl.classList.contains('uploaded')) hasAnyUploaded = true;
                        }
                    }
                    if (!hasAnyUploaded) allHaveFile = false;
                }

                const errEl = document.getElementById('err-3');
                if (!allHaveFile) {
                    errEl.textContent = '⚠ Setiap anggota wajib mengupload minimal 1 berkas.';
                    errEl.style.display = 'block';
                    return false;
                }
                if (hasRejected) {
                    errEl.textContent = '⚠ Harap ganti berkas yang ditolak dengan berkas baru.';
                    errEl.style.display = 'block';
                    return false;
                }
                errEl.style.display = 'none';
                return true;
            }
            if (step === 4) {
                const tempat = document.getElementById('inp-tempat')?.value.trim();
                const file = document.getElementById('filename-tahap4')?.value.trim();
                if (!tempat || !file) {
                    document.getElementById('err-4').style.display = 'block';
                    return false;
                }
                document.getElementById('err-4').style.display = 'none';
                document.getElementById('v-tempat').textContent = tempat;
                document.getElementById('v-file-bukti').textContent = file;
                document.getElementById('v-tgl-bukti').textContent = hariIni();
                return true;
            }
            return true;
        }

        /* ===== Tahap 3: Accordion & Status helpers ===== */
        function editBerkas() {
            editTahap(3);
        }

        function editTahap(step) {
            document.getElementById('completed-' + step).style.display = 'none';
            document.getElementById('form-' + step).style.display = 'block';
            document.getElementById('step-' + step).scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function toggleMemberAcc(idx) {
            document.getElementById('mcard-' + idx).classList.toggle('open');
        }

        function handleFileGrid(key, input, mi) {
            const isFileSelected = input.files && input.files.length > 0;
            const statusEl = document.getElementById('fstatus-' + key);
            const btnEl = document.getElementById('fbtn-' + key);
            
            if (isFileSelected) {
                let fileName = input.files[0].name;
                if (fileName.length > 18) fileName = fileName.substring(0, 15) + '...';
                statusEl.className = 'fc-status uploaded';
                statusEl.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> ' + fileName;
                btnEl.textContent = 'Ganti File';
                statusEl.dataset.rejected = '0';
            } else {
                statusEl.className = 'fc-status';
                statusEl.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg> Belum diunggah';
                btnEl.textContent = 'Upload';
                statusEl.dataset.rejected = '0';
            }
            updateMemberStatusGrid(mi);
        }

        function updateMemberStatusGrid(mi) {
            let count = 0;
            for (let fidx = 0; fidx < 5; fidx++) {
                const statusEl = document.getElementById('fstatus-m' + mi + '-' + fidx);
                if (statusEl && statusEl.classList.contains('uploaded')) {
                    count++;
                }
            }
            const badge = document.getElementById('mstatus-' + mi);
            if (badge) {
                badge.textContent = count + '/5 berkas';
                if (count === 5) {
                    badge.style.color = '#28C76F';
                } else {
                    badge.style.color = '#FF6B6B';
                }
            }
        }

        function nextStep(current) {
            // Validasi & isi data completed dulu
            if (!isiCompleted(current)) return;

            const currentHeader = document.getElementById(`header-${current}`);
            const currentForm = document.getElementById(`form-${current}`);
            const currentCompleted = document.getElementById(`completed-${current}`);

            // Keep header dark (matches Tahap 1 style - header stays dark when done)
            // No class change needed since t-header-dark is correct for done state

            // Sembunyikan form, tampilkan ringkasan
            if (currentForm) currentForm.style.display = 'none';
            if (currentCompleted) currentCompleted.style.display = 'block';

            // Buka tahap berikutnya
            const next = current + 1;
            const nextContainer = document.getElementById(`step-${next}`);
            if (nextContainer) {
                nextContainer.classList.remove('locked');
                const nextHeader = document.getElementById(`header-${next}`);
                const nextForm = document.getElementById(`form-${next}`);
                if (nextHeader) { nextHeader.classList.remove('t-header-white'); nextHeader.classList.add('t-header-dark'); }
                if (nextForm) nextForm.style.display = 'block';
                nextContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        /* ===== Tahap 3: Berkas Accordion Toggle ===== */
        function toggleBak(id) {
            const body = document.getElementById('bak-body-' + id);
            const chvPath = document.getElementById('bak-chvpath-' + id);
            if (!body) return;
            const isOpen = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            if (chvPath) {
                // Up chevron when open, down when closed
                chvPath.setAttribute('d', isOpen
                    ? 'M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z'
                    : 'M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z'
                );
            }
        }

    </script>
<?php require __DIR__ . '/footer.php'; ?>