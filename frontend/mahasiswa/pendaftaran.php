<?php
require_once __DIR__ . '/functions.php';
requireMahasiswaLogin();
$pageTitle = 'Pendaftaran Magang - SIMM';
$activePage = 'pendaftaran';
require __DIR__ . '/header.php';

// Load existing data
$mysqli = require __DIR__ . '/../../backend/database.php';
$userId = (int) $_SESSION['user_id'];
$kelompokId = null;
$stmt = $mysqli->prepare('SELECT id FROM kelompok WHERE ketua_user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if ($r) $kelompokId = (int) $r['id'];

// Load anggota for berkas tab
$anggotaList = [];
$lokasi = null;
$proposal = null;
$bukti = null;
$plotting = null;

if ($kelompokId) {
    $stmt = $mysqli->prepare('SELECT ak.id, m.nama, ak.peran FROM anggota_kelompok ak JOIN mahasiswa m ON ak.mahasiswa_id = m.id WHERE ak.kelompok_id = ? ORDER BY ak.peran ASC, ak.created_at ASC');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $anggotaList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $mysqli->prepare('SELECT p.nama AS perusahaan, p.nama_pimpinan, p.bidang, p.telepon, p.alamat, p.latitude, p.longitude, pl.status_verifikasi, pl.updated_at, pl.catatan FROM pendaftaran_lokasi pl JOIN perusahaan p ON pl.perusahaan_id = p.id WHERE pl.kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $lokasi = $stmt->get_result()->fetch_assoc();

    $stmt = $mysqli->prepare('SELECT * FROM proposal WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $proposal = $stmt->get_result()->fetch_assoc();

    $stmt = $mysqli->prepare('SELECT bd.id, p.nama AS tempat_diterima, bd.file_path, bd.status_verifikasi FROM bukti_diterima bd JOIN perusahaan p ON bd.perusahaan_id = p.id WHERE bd.kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $bukti = $stmt->get_result()->fetch_assoc();

    $stmt = $mysqli->prepare('SELECT pl.id, d.nama AS dosen_pembimbing FROM plotting pl JOIN dosen d ON pl.dosen_id = d.id WHERE pl.kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $plotting = $stmt->get_result()->fetch_assoc();
}

$berkasData = [];
$berkasFilePaths = [];
$berkasStatusMap = [];
$berkasUploadDate = null;
if ($kelompokId) {
    $bStmt = $mysqli->prepare('SELECT ba.anggota_id, ba.jenis_berkas, ba.file_path, ba.status_verifikasi, ba.created_at FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
    $bStmt->bind_param('i', $kelompokId);
    $bStmt->execute();
    foreach ($bStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $bRow) {
        $berkasData[$bRow['anggota_id']][$bRow['jenis_berkas']] = basename($bRow['file_path']);
        $berkasFilePaths[$bRow['anggota_id']][$bRow['jenis_berkas']] = $bRow['file_path'];
        $berkasStatusMap[$bRow['anggota_id']][$bRow['jenis_berkas']] = $bRow['status_verifikasi'];
        // Track latest upload date
        if (!$berkasUploadDate || $bRow['created_at'] > $berkasUploadDate) {
            $berkasUploadDate = $bRow['created_at'];
        }
    }
}

$berkasStatus = 'belum';
if ($kelompokId) {
    $stmt = $mysqli->prepare('SELECT COUNT(*) AS total, SUM(ba.status_verifikasi = "disetujui") AS approved, SUM(ba.status_verifikasi = "ditolak") AS rejected FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r && $r['total'] > 0) {
        if ($r['rejected'] > 0) $berkasStatus = 'ditolak';
        elseif ($r['approved'] == $r['total']) $berkasStatus = 'disetujui';
        else $berkasStatus = 'menunggu';
    }
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

$step1Done = ($lokasi !== null);
$step1Status = $lokasi['status_verifikasi'] ?? 'belum';

$step2Open = ($step1Status === 'disetujui');
$step2Done = ($proposal !== null);
$step2Status = $proposal['status_verifikasi'] ?? 'belum';

$step3Open = ($step2Status === 'disetujui');
$step3Done = ($berkasStatus !== 'belum');
$step3Status = $berkasStatus;
$berkasEditable = ($step3Done && ($berkasStatus === 'menunggu' || $berkasStatus === 'ditolak'));

$step4Open = ($step3Status === 'disetujui');
$step4Done = ($bukti !== null);
$step4Status = $bukti['status_verifikasi'] ?? 'belum';

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
                                <form class="profile-form" id="form-el-1" method="POST" action="../../backend/mahasiswa/pendaftaran.php">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Perusahaan <span style="color:#EA5455">*</span></label>
                                            <input type="text" name="perusahaan" id="inp-perusahaan" placeholder="Nama perusahaan" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Nama Pimpinan <span style="color:#EA5455">*</span></label>
                                            <input type="text" id="inp-pimpinan" name="nama_pimpinan"
                                                placeholder="Nama pimpinan / contact person" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Bidang <span style="color:#EA5455">*</span></label>
                                            <input type="text" id="inp-bidang" name="bidang" placeholder="Bidang perusahaan" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Telepon <span style="color:#EA5455">*</span></label>
                                            <input type="text" id="inp-telepon" name="telepon" placeholder="Nomor telepon" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Alamat <span style="color:#EA5455">*</span></label>
                                            <input type="text" name="alamat" id="inp-alamat" placeholder="Alamat lengkap perusahaan" required>
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
                                    <?php if ($step1Status === 'ditolak'): ?>
                                    <div style="margin-top:16px;">
                                        <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php">
                                            <input type="hidden" name="type" value="lokasi">
                                            <button type="submit" class="btn btn-dark">Ajukan Ulang</button>
                                        </form>
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
                                <form class="profile-form" id="form-el-2" method="POST" action="../../backend/mahasiswa/proposal.php" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Judul Proposal <span style="color:#EA5455">*</span></label>
                                        <input type="text" name="judul" id="inp-judul" placeholder="Masukkan judul proposal" required>
                                    </div>
                                    <div class="form-group">
                                        <label>File Proposal (PDF) <span style="color:#EA5455">*</span></label>
                                        <div class="file-input-row">
                                            <input type="file" name="proposal" id="file-tahap2" accept=".pdf" required style="display: none;"
                                                onchange="document.getElementById('filename-tahap2').value = this.files[0] ? this.files[0].name : ''">
                                            <button type="button" class="btn btn-dark"
                                                onclick="document.getElementById('file-tahap2').click()">Pilih
                                                File</button>
                                            <input type="text" id="filename-tahap2" readonly
                                                placeholder="Pilih file pdf...">
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
                                                    <a href="../../<?= htmlspecialchars($proposal['file_path']) ?>" target="_blank" style="margin-left: 10px; color: #10B981; text-decoration: none; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border: 1px solid #10B981; border-radius: 4px; background: #D1FAE5; transition: all 0.2s;">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                        Lihat File
                                                    </a>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($step2Status === 'ditolak'): ?>
                                    <div style="margin-top:14px;">
                                        <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php">
                                            <input type="hidden" name="type" value="proposal">
                                            <button type="submit" class="btn btn-dark">Ajukan Ulang</button>
                                        </form>
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

                            <div class="t-body t-form" id="form-3" style="<?= ($step3Open && !$step3Done) ? '' : 'display: none;' ?>">
                                <form class="profile-form" id="form-el-3" method="POST" action="../../backend/mahasiswa/berkas.php" enctype="multipart/form-data">

                                <!-- Tab Member -->
                                <div class="member-tabs" id="member-tabs">
                                    <?php foreach ($anggotaList as $idx => $anggota): ?>
                                    <button type="button" class="member-tab <?= $idx === 0 ? 'active' : '' ?>" id="mtab-<?= $idx ?>"
                                        onclick="switchMemberTab(<?= $idx ?>)">&#128100; <?= htmlspecialchars(explode(' ', $anggota['nama'])[0]) ?> <span class="mtab-status"
                                            id="mstatus-<?= $idx ?>"></span></button>
                                    <?php endforeach; ?>
                                </div>

                                <?php foreach ($anggotaList as $idx => $anggota): ?>
                                <!-- Panel Anggota <?= $idx ?>: <?= htmlspecialchars($anggota['nama']) ?> -->
                                <div class="member-panel <?= $idx === 0 ? 'active' : '' ?>" id="mpanel-<?= $idx ?>">
                                    <div class="member-panel-title"><?= htmlspecialchars($anggota['nama']) ?> &mdash; <em><?= ucfirst(htmlspecialchars($anggota['peran'])) ?></em></div>
                                    <div class="profile-form">
                                        <input type="hidden" name="anggota_id_<?= $idx ?>" value="<?= $anggota['id'] ?>">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Formulir Pendaftaran</label>
                                                <div class="file-input-row">
                                                    <input type="file" name="berkas_<?= $idx ?>_formulir" id="file-m<?= $idx ?>-1" style="display:none"
                                                        onchange="setFile('m<?= $idx ?>-1', this); updateMemberStatus(<?= $idx ?>)">
                                                    <button type="button" class="btn btn-dark" style="padding: 10px 15px;"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-1').click()">Pilih File</button>
                                                    <input type="text" id="fn-m<?= $idx ?>-1" readonly
                                                        value="<?= htmlspecialchars($berkasData[$anggota['id']]['formulir'] ?? '') ?>"
                                                        placeholder="<?= isset($berkasData[$anggota['id']]['formulir']) ? 'Ganti file...' : 'Klik untuk pilih file' ?>"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-1').click()"
                                                        style="cursor:pointer; flex: 1;">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Scan KTM</label>
                                                <div class="file-input-row">
                                                    <input type="file" name="berkas_<?= $idx ?>_ktm" id="file-m<?= $idx ?>-2" style="display:none"
                                                        onchange="setFile('m<?= $idx ?>-2', this); updateMemberStatus(<?= $idx ?>)">
                                                    <button type="button" class="btn btn-dark" style="padding: 10px 15px;"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-2').click()">Pilih File</button>
                                                    <input type="text" id="fn-m<?= $idx ?>-2" readonly
                                                        value="<?= htmlspecialchars($berkasData[$anggota['id']]['ktm'] ?? '') ?>"
                                                        placeholder="<?= isset($berkasData[$anggota['id']]['ktm']) ? 'Ganti file...' : 'Klik untuk pilih file' ?>"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-2').click()"
                                                        style="cursor:pointer; flex: 1;">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Transkrip Nilai</label>
                                                <div class="file-input-row">
                                                    <input type="file" name="berkas_<?= $idx ?>_transkrip" id="file-m<?= $idx ?>-3" style="display:none"
                                                        onchange="setFile('m<?= $idx ?>-3', this); updateMemberStatus(<?= $idx ?>)">
                                                    <button type="button" class="btn btn-dark" style="padding: 10px 15px;"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-3').click()">Pilih File</button>
                                                    <input type="text" id="fn-m<?= $idx ?>-3" readonly
                                                        value="<?= htmlspecialchars($berkasData[$anggota['id']]['transkrip'] ?? '') ?>"
                                                        placeholder="<?= isset($berkasData[$anggota['id']]['transkrip']) ? 'Ganti file...' : 'Klik untuk pilih file' ?>"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-3').click()"
                                                        style="cursor:pointer; flex: 1;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Pas Foto</label>
                                                <div class="file-input-row">
                                                    <input type="file" name="berkas_<?= $idx ?>_pas_foto" id="file-m<?= $idx ?>-4" accept="image/*" style="display:none"
                                                        onchange="setFile('m<?= $idx ?>-4', this); updateMemberStatus(<?= $idx ?>)">
                                                    <button type="button" class="btn btn-dark" style="padding: 10px 15px;"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-4').click()">Pilih File</button>
                                                    <input type="text" id="fn-m<?= $idx ?>-4" readonly
                                                        value="<?= htmlspecialchars($berkasData[$anggota['id']]['pas_foto'] ?? '') ?>"
                                                        placeholder="<?= isset($berkasData[$anggota['id']]['pas_foto']) ? 'Ganti file...' : 'Klik untuk pilih file' ?>"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-4').click()"
                                                        style="cursor:pointer; flex: 1;">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>CV</label>
                                                <div class="file-input-row">
                                                    <input type="file" name="berkas_<?= $idx ?>_cv" id="file-m<?= $idx ?>-5" style="display:none"
                                                        onchange="setFile('m<?= $idx ?>-5', this); updateMemberStatus(<?= $idx ?>)">
                                                    <button type="button" class="btn btn-dark" style="padding: 10px 15px;"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-5').click()">Pilih File</button>
                                                    <input type="text" id="fn-m<?= $idx ?>-5" readonly
                                                        value="<?= htmlspecialchars($berkasData[$anggota['id']]['cv'] ?? '') ?>"
                                                        placeholder="<?= isset($berkasData[$anggota['id']]['cv']) ? 'Ganti file...' : 'Klik untuk pilih file' ?>"
                                                        onclick="document.getElementById('file-m<?= $idx ?>-5').click()"
                                                        style="cursor:pointer; flex: 1;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <p id="err-3" style="color:#EA5455;font-size:12px;display:none;margin:12px 0 4px;">
                                    &#9888; Setiap anggota wajib mengupload minimal 1 berkas.</p>
                                <div class="form-actions" style="margin-top:16px;">
                                    <button type="submit" class="btn btn-dark">Simpan & Lanjut</button>
                                </div>
                                </form>
                            </div>

                            <!-- Completed -->
                            <div id="completed-3" style="<?= $step3Done ? '' : 'display: none;' ?>">
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
                                                $fileUrl  = '../../' . $filePath;
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
                                    <button type="button" class="btn btn-dark" onclick="editBerkas()" style="display:inline-flex;align-items:center;gap:6px;">&#9998; Edit Berkas</button>
                                    <?php if ($berkasStatus === 'ditolak'): ?>
                                    <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php" style="display:inline;">
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
                                <form class="profile-form" id="form-el-4" method="POST" action="../../backend/mahasiswa/bukti_diterima.php" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Tempat Diterima <span style="color:#EA5455">*</span></label>
                                        <input type="text" id="inp-tempat" name="tempat_diterima"
                                            placeholder="Nama perusahaan / instansi yang menerima" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Surat Penerimaan (PDF) <span style="color:#EA5455">*</span></label>
                                        <div class="file-input-row">
                                            <input type="file" name="surat_penerimaan" id="file-tahap4" accept=".pdf,.jpg,.png"
                                                style="display: none;" required
                                                onchange="document.getElementById('filename-tahap4').value = this.files[0] ? this.files[0].name : ''">
                                            <button type="button" class="btn btn-dark"
                                                onclick="document.getElementById('file-tahap4').click()">Pilih
                                                File</button>
                                            <input type="text" id="filename-tahap4" readonly
                                                placeholder="Pilih file surat penerimaan...">
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
                                                    <a href="../../<?= htmlspecialchars($bukti['file_path']) ?>" target="_blank" style="margin-left: 10px; color: #10B981; text-decoration: none; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border: 1px solid #10B981; border-radius: 4px; background: #D1FAE5; transition: all 0.2s;">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                        Lihat File
                                                    </a>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($step4Status === 'ditolak'): ?>
                                    <div style="margin-top:14px;">
                                        <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php">
                                            <input type="hidden" name="type" value="bukti">
                                            <button type="submit" class="btn btn-dark">Ajukan Ulang</button>
                                        </form>
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
                                <div style="background: #F8F9FA; padding: 20px; border-radius: 8px;">
                                    <p style="margin-bottom: 10px;">Status Plotting: <span
                                            style="background: #FFFF00; color: #111; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">Menunggu
                                            Plotting Oleh Korbid</span></p>
                                    <p style="font-size: 14px; margin-bottom: 5px;">Kelompok anda sedang menunggu
                                        penentuan dosen pembimbing oleh koordinator bidang</p>
                                    <p style="font-size: 14px;">Estimasi waktu plotting: 1 - 2 hari kerja</p>
                                </div>
                                <?php else: ?>
                                <div style="background: #F8F9FA; padding: 20px; border-radius: 8px;">
                                    <p style="margin-bottom: 10px;">Status Plotting: <span
                                            style="background: #28C76F; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">Selesai</span></p>
                                    <p style="font-size: 14px; margin-bottom: 5px;">Dosen Pembimbing: <strong><?= htmlspecialchars($plotting['dosen_pembimbing'] ?? 'Belum ditentukan') ?></strong></p>
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
        document.addEventListener('DOMContentLoaded', function() {
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
        });

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
                // Validation only - the completed view is rendered by PHP on reload
                const memberCount = document.querySelectorAll('.member-panel').length;
                let allHaveFile = true;

                for (let mi = 0; mi < memberCount; mi++) {
                    const hasAny = [1, 2, 3, 4, 5].some(fi =>
                        document.getElementById(`fn-m${mi}-${fi}`)?.value.trim()
                    );
                    if (!hasAny) allHaveFile = false;
                }

                if (!allHaveFile) {
                    document.getElementById('err-3').style.display = 'block';
                    return false;
                }
                document.getElementById('err-3').style.display = 'none';
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

        /* ===== Tahap 3: Tab & Status helpers ===== */
        function switchMemberTab(idx) {
            document.querySelectorAll('.member-tab').forEach((el, i) => {
                el.classList.toggle('active', i === idx);
            });
            document.querySelectorAll('.member-panel').forEach((el, i) => {
                el.classList.toggle('active', i === idx);
            });
        }

        function setFile(key, input) {
            const name = input.files[0] ? input.files[0].name : '';
            document.getElementById('fn-' + key).value = name;
        }

        /* ===== Tahap 3: Edit berkas after save ===== */
        function editBerkas() {
            // Hide completed view, show form
            document.getElementById('completed-3').style.display = 'none';
            document.getElementById('form-3').style.display = 'block';
            // Scroll to the form
            document.getElementById('step-3').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function updateMemberStatus(mi) {
            const count = [1, 2, 3, 4, 5].filter(fi =>
                document.getElementById('fn-m' + mi + '-' + fi)?.value.trim()
            ).length;
            const badge = document.getElementById('mstatus-' + mi);
            if (!badge) return;
            if (count === 0) {
                badge.textContent = '';
                badge.style.cssText = '';
            } else if (count < 5) {
                badge.textContent = count + '/5';
                badge.style.cssText = 'background:#FFF0DC;color:#FF9F43;margin-left:4px';
            } else {
                badge.textContent = '\u2713';
                badge.style.cssText = 'background:#D6F5E3;color:#28C76F;margin-left:4px';
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