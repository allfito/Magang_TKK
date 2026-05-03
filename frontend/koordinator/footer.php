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

    <!-- Modal Plotting -->
    <div id="modal-plotting" class="modal-overlay" onclick="tutupModalPlotOverlay(event)">
        <div class="modal-box modal-plot-box">
            <div class="modal-header">
                <h3 id="plot-modal-title">Plotting Kelompok</h3>
                <button class="modal-close" onclick="tutupModalPlotBtn()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-info-strip">
                    <div class="modal-info-item">
                        <span class="modal-info-label">Kelompok</span>
                        <span class="modal-info-val" id="pKelompok">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="modal-info-label">Ketua</span>
                        <span class="modal-info-val" id="pKetua">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="modal-info-label">Anggota</span>
                        <span class="modal-info-val" id="pAnggota">-</span>
                    </div>
                </div>

                <!-- Lokasi Magang -->
                <div class="plot-field">
                    <label for="plot-lokasi">Lokasi Magang <span class="required">*</span></label>
                    <select id="plot-lokasi" class="plot-select">
                        <option value="">-- Pilih Lokasi --</option>
                        <option>PT. Telkom Indonesia</option>
                        <option>PT. Pertamina Digital</option>
                        <option>CV. Nusantara Tech</option>
                        <option>PT. XYZ Solusi Digital</option>
                        <option>PT. Bank BRI Tbk</option>
                        <option>CV. Kreatif Media</option>
                        <option>PT. Astra International</option>
                        <option>Dinas Kominfo Kota Malang</option>
                    </select>
                </div>

                <!-- Dosen Pembimbing -->
                <div class="plot-field">
                    <label for="plot-dosen">Dosen Pembimbing <span class="required">*</span></label>
                    <select id="plot-dosen" class="plot-select" onchange="tampilInfoDosen()">
                        <option value="">-- Pilih Dosen Pembimbing --</option>
                        <option>Dr. Budi Santoso, M.Kom</option>
                        <option>Ir. Siti Rahayu, M.T</option>
                        <option>Prof. Ahmad Fauzi, Ph.D</option>
                        <option>Dra. Rina Wulandari, M.Si</option>
                        <option>Dr. Hendra Kurniawan, M.Kom</option>
                        <option>Ir. Dewi Lestari, M.T</option>
                        <option>Dr. Fajar Nugroho, M.Sc</option>
                        <option>Drs. Yusuf Hidayat, M.Pd</option>
                    </select>
                    <!-- Info beban dosen -->
                    <div id="dosen-info-box" class="dosen-info-box" style="display:none;">
                        <span class="dosen-info-icon">&#128100;</span>
                        <span id="dosen-info-text"></span>
                    </div>
                </div>

                <p id="plot-error" style="color:#EA5455;font-size:12px;margin-top:4px;display:none;">Harap pilih lokasi
                    dan dosen pembimbing.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-batal-modal" onclick="tutupModalPlotBtn()">Batal</button>
                <button class="btn-setuju-modal" onclick="simpanPlot()">&#10003; Simpan Plotting</button>
            </div>
        </div>
    </div>

    <!-- Modal Detail Kelompok -->
    <div id="modal-detail-kelompok" class="modal-overlay" onclick="tutupDetailOverlay(event)">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="detail-modal-title">Detail Kelompok</h3>
                <button class="modal-close" onclick="tutupDetail()">&times;</button>
            </div>
            <div class="modal-body" id="detail-modal-body">
                <!-- Diisi oleh JS -->
            </div>
            <div class="modal-footer">
                <button class="btn-batal-modal" onclick="tutupDetail()">Tutup</button>
                <button class="btn-setuju-modal" id="detail-to-plot-btn" onclick="lanjutPlotDariDetail()">&#9998; Plot
                    Kelompok Ini</button>
            </div>
        </div>
    </div>

    <script src="../../js/koordinator.js"></script>
</body>

</html>