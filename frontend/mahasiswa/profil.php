<?php
require_once __DIR__ . '/functions.php';
requireMahasiswaLogin();

$user = currentMahasiswa();
$userName = $user ? htmlspecialchars($user['nama']) : 'Mahasiswa';
$userEmail = $user ? htmlspecialchars($user['email']) : '';
$userPhone = $user ? htmlspecialchars($user['no_tlp']) : '';

$pageTitle = 'Profil Mahasiswa - SIMM';
$activePage = 'profil';
$extraHead = '<style>
        .profil-main { display: flex; justify-content: center; align-items: flex-start; padding: 40px 30px; }
        .profil-card { background: white; border-radius: 20px; box-shadow: 0 6px 30px rgba(28, 51, 77, 0.10); width: 100%; max-width: 520px; overflow: hidden; }
        .profil-avatar-section { display: flex; flex-direction: column; align-items: center; padding: 36px 32px 24px; border-bottom: 1px solid #F0F4F8; }
        .avatar-wrapper { position: relative; margin-bottom: 16px; }
        .profil-avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #E8EDF3; display: block; }
        .avatar-edit-btn { position: absolute; bottom: 4px; right: 4px; width: 30px; height: 30px; background: #1F3653; border-radius: 50%; border: 2px solid white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .avatar-edit-btn:hover { background: #2A486C; }
        .avatar-edit-btn svg { width: 14px; height: 14px; fill: white; }
        #avatar-file-input { display: none; }
        .profil-username { font-size: 22px; font-weight: 700; color: #1C334D; margin-bottom: 4px; }
        .profil-role { font-size: 13px; color: #7B8FA1; font-weight: 500; }
        .profil-fields { padding: 8px 32px 8px; }
        .profil-field-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #F0F4F8; gap: 16px; }
        .profil-field-row:last-child { border-bottom: none; }
        .profil-field-label { font-size: 11px; font-weight: 700; color: #9BAABB; letter-spacing: 0.8px; text-transform: uppercase; flex-shrink: 0; width: 130px; }
        .profil-field-value { font-size: 14px; font-weight: 500; color: #1C334D; text-align: right; flex: 1; }
        .profil-field-input { font-size: 14px; font-weight: 500; color: #1C334D; text-align: right; flex: 1; border: none; border-bottom: 2px solid #87CEEB; outline: none; background: transparent; padding: 2px 4px; font-family: inherit; transition: border-color 0.2s; }
        .profil-field-input:focus { border-bottom-color: #1F3653; }
        .profil-btn-area { padding: 20px 32px 28px; }
        .btn-edit-profil { width: 100%; padding: 14px; background: #1F3653; color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; font-family: inherit; transition: background 0.2s, transform 0.15s; letter-spacing: 0.3px; }
        .btn-edit-profil:hover { background: #2A486C; transform: translateY(-1px); }
        .btn-batal-edit { width: 100%; padding: 10px; background: none; color: #9BAABB; border: none; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; margin-top: 8px; transition: color 0.2s; }
        .btn-batal-edit:hover { color: #1C334D; }
        .btn-batal-edit.hidden { display: none; }
        .toast { position: fixed; bottom: 32px; right: 32px; background: #1F3653; color: white; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 500; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); opacity: 0; transform: translateY(10px); transition: opacity 0.3s, transform 0.3s; pointer-events: none; z-index: 999; }
        .toast.show { opacity: 1; transform: translateY(0); }
    </style>';
require __DIR__ . '/header.php';
?>


            <div class="profil-card">

                <!-- Avatar + Nama -->
                <div class="profil-avatar-section">
                    <div class="avatar-wrapper">
                        <img id="profil-avatar-img" class="profil-avatar"
                            src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=1F3653&color=fff&size=100"
                            alt="Foto Profil">
                        <button class="avatar-edit-btn" id="btn-ganti-foto" title="Ganti foto profil">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.92c.04-.36.07-.72.07-1.08s-.03-.73-.07-1.08l2.32-1.82c.21-.16.26-.46.12-.7l-2.2-3.81c-.13-.24-.42-.33-.66-.24l-2.74 1.1c-.57-.44-1.18-.81-1.86-1.08l-.41-2.91c-.04-.25-.26-.44-.52-.44h-4.4c-.26 0-.48.19-.52.44l-.41 2.91c-.68.27-1.29.64-1.86 1.08l-2.74-1.1c-.24-.09-.53 0-.66.24l-2.2 3.81c-.14.24-.08.54.12.7l2.32 1.82c-.04.35-.07.72-.07 1.08s.03.73.07 1.08l-2.32 1.82c-.21.16-.26.46-.12.7l2.2 3.81c.13.24.42.33.66.24l2.74-1.1c.57.44 1.18.81 1.86 1.08l.41 2.91c.04.25.26.44.52.44h4.4c.26 0 .48-.19.52-.44l.41-2.91c.68-.27 1.29-.64 1.86-1.08l2.74 1.1c.24.09.53 0 .66-.24l2.2-3.81c.14-.24.08-.54-.12-.7l-2.32-1.82z" />
                            </svg>
                        </button>
                        <input type="file" id="avatar-file-input" accept="image/*">
                    </div>
                    <div class="profil-username" id="display-username"><?= $userName ?></div>
                    <div class="profil-role">Mahasiswa</div>
                </div>

                <!-- Field Rows -->
                <div class="profil-fields">

                    <div class="profil-field-row">
                        <span class="profil-field-label">NIM</span>
                        <span class="profil-field-value" id="val-nim" data-field="nim">e41252686</span>
                        <input class="profil-field-input hidden-input" id="inp-nim" type="text" value="e41252686"
                            style="display:none;">
                    </div>

                    <div class="profil-field-row">
                        <span class="profil-field-label">Program Studi</span>
                        <span class="profil-field-value" id="val-prodi" data-field="prodi">Teknik Komputer</span>
                        <input class="profil-field-input hidden-input" id="inp-prodi" type="text"
                            value="Teknik Komputer" style="display:none;">
                    </div>

                    <div class="profil-field-row">
                        <span class="profil-field-label">No Telepon</span>
                        <span class="profil-field-value" id="val-telp" data-field="telp"><?= $userPhone ?: '–' ?></span>
                        <input class="profil-field-input hidden-input" id="inp-telp" type="text" value="<?= $userPhone ?>"
                            placeholder="08xx..." style="display:none;">
                    </div>

                    <div class="profil-field-row">
                        <span class="profil-field-label">Email</span>
                        <span class="profil-field-value" id="val-email"
                            data-field="email"><?= $userEmail ?></span>
                        <input class="profil-field-input hidden-input" id="inp-email" type="email"
                            value="<?= $userEmail ?>" style="display:none;">
                    </div>

                </div>

                <!-- Tombol -->
                <div class="profil-btn-area">
                    <button class="btn-edit-profil" id="btn-edit-profil">Edit Profil</button>
                    <button class="btn-batal-edit hidden" id="btn-batal-edit">Batal</button>
                </div>

            </div><!-- /profil-card -->

        </main>
    </div>

    <!-- Toast Notifikasi -->
    <div class="toast" id="toast">Profil berhasil disimpan!</div>

    <script>
        // ─── Ganti Foto ───────────────────────────────────────────
        document.getElementById('btn-ganti-foto').addEventListener('click', () => {
            document.getElementById('avatar-file-input').click();
        });

        document.getElementById('avatar-file-input').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('profil-avatar-img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        // ─── Edit / Simpan Profil ─────────────────────────────────
        const fields = [
            { val: 'val-nim', inp: 'inp-nim' },
            { val: 'val-prodi', inp: 'inp-prodi' },
            { val: 'val-telp', inp: 'inp-telp' },
            { val: 'val-semester', inp: 'inp-semester' },
            { val: 'val-email', inp: 'inp-email' },
        ];

        let isEditing = false;

        const btnEdit = document.getElementById('btn-edit-profil');
        const btnBatal = document.getElementById('btn-batal-edit');

        // Simpan nilai asli saat mulai edit
        let originalValues = {};

        function enterEditMode() {
            isEditing = true;
            // Simpan nilai asli
            fields.forEach(f => {
                originalValues[f.inp] = document.getElementById(f.inp).value;
            });
            // Tampilkan input, sembunyikan teks
            fields.forEach(f => {
                document.getElementById(f.val).style.display = 'none';
                document.getElementById(f.inp).style.display = 'block';
            });
            btnEdit.textContent = 'Simpan';
            btnEdit.classList.add('mode-simpan');
            btnBatal.classList.remove('hidden');
        }

        function exitEditMode(save) {
            isEditing = false;
            fields.forEach(f => {
                const valEl = document.getElementById(f.val);
                const inpEl = document.getElementById(f.inp);

                if (save) {
                    const newVal = inpEl.value.trim();
                    valEl.textContent = newVal !== '' ? newVal : '–';
                    // Update username display jika NIM berubah
                    if (f.inp === 'inp-nim') {
                        document.getElementById('display-username').textContent = newVal || 'e41252686';
                    }
                } else {
                    // Kembalikan nilai asli
                    inpEl.value = originalValues[f.inp];
                }

                valEl.style.display = 'block';
                inpEl.style.display = 'none';
            });

            btnEdit.textContent = 'Edit Profil';
            btnEdit.classList.remove('mode-simpan');
            btnBatal.classList.add('hidden');

            if (save) showToast('Profil berhasil disimpan!');
        }

        btnEdit.addEventListener('click', () => {
            if (!isEditing) {
                enterEditMode();
            } else {
                exitEditMode(true);
            }
        });

        btnBatal.addEventListener('click', () => exitEditMode(false));

        // ─── Toast ────────────────────────────────────────────────
        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2800);
        }
    </script>
<?php require __DIR__ . '/footer.php'; ?>