        </main>
    </div>

    <!-- Modal Verifikasi -->
    <div id="modal-verifikasi" class="modal-overlay" onclick="tutupModal(event)">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Detail Verifikasi</h3>
                <button class="modal-close" onclick="tutupModalBtn()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-row"><span class="modal-label">Nama Kelompok</span><span class="modal-val"
                        id="mKelompok">-</span></div>
                <div class="modal-row"><span class="modal-label">Ketua</span><span class="modal-val"
                        id="mKetua">-</span></div>
                <div class="modal-row"><span class="modal-label">Jenis Verifikasi</span><span class="modal-val"
                        id="mJenis">-</span></div>
                <div class="modal-row"><span class="modal-label">Status Saat Ini</span><span class="modal-val"
                        id="mStatus">-</span></div>
                <div class="modal-catatan">
                    <label for="catatan-modal">Catatan (opsional)</label>
                    <textarea id="catatan-modal" placeholder="Tulis catatan untuk kelompok ini..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-tolak-modal" onclick="aksiModal('tolak')">Tolak</button>
                <button class="btn-setuju-modal" onclick="aksiModal('setuju')">Setujui</button>
            </div>
        </div>
    </div>

    <!-- Style for Plotting & Detail Modals (Matches Premium Design) -->
    <!-- Modal Plotting -->
    <div id="modal-plotting" class="modal-overlay" onclick="tutupModalPlotOverlay(event)">
        <div class="plot-modal-box">
            <div class="plot-modal-header">
                <h3 id="plot-modal-title"><i class="fas fa-map-marker-alt"></i>Plotting Kelompok</h3>
                <button type="button" class="plot-modal-close" onclick="tutupModalPlotBtn()">&times;</button>
            </div>
            <form method="POST" action="../../backend/actions/koordinator_plotting.php">
                <input type="hidden" name="kelompok_id" id="plot-kelompok-id">
                <div class="plot-modal-body">
                    <div class="plot-info-strip">
                        <div class="plot-info-item">
                            <span class="plot-info-label">Kelompok</span>
                            <span class="plot-info-val" id="pKelompok">-</span>
                        </div>
                        <div class="plot-info-item">
                            <span class="plot-info-label">Ketua</span>
                            <span class="plot-info-val" id="pKetua">-</span>
                        </div>
                        <div class="plot-info-item">
                            <span class="plot-info-label">Anggota</span>
                            <span class="plot-info-val" id="pAnggota">-</span>
                        </div>
                    </div>

                    <!-- Dosen Pembimbing -->
                    <div class="dosen-input-group">
                        <label for="plot-dosen">Dosen Pembimbing <span class="required">*</span></label>
                        <input type="text" id="plot-dosen" name="dosen_pembimbing" class="dosen-text-input" list="dosen-list" placeholder="Ketik atau pilih dosen pembimbing..." oninput="tampilInfoDosen()" required>
                        <datalist id="dosen-list">
                            <?php 
                            $allDosen = KoordinatorHelper::getAllDosen();
                            foreach ($allDosen as $d) {
                                echo '<option value="' . htmlspecialchars($d['nama']) . '">';
                            }
                            ?>
                        </datalist>
                        <!-- Info beban dosen -->
                        <div id="dosen-info-box" class="dosen-info-box" style="display:none; margin-top: 12px; background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #4338CA; display: flex; align-items: center; gap: 8px;">
                            <span class="dosen-info-icon">&#128100;</span>
                            <span id="dosen-info-text"></span>
                        </div>
                    </div>

                    <p id="plot-error" style="color:#EF4444;font-size:12px;margin-top:8px;display:none;font-weight:600;"><i class="fas fa-exclamation-circle"></i> Harap pilih dosen pembimbing.</p>
                </div>
                <div class="plot-modal-footer">
                    <button type="button" class="plot-btn-batal" onclick="tutupModalPlotBtn()">Batal</button>
                    <button type="submit" class="plot-btn-submit"><i class="fas fa-check"></i>Simpan Plotting</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Kelompok -->
    <div id="modal-detail-kelompok" class="modal-overlay" onclick="tutupDetailOverlay(event)">
        <div class="detail-modal-box">
            <div class="detail-modal-header">
                <h3 id="detail-modal-title"><i class="fas fa-info-circle"></i>Detail Kelompok</h3>
                <button type="button" class="detail-modal-close" onclick="tutupDetail()">&times;</button>
            </div>
            <div class="detail-modal-body" id="detail-modal-body" style="padding: 24px;">
                <!-- Diisi oleh JS -->
            </div>
            <div class="detail-modal-footer">
                <button type="button" class="detail-btn-batal" onclick="tutupDetail()">Tutup</button>
                <button type="button" class="detail-btn-submit" id="detail-to-plot-btn" onclick="lanjutPlotDariDetail()">&#9998; Plot Kelompok Ini</button>
            </div>
        </div>
    </div>

    <!-- Style for Tambah Dosen Modal (Matches Screenshot Design) -->
    <!-- Modal Tambah Dosen -->
    <div id="modal-tambah-dosen" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('open')">
        <div class="dosen-modal-box">
            <div class="dosen-modal-header">
                <h3><i class="fas fa-user-plus"></i>Tambah Dosen Baru</h3>
                <button type="button" class="dosen-modal-close" onclick="document.getElementById('modal-tambah-dosen').classList.remove('open')">&times;</button>
            </div>
            <form method="POST" action="../../backend/actions/koordinator_tambah_dosen.php">
                <div class="dosen-modal-body">
                    <div class="dosen-input-group">
                        <label>Nama Lengkap & Gelar <span class="required">*</span></label>
                        <input type="text" name="nama_dosen" class="dosen-text-input" required placeholder="Contoh: Dr. Budi Santoso, M.Kom">
                        <span class="dosen-helper-text">Pastikan penulisan gelar sudah benar untuk keperluan administrasi.</span>
                    </div>
                    <div class="dosen-input-group">
                        <label>NIP <span class="required">*</span></label>
                        <input type="text" name="nip_dosen" class="dosen-text-input" required placeholder="Contoh: 196804201994031002" minlength="18" maxlength="18" pattern="[0-9]{18}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="NIP harus terdiri dari tepat 18 digit angka">
                        <span class="dosen-helper-text">NIP wajib diisi dan harus terdiri dari tepat 18 digit angka.</span>
                    </div>
                    <div class="dosen-input-group">
                        <label>No. Telepon <span class="required">*</span></label>
                        <input type="text" name="tlp_dosen" class="dosen-text-input" required placeholder="Contoh: 081234567890" minlength="10" maxlength="13" pattern="08[0-9]{8,11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Nomor telepon harus diawali 08 dan terdiri dari 10-13 digit angka">
                        <span class="dosen-helper-text">Nomor telepon wajib diisi dan harus berupa angka (10-13 digit).</span>
                    </div>
                </div>
                <div class="dosen-modal-footer">
                    <button type="button" class="dosen-btn-batal" onclick="document.getElementById('modal-tambah-dosen').classList.remove('open')">Batal</button>
                    <button type="submit" class="dosen-btn-submit"><i class="fas fa-plus"></i>Tambah Dosen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Plotting -->
    <div id="modal-hapus-plot" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('open')">
        <div class="dosen-modal-box" style="max-width: 450px;">
            <div class="dosen-modal-header" style="background: #EF4444; border-bottom-color: #FCA5A5;">
                <h3><i class="fas fa-trash-alt"></i>Hapus Plotting Dosen</h3>
                <button type="button" class="dosen-modal-close" onclick="document.getElementById('modal-hapus-plot').classList.remove('open')">&times;</button>
            </div>
            <form method="POST" action="../../backend/actions/koordinator_hapus_plotting.php">
                <input type="hidden" name="kelompok_id" id="hapus-plot-kelompok-id">
                <div class="dosen-modal-body" style="padding: 24px;">
                    <p style="font-size: 14px; color: #1E293B; line-height: 1.5; margin: 0 0 12px 0;">Apakah Anda yakin ingin menghapus plotting dosen pembimbing untuk kelompok <strong id="hapus-plot-kelompok-nama" style="color: #EF4444;">-</strong>?</p>
                    <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #B91C1C; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exclamation-triangle" style="color: #EF4444; font-size: 14px;"></i>
                        <span>Tindakan ini akan mengosongkan dosen pembimbing kelompok tersebut.</span>
                    </div>
                </div>
                <div class="dosen-modal-footer">
                    <button type="button" class="dosen-btn-batal" onclick="document.getElementById('modal-hapus-plot').classList.remove('open')">Batal</button>
                    <button type="submit" class="dosen-btn-submit" style="background: #EF4444; color: white;"><i class="fas fa-trash-alt"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Dosen -->
    <div id="modal-hapus-dosen" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('open')">
        <div class="dosen-modal-box" style="max-width: 450px;">
            <div class="dosen-modal-header" style="background: #EF4444; border-bottom-color: #FCA5A5;">
                <h3><i class="fas fa-user-minus"></i>Hapus Dosen Pembimbing</h3>
                <button type="button" class="dosen-modal-close" onclick="document.getElementById('modal-hapus-dosen').classList.remove('open')">&times;</button>
            </div>
            <form method="POST" action="../../backend/actions/koordinator_hapus_dosen.php">
                <input type="hidden" name="dosen_id" id="hapus-dosen-id">
                <div class="dosen-modal-body" style="padding: 24px;">
                    <p style="font-size: 14px; color: #1E293B; line-height: 1.5; margin: 0 0 12px 0;">Apakah Anda yakin ingin menghapus dosen <strong id="hapus-dosen-nama" style="color: #EF4444;">-</strong> dari sistem?</p>
                    <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #B91C1C; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exclamation-triangle" style="color: #EF4444; font-size: 14px;"></i>
                        <span>Dosen yang sedang membimbing kelompok aktif tidak dapat dihapus.</span>
                    </div>
                </div>
                <div class="dosen-modal-footer">
                    <button type="button" class="dosen-btn-batal" onclick="document.getElementById('modal-hapus-dosen').classList.remove('open')">Batal</button>
                    <button type="submit" class="dosen-btn-submit" style="background: #EF4444; color: white;"><i class="fas fa-trash-alt"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6DQD021o6FfQ2z9F3/jzQOf/0C1CmZ5l5q2Q8Qw9TGTg" crossorigin="anonymous"></script>
    <script src="../../js/koordinator.js"></script>
</body>

</html>