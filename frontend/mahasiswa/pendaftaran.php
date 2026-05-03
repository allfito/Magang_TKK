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
$stmt = $mysqli->prepare('SELECT k.id FROM kelompok k LEFT JOIN anggota_kelompok ak ON ak.kelompok_id = k.id AND ak.mahasiswa_id = ? WHERE k.ketua_id = ? OR ak.mahasiswa_id = ? LIMIT 1');
$stmt->bind_param('iii', $userId, $userId, $userId);
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
    $stmt = $mysqli->prepare('SELECT id, nama, peran FROM anggota_kelompok WHERE kelompok_id = ? ORDER BY peran ASC, created_at ASC');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $anggotaList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $mysqli->prepare('SELECT * FROM pendaftaran_lokasi WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $lokasi = $stmt->get_result()->fetch_assoc();

    $stmt = $mysqli->prepare('SELECT * FROM proposal WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $proposal = $stmt->get_result()->fetch_assoc();

    $stmt = $mysqli->prepare('SELECT * FROM bukti_diterima WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $bukti = $stmt->get_result()->fetch_assoc();

    $stmt = $mysqli->prepare('SELECT * FROM plotting WHERE kelompok_id = ? LIMIT 1');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $plotting = $stmt->get_result()->fetch_assoc();
}

$berkasData = [];
$berkasUploadDate = null;
if ($kelompokId) {
    $bStmt = $mysqli->prepare('SELECT ba.anggota_id, ba.jenis_berkas, ba.file_path, ba.created_at FROM berkas_anggota ba JOIN anggota_kelompok ak ON ba.anggota_id = ak.id WHERE ak.kelompok_id = ?');
    $bStmt->bind_param('i', $kelompokId);
    $bStmt->execute();
    foreach ($bStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $bRow) {
        $berkasData[$bRow['anggota_id']][$bRow['jenis_berkas']] = basename($bRow['file_path']);
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


            <div class="card pendaftaran-card">
                <div class="card-header dark">
                    <h3>Form Pendaftaran Magang</h3>
                </div>
                <div class="card-body p-0" style="padding: 25px; background: white;">

                    <div id="accordion">

                        <!-- Tahap 1 -->
                        <div class="tahap-container active" id="step-1">
                            <div class="t-header <?= $step1Done ? 't-header-white' : 't-header-dark' ?>" id="header-1">Tahap 1 : Lokasi magang <?= $step1Done ? '&#10003; (selesai)' : '' ?></div>

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
                                            <input type="text" name="alamat" id="inp-alamat" placeholder="Alamat lengkap perusahaan"
                                                oninput="debounceMapUpdate()" required>
                                        </div>
                                    </div>

                                    <!-- Google Maps Section -->
                                    <div class="form-group" style="margin-top: 14px;">
                                        <label>&#128205; Lokasi Magang di Google Maps <span
                                                style="color:#EA5455">*</span></label>
                                        <p style="font-size:12px;color:#778;margin-bottom:8px;">Cari lokasi perusahaan
                                            tempat magang di peta, klik <strong>Temukan Lokasi</strong> atau ketik di
                                            kolom pencarian untuk memperbarui peta.</p>

                                        <!-- Search Bar -->
                                        <div class="maps-search-bar">
                                            <input type="text" id="maps-search-input"
                                                placeholder="Cari lokasi di Google Maps..."
                                                onkeydown="if(event.key==='Enter'){event.preventDefault();searchMaps();}">
                                            <button type="button" class="btn btn-dark" onclick="searchMaps()">&#128269;
                                                Cari</button>
                                            <button type="button" class="btn-loc" onclick="getMyLocation()"
                                                title="Gunakan lokasi saya">&#127759; Lokasi Saya</button>
                                        </div>

                                        <!-- Map Embed -->
                                        <div class="maps-wrapper">
                                            <iframe id="map-iframe"
                                                src="https://maps.google.com/maps?q=Malang,+Jawa+Timur,+Indonesia&output=embed&z=13"
                                                allowfullscreen loading="lazy"
                                                referrerpolicy="no-referrer-when-downgrade">
                                            </iframe>
                                        </div>

                                        <!-- Coordinate Fields -->
                                        <div class="maps-coord-row">
                                            <div class="form-group">
                                                <label>Latitude</label>
                                                <input type="text" name="latitude" id="inp-lat" placeholder="Contoh: -7.9666" readonly class="input-readonly">
                                            </div>
                                            <div class="form-group">
                                                <label>Longitude</label>
                                                <input type="text" name="longitude" id="inp-lng" placeholder="Contoh: 112.6326" readonly class="input-readonly">
                                            </div>
                                        </div>
                                        <p class="maps-pin-info" id="maps-pin-label">&#8505; Klik tombol
                                            <span>Cari</span> atau <span>Lokasi Saya</span> untuk menyimpan koordinat
                                            lokasi magang.</p>
                                    </div>

                                    <p id="err-1" style="color:#EA5455;font-size:12px;display:none;margin-bottom:8px;">
                                        &#9888; Harap isi semua field yang wajib diisi.</p>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-dark">Simpan Lokasi Magang</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Completed -->
                            <div class="t-body t-completed" id="completed-1" style="<?= $step1Done ? '' : 'display: none;' ?>">
                                <div class="completed-grid">
                                    <div>
                                        <p>Perusahaan: <strong id="v-perusahaan"><?= htmlspecialchars($lokasi['perusahaan'] ?? '-') ?></strong></p>
                                        <p>Bidang: <strong id="v-bidang"><?= htmlspecialchars($lokasi['bidang'] ?? '-') ?></strong></p>
                                        <p>Alamat: <strong id="v-alamat"><?= htmlspecialchars($lokasi['alamat'] ?? '-') ?></strong></p>
                                        <p>Koordinat: <strong id="v-koordinat"><?= htmlspecialchars(($lokasi['latitude'] ?? '') . ', ' . ($lokasi['longitude'] ?? 'Belum dicatat')) ?></strong></p>
                                    </div>
                                    <div>
                                        <p>Contact Person: <strong id="v-pimpinan"><?= htmlspecialchars($lokasi['nama_pimpinan'] ?? '-') ?></strong></p>
                                        <p>Telepon: <strong id="v-telepon"><?= htmlspecialchars($lokasi['telepon'] ?? '-') ?></strong></p>
                                        <p>Status: <?= stepBadge($step1Status) ?></p>
                                    </div>
                                </div>
                                <?php if ($lokasi && $lokasi['latitude'] && $lokasi['longitude']): ?>
                                <div id="v-map-preview"
                                    style="margin-top:14px;border-radius:10px;overflow:hidden;border:1.5px solid #DDEAF5;">
                                    <iframe src="https://maps.google.com/maps?q=<?= $lokasi['latitude'] ?>,<?= $lokasi['longitude'] ?>&output=embed&z=15" width="100%" height="220" style="border:none;display:block" loading="lazy"></iframe>
                                </div>
                                <?php endif; ?>
                                <?php if ($step1Status === 'ditolak'): ?>
                                <div style="margin-top:10px;">
                                    <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php">
                                        <input type="hidden" name="type" value="lokasi">
                                        <button type="submit" class="btn btn-dark">Ajukan Ulang</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tahap 2 -->
                        <div class="tahap-container <?= $step2Open ? 'active' : 'locked' ?>" id="step-2">
                            <div class="t-header <?= $step2Done ? 't-header-white' : ($step2Open ? 't-header-dark' : 't-header-white') ?>" id="header-2">Tahap 2 : Proposal Kelompok <?= $step2Done ? '&#10003; (selesai)' : '' ?></div>

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

                            <div class="t-body t-completed" id="completed-2" style="<?= $step2Done ? '' : 'display: none;' ?>">
                                <div class="completed-grid">
                                    <div>
                                        <p>Judul Proposal: <strong id="v-judul"><?= htmlspecialchars($proposal['judul'] ?? '-') ?></strong></p>
                                        <p>File: <strong id="v-file-proposal"><?= htmlspecialchars(basename($proposal['file_path'] ?? '-')) ?></strong></p>
                                        <p>Status: <?= stepBadge($step2Status) ?></p>
                                    </div>
                                </div>
                                <?php if ($step2Status === 'ditolak'): ?>
                                <div style="margin-top:10px;">
                                    <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php">
                                        <input type="hidden" name="type" value="proposal">
                                        <button type="submit" class="btn btn-dark">Ajukan Ulang</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tahap 3 -->
                        <div class="tahap-container <?= $step3Open ? 'active' : 'locked' ?>" id="step-3">
                            <div class="t-header <?= $step3Done ? 't-header-white' : ($step3Open ? 't-header-dark' : 't-header-white') ?>" id="header-3">Tahap 3 : Berkas Anggota <?= $step3Done ? '&#10003; (selesai)' : '' ?></div>

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
                            <div class="t-body t-completed" id="completed-3" style="<?= $step3Done ? '' : 'display: none;' ?>">
                                <div id="v-berkas-summary">
                                    <?php if ($step3Done && $kelompokId): ?>
                                        <?php
                                        $berkasLabels = [
                                            'formulir' => 'Formulir',
                                            'ktm' => 'Scan KTM',
                                            'transkrip' => 'Transkrip Nilai',
                                            'pas_foto' => 'Pas Foto',
                                            'cv' => 'CV'
                                        ];
                                        foreach ($anggotaList as $anggota):
                                            $aBerkas = $berkasData[$anggota['id']] ?? [];
                                            $uploadedCount = count($aBerkas);
                                            $totalBerkas = 5;
                                            $iconColor = $uploadedCount === $totalBerkas ? '#28C76F' : ($uploadedCount > 0 ? '#FF9F43' : '#EA5455');
                                            $icon = $uploadedCount === $totalBerkas ? '&#10003;' : ($uploadedCount > 0 ? '&#9888;' : '&#10007;');
                                        ?>
                                        <div class="berkas-member-card">
                                            <div class="berkas-member-header">
                                                <div>
                                                    <span style="color:<?= $iconColor ?>;font-weight:700;"><?= $icon ?> <?= htmlspecialchars($anggota['nama']) ?></span>
                                                    <em style="color:rgba(255,255,255,0.6);font-size:12px;margin-left:4px;">(<?= ucfirst(htmlspecialchars($anggota['peran'])) ?>)</em>
                                                </div>
                                                <span class="berkas-count" style="color:<?= $iconColor ?>"><?= $uploadedCount ?>/<?= $totalBerkas ?> berkas</span>
                                            </div>
                                            <div class="berkas-file-list">
                                                <?php foreach ($aBerkas as $jenis => $filename): ?>
                                                <div class="berkas-file-item">
                                                    &#128196; <?= $berkasLabels[$jenis] ?? $jenis ?>: <?= htmlspecialchars($filename) ?>
                                                </div>
                                                <?php endforeach; ?>
                                                <?php if (empty($aBerkas)): ?>
                                                <div class="berkas-file-item" style="font-style:italic;opacity:0.6;">Belum ada berkas diupload</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($berkasUploadFormatted): ?>
                                <p style="margin-top:14px;font-weight:700;font-size:14px;">Tanggal Upload: <strong><?= $berkasUploadFormatted ?></strong></p>
                                <?php endif; ?>
                                <p style="margin-top:6px;font-size:14px;">Status: &nbsp;<?= stepBadge($step3Status) ?></p>

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
                            </div>
                        </div>

                        <!-- Tahap 4 -->
                        <div class="tahap-container <?= $step4Open ? 'active' : 'locked' ?>" id="step-4">
                            <div class="t-header <?= $step4Done ? 't-header-white' : ($step4Open ? 't-header-dark' : 't-header-white') ?>" id="header-4">Tahap 4 : Bukti Diterima <?= $step4Done ? '&#10003; (selesai)' : '' ?></div>

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

                            <div class="t-body t-completed" id="completed-4" style="<?= $step4Done ? '' : 'display: none;' ?>">
                                <div class="completed-grid">
                                    <div>
                                        <p>Tempat Diterima: <strong id="v-tempat"><?= htmlspecialchars($bukti['tempat_diterima'] ?? '-') ?></strong></p>
                                        <p>File: <strong id="v-file-bukti"><?= htmlspecialchars(basename($bukti['file_path'] ?? '-')) ?></strong></p>
                                        <p>Status: <?= stepBadge($step4Status) ?></p>
                                    </div>
                                </div>
                                <?php if ($step4Status === 'ditolak'): ?>
                                <div style="margin-top:10px;">
                                    <form method="POST" action="../../backend/mahasiswa/hapus_pendaftaran.php">
                                        <input type="hidden" name="type" value="bukti">
                                        <button type="submit" class="btn btn-dark">Ajukan Ulang</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tahap 5 -->
                        <div class="tahap-container <?= $step5Open ? 'active' : 'locked' ?>" id="step-5">
                            <div class="t-header <?= $step5Done ? 't-header-white' : ($step5Open ? 't-header-dark' : 't-header-white') ?>" id="header-5">Tahap 5 : Plotting Dosen <?= $step5Done ? '&#10003; (selesai)' : '' ?></div>

                            <div class="t-body t-form" id="form-5" style="<?= ($step5Open && !$step5Done) ? '' : 'display: none;' ?>">
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
                                    <p style="font-size: 14px; margin-bottom: 5px;">Dosen Pembimbing: <strong><?= htmlspecialchars($plotting['dosen_pembimbing']) ?></strong></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        // ===== Google Maps Helpers =====
        let mapQuery = 'Malang, Jawa Timur, Indonesia';
        let savedLat = '';
        let savedLng = '';
        let mapUpdateTimer = null;

        function updateMap(query) {
            const iframe = document.getElementById('map-iframe');
            if (!iframe || !query) return;
            const encoded = encodeURIComponent(query);
            iframe.src = `https://maps.google.com/maps?q=${encoded}&output=embed&z=15`;
            mapQuery = query;
        }

        function searchMaps() {
            const searchInput = document.getElementById('maps-search-input');
            const query = searchInput.value.trim() || document.getElementById('inp-alamat').value.trim();
            if (!query) { alert('Masukkan nama lokasi atau alamat terlebih dahulu.'); return; }

            // Try Geocoding via Nominatim (free, no API key needed)
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const { lat, lon, display_name } = data[0];
                        savedLat = parseFloat(lat).toFixed(6);
                        savedLng = parseFloat(lon).toFixed(6);
                        document.getElementById('inp-lat').value = savedLat;
                        document.getElementById('inp-lng').value = savedLng;
                        document.getElementById('maps-pin-label').innerHTML =
                            `&#128205; Lokasi ditemukan: <span>${display_name.substring(0, 60)}...</span>`;
                        // Update map embed with coordinates
                        const iframe = document.getElementById('map-iframe');
                        iframe.src = `https://maps.google.com/maps?q=${savedLat},${savedLng}&output=embed&z=16`;
                    } else {
                        alert('Lokasi tidak ditemukan. Coba dengan kata kunci yang lebih spesifik.');
                    }
                })
                .catch(() => {
                    // Fallback: update map by query string
                    updateMap(query);
                    document.getElementById('maps-pin-label').innerHTML =
                        `&#128205; Peta diperbarui ke: <span>${query}</span> (koordinat tidak tersedia)`;
                });
        }

        function getMyLocation() {
            if (!navigator.geolocation) { alert('Browser Anda tidak mendukung Geolocation.'); return; }
            navigator.geolocation.getCurrentPosition(
                pos => {
                    savedLat = pos.coords.latitude.toFixed(6);
                    savedLng = pos.coords.longitude.toFixed(6);
                    document.getElementById('inp-lat').value = savedLat;
                    document.getElementById('inp-lng').value = savedLng;
                    const iframe = document.getElementById('map-iframe');
                    iframe.src = `https://maps.google.com/maps?q=${savedLat},${savedLng}&output=embed&z=16`;
                    document.getElementById('maps-pin-label').innerHTML =
                        `&#128205; Posisi Anda: <span>${savedLat}, ${savedLng}</span>`;
                    // Populate search input
                    document.getElementById('maps-search-input').value = `${savedLat}, ${savedLng}`;
                },
                err => { alert('Gagal mendapatkan lokasi: ' + err.message); }
            );
        }

        function debounceMapUpdate() {
            clearTimeout(mapUpdateTimer);
            const alamat = document.getElementById('inp-alamat').value.trim();
            if (!alamat) return;
            // Sync search input with alamat
            document.getElementById('maps-search-input').value = alamat;
            mapUpdateTimer = setTimeout(() => updateMap(alamat), 900);
        }

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

                // Simpan koordinat & tampilkan peta mini di ringkasan
                const lat = document.getElementById('inp-lat').value.trim();
                const lng = document.getElementById('inp-lng').value.trim();
                const koordinatText = lat && lng ? `${lat}, ${lng}` : 'Belum dicatat';
                document.getElementById('v-koordinat').textContent = koordinatText;

                const mapPreview = document.getElementById('v-map-preview');
                if (lat && lng) {
                    mapPreview.innerHTML = `<iframe src="https://maps.google.com/maps?q=${lat},${lng}&output=embed&z=15" width="100%" height="220" style="border:none;display:block" loading="lazy"></iframe>`;
                } else {
                    const q = encodeURIComponent(alamat);
                    mapPreview.innerHTML = `<iframe src="https://maps.google.com/maps?q=${q}&output=embed&z=14" width="100%" height="220" style="border:none;display:block" loading="lazy"></iframe>`;
                }
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

            // Tandai header selesai
            currentHeader.classList.remove('t-header-dark');
            currentHeader.classList.add('t-header-white');
            if (!currentHeader.innerText.includes('(selesai)')) {
                currentHeader.innerText = currentHeader.innerText + ' ✓ (selesai)';
            }

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
    </script>
<?php require __DIR__ . '/footer.php'; ?>