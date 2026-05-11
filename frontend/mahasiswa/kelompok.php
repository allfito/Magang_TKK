<?php
require_once __DIR__ . '/functions.php';
requireMahasiswaLogin();

$pageTitle = 'Kelompok Magang - SIMM';
$activePage = 'kelompok';

$mysqli = require __DIR__ . '/../../backend/database.php';
$userId = (int) $_SESSION['user_id'];
$user = currentMahasiswa();

// Check if user already has a kelompok
$existingKelompok = null;
$existingAnggota = [];

$stmt = $mysqli->prepare('SELECT k.id, k.nama FROM kelompok k WHERE k.ketua_user_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $existingKelompok = $result->fetch_assoc();
}

if ($existingKelompok) {
    $kelompokId = $existingKelompok['id'];
    $stmt = $mysqli->prepare('SELECT m.nama, m.nim, m.no_tlp, ak.peran, ak.status_berkas FROM anggota_kelompok ak JOIN mahasiswa m ON ak.mahasiswa_id = m.id WHERE ak.kelompok_id = ? ORDER BY ak.peran ASC, ak.created_at ASC');
    $stmt->bind_param('i', $kelompokId);
    $stmt->execute();
    $existingAnggota = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

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
                            <?= htmlspecialchars($existingKelompok['nama']) ?>
                        </h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.7); margin:0;">
                            Kelompok sudah terdaftar
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="member-grid" id="member-grid">
                        <?php foreach ($existingAnggota as $idx => $anggota): ?>
                        <div class="member-grid-slot">
                            <div class="member-grid-card">
                                <img class="member-grid-avatar"
                                     src="https://ui-avatars.com/api/?name=<?= urlencode($anggota['nama']) ?>&background=1F3653&color=fff&size=72"
                                     alt="Avatar <?= htmlspecialchars($anggota['nama']) ?>">
                                <div class="member-grid-name"><?= htmlspecialchars($anggota['nama']) ?></div>
                                <div class="member-grid-nim"><?= htmlspecialchars($anggota['nim']) ?></div>
                                <div class="member-grid-phone"><?= htmlspecialchars($anggota['no_tlp'] ?: '-') ?></div>
                                <?php if ($anggota['peran'] === 'ketua'): ?>
                                <span class="badge-anggota" style="background:#FFF3CD;color:#856404;">KETUA</span>
                                <?php else: ?>
                                <span class="badge-anggota" style="background:#DBE9F4;color:#1C334D;">ANGGOTA</span>
                                <?php endif; ?>
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

    <!-- Modal Tambah Anggota -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-box">
            <h3>Tambah Anggota</h3>

            <div class="modal-field">
                <label>Nama Mahasiswa</label>
                <input type="text" id="input-nama" placeholder="Masukkan nama lengkap...">
            </div>
            <div class="modal-field">
                <label>NIM</label>
                <input type="text" id="input-nim" placeholder="NIM...">
            </div>
            <div class="modal-field">
                <label>No Telepon</label>
                <input type="text" id="input-telp" placeholder="0812...">
            </div>

            <div class="modal-actions">
                <button class="btn-batal" id="btn-batal">BATAL</button>
                <button class="btn-simpan" id="btn-simpan">SIMPAN</button>
            </div>
        </div>
    </div>

    <script>
        // ── State ──
        let memberCount = 0;
        const MAX_MEMBERS = 4;

        const slotLabels = ['Ketua', 'Anggota 1', 'Anggota 2', 'Anggota 3'];
        const badgeLabels = ['KETUA', 'ANGGOTA', 'ANGGOTA', 'ANGGOTA'];
        const badgeColors = [
            { bg: '#FFF3CD', color: '#856404' },
            { bg: '#DBE9F4', color: '#1C334D' },
            { bg: '#DBE9F4', color: '#1C334D' },
            { bg: '#DBE9F4', color: '#1C334D' },
        ];

        // ── Modal open / close ──
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
            document.getElementById('input-nim').value = '';
            document.getElementById('input-telp').value = '';
        }

        if (btnBatal) btnBatal.addEventListener('click', closeModal);
        if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

        // ── Add member ──
        const btnSimpan = document.getElementById('btn-simpan');
        if (btnSimpan) {
            btnSimpan.addEventListener('click', () => {
                const nama = document.getElementById('input-nama').value.trim();
                const nim = document.getElementById('input-nim').value.trim();
                const telp = document.getElementById('input-telp').value.trim();

                if (!nama || !nim) {
                    alert('Nama dan NIM wajib diisi!');
                    return;
                }

                const slotIndex = memberCount;
                const label = badgeLabels[slotIndex];
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

        // ── Submit group ──
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
                const nim = slot.querySelector('.member-grid-nim')?.textContent;
                const telp = slot.dataset.telp || '';

                if (name && nim) {
                    members.push({ nama: name, nim: nim, no_tlp: telp });
                }
            }

            // Populate hidden form
            const container = document.getElementById('form-anggota-container');
            container.innerHTML = `<input type="hidden" name="nama_kelompok" value="${namaKelompok}">`;

            members.forEach((member, index) => {
                container.innerHTML += `
                    <input type="hidden" name="anggota[${index}][nama]" value="${member.nama}">
                    <input type="hidden" name="anggota[${index}][nim]" value="${member.nim}">
                    <input type="hidden" name="anggota[${index}][no_tlp]" value="${member.no_tlp}">
                `;
            });

            document.getElementById('group-form').submit();
        }
    </script>

    <!-- Hidden form for submitting group data -->
    <form id="group-form" method="POST" action="../../backend/mahasiswa/kelompok.php" style="display: none;">
        <div id="form-anggota-container"></div>
    </form>

<?php require __DIR__ . '/footer.php'; ?>