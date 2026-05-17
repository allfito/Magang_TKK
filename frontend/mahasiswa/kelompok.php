<?php
require_once __DIR__ . '/../../backend/helpers/MahasiswaHelper.php';
MahasiswaHelper::requireLogin();

$pageTitle = 'Kelompok Magang - SIMM';
$activePage = 'kelompok';

// Load existing data via Controller
require_once __DIR__ . '/../../backend/controllers/MahasiswaKelompokViewController.php';

$userId = (int) $_SESSION['user_id'];
$user = MahasiswaHelper::currentUser();

$controller = new MahasiswaKelompokViewController();
$kelompokData = $controller->getKelompokData($userId);

$existingKelompok = $kelompokData['kelompok'];
$existingAnggota = $kelompokData['anggotaList'];

// Session messages
$errorMessage = $_SESSION['error'] ?? '';
$successMessage = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

require __DIR__ . '/header.php';
?>

            <?php if ($successMessage): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 12px 18px; margin-bottom: 16px; border-radius: 8px; border: 1px solid #c3e6cb; font-size: 14px;">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 12px 18px; margin-bottom: 16px; border-radius: 8px; border: 1px solid #f5c6cb; font-size: 14px;">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>


            <?php if ($existingKelompok): ?>
            <!-- Existing Kelompok View -->
            <div class="card">
                <div class="card-header dark"
                    style="display:flex; align-items:center; justify-content:space-between; padding: 20px 28px;">
                    <div>
                        <h3 style="font-size:18px; font-weight:700; margin-bottom:4px;">
                            <?= htmlspecialchars(ucwords(strtolower($existingKelompok['nama']))) ?>
                        </h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.7); margin:0;">
                            <?= count($existingAnggota) ?>/4 anggota terdaftar
                        </p>
                    </div>
                    <?php if (count($existingAnggota) < 4): ?>
                    <button class="btn-tambah" id="btn-tambah-existing" onclick="openTambahModal()">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                            <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                        Tambah Anggota
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="member-grid" id="member-grid">
                        <?php foreach ($existingAnggota as $idx => $anggota): ?>
                        <div class="member-grid-slot">
                            <div class="member-card-v2">
                                <!-- Hover Overlay -->
                                <div class="member-card-overlay">
                                    <button type="button" class="mcv2-btn mcv2-btn-edit"
                                        onclick="openEditModal(
                                            <?= (int)$anggota['anggota_id'] ?>,
                                            <?= (int)$anggota['mahasiswa_id'] ?>,
                                            '<?= addslashes(htmlspecialchars(ucwords(strtolower($anggota['nama'])))) ?>',
                                            '<?= addslashes(htmlspecialchars($anggota['nim'])) ?>',
                                            '<?= addslashes(htmlspecialchars($anggota['no_tlp'] ?: '')) ?>'
                                        )">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <?php if ($anggota['peran'] !== 'ketua'): ?>
                                    <button type="button" class="mcv2-btn mcv2-btn-hapus"
                                        onclick="confirmHapus(<?= (int)$anggota['anggota_id'] ?>, '<?= addslashes(htmlspecialchars(ucwords(strtolower($anggota['nama'])))) ?>')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        Hapus
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Avatar -->
                                <div class="mcv2-avatar-wrap <?= $anggota['peran'] === 'ketua' ? 'mcv2-avatar-ketua' : 'mcv2-avatar-anggota' ?>">
                                    <img class="mcv2-avatar"
                                         src="https://ui-avatars.com/api/?name=<?= urlencode($anggota['nama']) ?>&background=1F3653&color=fff&size=80&bold=true"
                                         alt="<?= htmlspecialchars(ucwords(strtolower($anggota['nama']))) ?>">
                                </div>

                                <!-- Badge -->
                                <?php if ($anggota['peran'] === 'ketua'): ?>
                                <span class="mcv2-badge mcv2-badge-ketua">&#9733; Ketua</span>
                                <?php else: ?>
                                <span class="mcv2-badge mcv2-badge-anggota">Anggota</span>
                                <?php endif; ?>

                                <!-- Info -->
                                <div class="mcv2-name"><?= htmlspecialchars(ucwords(strtolower($anggota['nama']))) ?></div>
                                <div class="mcv2-detail">
                                    <span class="mcv2-nim"><?= htmlspecialchars($anggota['nim']) ?></span>
                                    <?php if ($anggota['no_tlp']): ?>
                                    <span class="mcv2-sep">·</span>
                                    <span class="mcv2-phone"><?= htmlspecialchars($anggota['no_tlp']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Create New Kelompok -->
            <div class="card">
                <div class="card-header dark"
                    style="display:flex; align-items:center; justify-content:space-between; padding: 20px 28px;">
                    <div>
                        <h3 style="font-size:18px; font-weight:700; margin-bottom:4px;">Anggota Kelompok
                        </h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.7); margin:0;">Minimal 3 orang, Maksimal 4 orang anggota.
                        </p>
                    </div>
                    <button class="btn-tambah" id="btn-open-modal">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                            <path
                                d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                        Tambah Anggota
                    </button>
                </div>

                <div class="card-body">
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; font-weight:600; margin-bottom:8px;">Nama Kelompok <span style="color:#EA5455">*</span></label>
                        <input type="text" id="input-nama-kelompok" placeholder="Masukkan nama kelompok..." style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                    </div>
                    <div class="member-grid" id="member-grid">
                        <!-- Slot 1: Ketua -->
                        <div class="member-grid-slot" id="slot-0">
                            <div class="member-grid-empty">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#A0B2C0">
                                    <path
                                        d="M12 2C9.24 2 7 4.24 7 7s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" />
                                </svg>
                                <span class="slot-label">Ketua</span>
                            </div>
                        </div>
                        <!-- Slot 2: Anggota 1 -->
                        <div class="member-grid-slot" id="slot-1">
                            <div class="member-grid-empty">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#A0B2C0">
                                    <path
                                        d="M12 2C9.24 2 7 4.24 7 7s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" />
                                </svg>
                                <span class="slot-label">Anggota 1</span>
                            </div>
                        </div>
                        <!-- Slot 3: Anggota 2 -->
                        <div class="member-grid-slot" id="slot-2">
                            <div class="member-grid-empty">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#A0B2C0">
                                    <path
                                        d="M12 2C9.24 2 7 4.24 7 7s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" />
                                </svg>
                                <span class="slot-label">Anggota 2</span>
                            </div>
                        </div>
                        <!-- Slot 4: Anggota 3 -->
                        <div class="member-grid-slot" id="slot-3">
                            <div class="member-grid-empty">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#A0B2C0">
                                    <path
                                        d="M12 2C9.24 2 7 4.24 7 7s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" />
                                </svg>
                                <span class="slot-label">Anggota 3</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-dark" style="margin-top:20px;" onclick="submitGroup()">Buat Kelompok</button>
                </div>
            </div><!-- /card -->
            <?php endif; ?>

        </main>
    </div>

    <!-- ── Modal Edit Anggota ── -->
    <div class="modal-overlay" id="modal-edit-overlay">
        <div class="modal-box">
            <h3>Edit Anggota</h3>
            <div class="modal-field">
                <label>Nama Mahasiswa</label>
                <input type="text" id="edit-input-nama" placeholder="Nama lengkap...">
            </div>
            <div class="modal-field">
                <label>NIM <small style="color:#A0B2C0;font-weight:400;">(maks. 9 karakter)</small></label>
                <input type="text" id="edit-input-nim" placeholder="NIM..." maxlength="9">
            </div>
            <div class="modal-field">
                <label>No Telepon <small style="color:#A0B2C0;font-weight:400;">(10-13 digit, diawali 08)</small></label>
                <input type="text" id="edit-input-telp" placeholder="0812..." minlength="10" maxlength="13" pattern="08[0-9]{8,11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka">
            </div>
            <div class="modal-actions">
                <button class="btn-batal" onclick="closeEditModal()">BATAL</button>
                <button class="btn-simpan" onclick="submitEdit()">SIMPAN</button>
            </div>
        </div>
    </div>

    <!-- ── Modal Tambah Anggota (Buat Kelompok Baru) ── -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-box">
            <h3>Tambah Anggota</h3>

            <div class="modal-field">
                <label>Nama Mahasiswa</label>
                <input type="text" id="input-nama" placeholder="Masukkan nama lengkap...">
            </div>
            <div class="modal-field">
                <label>NIM <small style="color:#A0B2C0;font-weight:400;">(maks. 9 karakter)</small></label>
                <input type="text" id="input-nim" placeholder="NIM..." maxlength="9">
            </div>
            <div class="modal-field">
                <label>No Telepon <small style="color:#A0B2C0;font-weight:400;">(10-13 digit, diawali 08)</small></label>
                <input type="text" id="input-telp" placeholder="0812..." minlength="10" maxlength="13" pattern="08[0-9]{8,11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka">
            </div>

            <div class="modal-actions">
                <button class="btn-batal" id="btn-batal">BATAL</button>
                <button class="btn-simpan" id="btn-simpan">SIMPAN</button>
            </div>
        </div>
    </div>

    <!-- ── Modal Tambah Anggota (Existing Kelompok) ── -->
    <div class="modal-overlay" id="modal-tambah-existing-overlay">
        <div class="modal-box">
            <h3>Tambah Anggota ke Kelompok</h3>

            <div class="modal-field">
                <label>Nama Mahasiswa</label>
                <input type="text" id="tambah-existing-nama" placeholder="Masukkan nama lengkap...">
            </div>
            <div class="modal-field">
                <label>NIM <small style="color:#A0B2C0;font-weight:400;">(maks. 9 karakter)</small></label>
                <input type="text" id="tambah-existing-nim" placeholder="NIM..." maxlength="9">
            </div>
            <div class="modal-field">
                <label>No Telepon <small style="color:#A0B2C0;font-weight:400;">(10-13 digit, diawali 08)</small></label>
                <input type="text" id="tambah-existing-telp" placeholder="0812..." minlength="10" maxlength="13" pattern="08[0-9]{8,11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka">
            </div>

            <div class="modal-actions">
                <button class="btn-batal" onclick="closeTambahExistingModal()">BATAL</button>
                <button class="btn-simpan" onclick="submitTambahExisting()">SIMPAN</button>
            </div>
        </div>
    </div>

    <!-- ── Hidden form: Edit anggota ── -->
    <form id="form-edit-anggota" method="POST"
          action="../../backend/actions/mahasiswa_anggota_edit.php"
          style="display:none;">
        <input type="hidden" name="anggota_id"   id="hf-anggota-id">
        <input type="hidden" name="mahasiswa_id" id="hf-mahasiswa-id">
        <input type="hidden" name="nama"         id="hf-nama">
        <input type="hidden" name="nim"          id="hf-nim">
        <input type="hidden" name="no_tlp"       id="hf-no-tlp">
    </form>

    <!-- ── Hidden form: Hapus anggota ── -->
    <form id="form-hapus-anggota" method="POST"
          action="../../backend/actions/mahasiswa_anggota_hapus.php"
          style="display:none;">
        <input type="hidden" name="anggota_id" id="hf-hapus-anggota-id">
    </form>

    <!-- ── Hidden form: Tambah anggota existing ── -->
    <form id="form-tambah-existing" method="POST"
          action="../../backend/actions/mahasiswa_anggota_tambah.php"
          style="display:none;">
        <input type="hidden" name="nama"   id="hf-tambah-nama">
        <input type="hidden" name="nim"    id="hf-tambah-nim">
        <input type="hidden" name="no_tlp" id="hf-tambah-telp">
    </form>

    <script>
        // ── State ──
        let memberCount = 0;
        const MAX_MEMBERS = 4;

        const slotLabels  = ['Ketua', 'Anggota 1', 'Anggota 2', 'Anggota 3'];
        const badgeLabels = ['KETUA', 'ANGGOTA', 'ANGGOTA', 'ANGGOTA'];
        const badgeColors = [
            { bg: '#FFF3CD', color: '#856404' },
            { bg: '#DBE9F4', color: '#1C334D' },
            { bg: '#DBE9F4', color: '#1C334D' },
            { bg: '#DBE9F4', color: '#1C334D' },
        ];

        // ── Modal tambah open / close ──
        const overlay = document.getElementById('modal-overlay');
        const btnOpen = document.getElementById('btn-open-modal');
        const btnBatal = document.getElementById('btn-batal');

        if (btnOpen) {
            btnOpen.addEventListener('click', () => {
                if (memberCount >= MAX_MEMBERS) {
                    alert('Kelompok sudah penuh (maksimal 4 anggota termasuk ketua).');
                    return;
                }
                document.querySelector('#modal-overlay h3').textContent =
                    memberCount === 0 ? 'Tambah Ketua Kelompok' : 'Tambah Anggota';
                overlay.classList.add('open');
                document.getElementById('input-nama').focus();
            });
        }

        function closeModal() {
            overlay.classList.remove('open');
            document.getElementById('input-nama').value = '';
            document.getElementById('input-nim').value  = '';
            document.getElementById('input-telp').value = '';
        }

        if (btnBatal) btnBatal.addEventListener('click', closeModal);
        if (overlay)  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

        // ── Add member ──
        const btnSimpan = document.getElementById('btn-simpan');
        if (btnSimpan) {
            btnSimpan.addEventListener('click', () => {
                const nama = document.getElementById('input-nama').value.trim();
                const nim  = document.getElementById('input-nim').value.trim();
                const telp = document.getElementById('input-telp').value.trim();

                if (!nama || !nim) { alert('Nama dan NIM wajib diisi!'); return; }
                if (telp && !/^08[0-9]{8,11}$/.test(telp)) {
                    alert('Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka!');
                    document.getElementById('input-telp').focus();
                    return;
                }

                const slotIndex = memberCount;
                const label  = badgeLabels[slotIndex];
                const colors = badgeColors[slotIndex];

                const slot = document.getElementById('slot-' + slotIndex);
                slot.innerHTML = `
                    <div class="member-grid-card">
                        <img class="member-grid-avatar"
                             src="https://ui-avatars.com/api/?name=${encodeURIComponent(nama)}&background=1F3653&color=fff&size=72"
                             alt="Avatar ${nama}">
                        <div class="member-grid-name">${nama}</div>
                        <div class="member-grid-nim">${nim}</div>
                        <div class="member-grid-phone">${telp || '-'}</div>
                        <span class="badge-anggota" style="background:${colors.bg};color:${colors.color};">${label}</span>
                    </div>
                `;
                slot.dataset.telp = telp;
                memberCount++;
                closeModal();
            });
        }

        // ── Submit group (buat kelompok baru) ──
        function submitGroup() {
            const namaKelompok = document.getElementById('input-nama-kelompok').value.trim();
            if (!namaKelompok) {
                alert('Nama kelompok wajib diisi!');
                document.getElementById('input-nama-kelompok').focus();
                return;
            }
            if (memberCount < 3) {
                alert('Minimal 3 anggota (ketua + 2 anggota)');
                return;
            }

            const members = [];
            for (let i = 0; i < memberCount; i++) {
                const slot = document.getElementById('slot-' + i);
                const name = slot.querySelector('.member-grid-name')?.textContent;
                const nim  = slot.querySelector('.member-grid-nim')?.textContent;
                const telp = slot.dataset.telp || '';
                if (name && nim) members.push({ nama: name, nim, no_tlp: telp });
            }

            const container = document.getElementById('form-anggota-container');
            container.innerHTML = `<input type="hidden" name="nama_kelompok" value="${namaKelompok}">`;
            members.forEach((member, index) => {
                container.innerHTML += `
                    <input type="hidden" name="anggota[${index}][nama]"   value="${member.nama}">
                    <input type="hidden" name="anggota[${index}][nim]"    value="${member.nim}">
                    <input type="hidden" name="anggota[${index}][no_tlp]" value="${member.no_tlp}">
                `;
            });
            document.getElementById('group-form').submit();
        }

        // ──────────────────────────────────────────
        // Edit Anggota
        // ──────────────────────────────────────────
        const editOverlay = document.getElementById('modal-edit-overlay');

        function openEditModal(anggotaId, mahasiswaId, nama, nim, telp) {
            document.getElementById('edit-input-nama').value = nama;
            document.getElementById('edit-input-nim').value  = nim;
            document.getElementById('edit-input-telp').value = telp;
            editOverlay.dataset.anggotaId   = anggotaId;
            editOverlay.dataset.mahasiswaId = mahasiswaId;
            editOverlay.classList.add('open');
            document.getElementById('edit-input-nama').focus();
        }

        function closeEditModal() {
            editOverlay.classList.remove('open');
        }

        editOverlay.addEventListener('click', (e) => { if (e.target === editOverlay) closeEditModal(); });

        function submitEdit() {
            const nama = document.getElementById('edit-input-nama').value.trim();
            const nim  = document.getElementById('edit-input-nim').value.trim();
            const telp = document.getElementById('edit-input-telp').value.trim();

            if (!nama || !nim) { alert('Nama dan NIM wajib diisi!'); return; }
            if (telp && !/^08[0-9]{8,11}$/.test(telp)) {
                alert('Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka!');
                document.getElementById('edit-input-telp').focus();
                return;
            }

            document.getElementById('hf-anggota-id').value   = editOverlay.dataset.anggotaId;
            document.getElementById('hf-mahasiswa-id').value = editOverlay.dataset.mahasiswaId;
            document.getElementById('hf-nama').value   = nama;
            document.getElementById('hf-nim').value    = nim;
            document.getElementById('hf-no-tlp').value = telp;
            document.getElementById('form-edit-anggota').submit();
        }

        // ──────────────────────────────────────────
        // Hapus Anggota
        // ──────────────────────────────────────────
        function confirmHapus(anggotaId, nama) {
            if (!confirm(`Hapus "${nama}" dari kelompok?\n\nTindakan ini tidak dapat dibatalkan.`)) return;
            document.getElementById('hf-hapus-anggota-id').value = anggotaId;
            document.getElementById('form-hapus-anggota').submit();
        }

        // ──────────────────────────────────────────
        // Tambah Anggota Existing
        // ──────────────────────────────────────────
        const tambahExistingOverlay = document.getElementById('modal-tambah-existing-overlay');

        function openTambahModal() {
            tambahExistingOverlay.classList.add('open');
            document.getElementById('tambah-existing-nama').focus();
        }

        function closeTambahExistingModal() {
            tambahExistingOverlay.classList.remove('open');
            document.getElementById('tambah-existing-nama').value = '';
            document.getElementById('tambah-existing-nim').value = '';
            document.getElementById('tambah-existing-telp').value = '';
        }

        if (tambahExistingOverlay) {
            tambahExistingOverlay.addEventListener('click', (e) => { 
                if (e.target === tambahExistingOverlay) closeTambahExistingModal(); 
            });
        }

        function submitTambahExisting() {
            const nama = document.getElementById('tambah-existing-nama').value.trim();
            const nim  = document.getElementById('tambah-existing-nim').value.trim();
            const telp = document.getElementById('tambah-existing-telp').value.trim();

            if (!nama || !nim) { 
                alert('Nama dan NIM wajib diisi!'); 
                return; 
            }
            if (telp && !/^08[0-9]{8,11}$/.test(telp)) {
                alert('Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka!');
                document.getElementById('tambah-existing-telp').focus();
                return;
            }

            document.getElementById('hf-tambah-nama').value = nama;
            document.getElementById('hf-tambah-nim').value  = nim;
            document.getElementById('hf-tambah-telp').value = telp;
            document.getElementById('form-tambah-existing').submit();
        }
    </script>

    <!-- Hidden form for submitting group data (buat kelompok baru) -->
    <form id="group-form" method="POST" action="../../backend/actions/mahasiswa_kelompok.php" style="display: none;">
        <div id="form-anggota-container"></div>
    </form>

<?php require __DIR__ . '/footer.php'; ?>