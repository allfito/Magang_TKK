/* ============================================================
   KOORDINATOR BIDANG - JavaScript
   Handles: sidebar submenu, modal verifikasi, plotting, detail kelompok
   ============================================================ */

// Simpan referensi baris yang sedang dibuka di modal
let currentRow = null;

/* --------------------------------------------------
   SIDEBAR SUBMENU TOGGLE (Verifikasi)
   -------------------------------------------------- */
function toggleVerifikasi() {
    const submenu = document.getElementById('verifikasi-submenu');
    const arrow = document.getElementById('verifikasi-arrow');
    submenu.classList.toggle('open');
    arrow.classList.toggle('open');
}


function tutupModal(event) {
    // Tutup hanya jika klik di luar modal-box
    if (event.target === document.getElementById('modal-verifikasi')) {
        document.getElementById('modal-verifikasi').classList.remove('open');
    }
}

function tutupModalBtn() {
    document.getElementById('modal-verifikasi').classList.remove('open');
}

function aksiModal(tindakan) {
    if (!currentRow) return;
    const badgeEl = currentRow.querySelector('.badge');

    if (tindakan === 'setuju') {
        badgeEl.className = 'badge badge-success-status';
        badgeEl.textContent = 'Disetujui';
        showToast('Kelompok berhasil disetujui!', 'success');
    } else {
        badgeEl.className = 'badge badge-danger';
        badgeEl.textContent = 'Ditolak';
        showToast('Kelompok telah ditolak.', 'danger');
    }

    document.getElementById('modal-verifikasi').classList.remove('open');
    currentRow = null;

    // Update counter stat cards
    updateStatCards();
}


/* --------------------------------------------------
   SORT PAGE (Dropdown pengurutan)
   -------------------------------------------------- */
function changeSortPage(sortValue) {
    // Debug: console.log('Sort value:', sortValue);
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    // Debug: console.log('New URL:', url.toString());
    window.location.href = url.toString();
}

/* --------------------------------------------------
   MODAL PLOTTING - Lokasi + Dosen Pembimbing
   -------------------------------------------------- */
let currentPlotRow   = null;
let detailSourceRow  = null; // baris yang dibuka dari modal detail

// Hitung beban dosen dari tabel saat ini
function hitungBebanDosen() {
    const beban = {};
    document.querySelectorAll('#tbody-plotting tr').forEach(row => {
        const dosenCell = row.querySelector('.col-dosen');
        if (dosenCell && !dosenCell.querySelector('em')) {
            const nama = dosenCell.textContent.trim();
            if (nama) beban[nama] = (beban[nama] || 0) + 1;
        }
    });
    return beban;
}

function bukaModalPlot(btn, isEdit, kelompokId) {
    const row = btn.closest('tr');
    currentPlotRow = row;

    const cells = row.querySelectorAll('td');
    document.getElementById('plot-kelompok-id').value = kelompokId;
    document.getElementById('pKelompok').textContent = cells[0].textContent.trim();
    document.getElementById('pKetua').textContent    = cells[1].textContent.trim();
    document.getElementById('pAnggota').textContent  = cells[2].textContent.trim();
    document.getElementById('plot-error').style.display = 'none';
    document.getElementById('dosen-info-box').style.display = 'none';

    const dosenSel  = document.getElementById('plot-dosen');

    if (isEdit) {
        const dosenVal  = cells[3].textContent.trim();
        dosenSel.value = dosenVal;
        document.getElementById('plot-modal-title').textContent = 'Edit Plotting';
        tampilInfoDosen();
    } else {
        dosenSel.value  = '';
        document.getElementById('plot-modal-title').textContent = 'Plotting Kelompok';
    }

    document.getElementById('modal-plotting').classList.add('open');
}

function tampilInfoDosen() {
    const dosenVal  = document.getElementById('plot-dosen').value;
    const infoBox   = document.getElementById('dosen-info-box');
    const infoText  = document.getElementById('dosen-info-text');
    if (!dosenVal) { infoBox.style.display = 'none'; return; }

    const beban = hitungBebanDosen();
    // Jangan hitung baris yang sedang diedit sendiri
    let jumlah = beban[dosenVal] || 0;
    const editMode = document.getElementById('plot-modal-title').textContent === 'Edit Plotting';
    if (editMode && currentPlotRow) {
        const existing = currentPlotRow.querySelector('.col-dosen');
        if (existing && existing.textContent.trim() === dosenVal) jumlah = Math.max(jumlah - 1, 0);
    }

    let warna, label;
    if (jumlah === 0) { warna = '#28C76F'; label = 'Belum membimbing kelompok manapun — tersedia'; }
    else if (jumlah <= 2) { warna = '#FF9F43'; label = `Sudah membimbing ${jumlah} kelompok — masih tersedia`; }
    else { warna = '#EA5455'; label = `Sudah membimbing ${jumlah} kelompok — beban penuh`; }

    infoText.innerHTML = `<span style="color:${warna};font-weight:700;">${label}</span>`;
    infoBox.style.display = 'flex';
}


function tutupModalPlotBtn() {
    document.getElementById('modal-plotting').classList.remove('open');
}

function tutupModalPlotOverlay(event) {
    if (event.target === document.getElementById('modal-plotting')) {
        document.getElementById('modal-plotting').classList.remove('open');
    }
}

/* --------------------------------------------------
   MODAL DETAIL KELOMPOK
   -------------------------------------------------- */

function bukaDetailKelompok(btn) {
    const row    = btn.closest('tr');
    detailSourceRow = row;
    const cells  = row.querySelectorAll('td');
    const nama   = cells[0].textContent.trim();
    const ketua  = cells[1].textContent.trim();
    const anggotaCount = cells[2].textContent.trim();
    const dosen  = cells[3].querySelector('em') ? '-' : cells[3].textContent.trim();
    const status = cells[4].querySelector('.badge').textContent.trim();
    const isSelesai = status === 'Selesai';

    document.getElementById('detail-modal-title').innerHTML = `<i class="fas fa-users" style="margin-right:10px; color:#FFFFFF;"></i> ${nama}`;

    const membersAttr = btn.getAttribute('data-members');
    let members = [];
    try {
        if (membersAttr) {
            members = JSON.parse(membersAttr);
        }
    } catch(e) {
        console.error("Gagal parsing data-members:", e);
    }

    const anggotaHTML = members.map(m => {
        const isKetua = m.peran.toLowerCase() === 'ketua';
        const roleLabel = isKetua ? '<span style="background: #EEF2FF; color: #4F46E5; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; margin-left: 8px;">Ketua</span>' : '';
        return `<div class="detail-member-item" style="padding: 10px 14px; border-bottom: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: #334155; font-family: 'Inter', sans-serif;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color:#6366F1;"><i class="fas fa-user-graduate"></i></span>
                <span><strong>${m.nama}</strong> <span style="color: #64748B;">(NIM: ${m.nim})</span></span>
            </div>
            <div>${roleLabel}</div>
        </div>`;
    }).join('');

    document.getElementById('detail-modal-body').innerHTML = `
        <div class="detail-info-grid">
            <div class="detail-info-block">
                <span class="detail-label">Ketua Kelompok</span>
                <span class="detail-val">${ketua}</span>
            </div>
            <div class="detail-info-block">
                <span class="detail-label">Total Anggota</span>
                <span class="detail-val">${anggotaCount} Mahasiswa</span>
            </div>
            <div class="detail-info-block">
                <span class="detail-label">Dosen Pembimbing</span>
                <span class="detail-val">${dosen === '-' ? '<em style="color:#94A3B8; font-weight:normal;">Belum Diplot</em>' : dosen}</span>
            </div>
            <div class="detail-info-block">
                <span class="detail-label">Status Plotting</span>
                <span class="detail-val">
                    <span class="badge-status ${isSelesai ? 'badge-disetujui' : 'badge-menunggu'}" style="padding:2px 10px; font-size:10px; border-radius:4px;">
                        ${isSelesai ? '✓ Terverifikasi' : '⧖ Menunggu'}
                    </span>
                </span>
            </div>
        </div>
        <div class="detail-anggota-section">
            <h4 class="detail-anggota-title"><i class="fas fa-list-ul" style="margin-right:8px;"></i>Daftar Anggota Kelompok</h4>
            <div class="detail-member-list">${anggotaHTML || '<em style="color:#94A3B8; font-size:12px;">Data anggota belum tersedia di sistem ini.</em>'}</div>
        </div>
    `;

    // Update Plot button text
    const plotBtn = document.getElementById('detail-to-plot-btn');
    if (plotBtn) {
        plotBtn.innerHTML = isSelesai ? '<i class="fas fa-edit"></i> Edit Plotting' : '<i class="fas fa-plus"></i> Plot Kelompok';
    }

    document.getElementById('modal-detail-kelompok').classList.add('open');
}

function lanjutPlotDariDetail() {
    if (!detailSourceRow) return;
    tutupDetail();
    const isSelesai = detailSourceRow.querySelector('.badge').textContent.trim() === 'Selesai';
    const plotBtn   = isSelesai
        ? detailSourceRow.querySelector('.btn-edit-plot')
        : detailSourceRow.querySelector('.btn-plot');
    if (plotBtn) plotBtn.click();
}

function tutupDetail() {
    document.getElementById('modal-detail-kelompok').classList.remove('open');
}

function tutupDetailOverlay(event) {
    if (event.target === document.getElementById('modal-detail-kelompok')) tutupDetail();
}

/* --------------------------------------------------
   FILTER & SEARCH TABEL PLOTTING
   -------------------------------------------------- */
let filterStatusAktif = 'all';

function filterStatus(status, btn) {
    filterStatusAktif = status;
    document.querySelectorAll('.plot-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterTabelPlotting();
}

function filterTabelPlotting() {
    const q = (document.getElementById('plot-search')?.value || '').toLowerCase();
    document.querySelectorAll('#tbody-plotting tr').forEach(row => {
        const namaKelompok = row.cells[0].textContent.toLowerCase();
        const ketua        = row.cells[1].textContent.toLowerCase();
        const statusBadge  = row.cells[4].querySelector('.badge')?.textContent.trim().toLowerCase();

        const matchSearch = namaKelompok.includes(q) || ketua.includes(q);
        const matchStatus =
            filterStatusAktif === 'all'
            || (filterStatusAktif === 'selesai'  && statusBadge === 'selesai')
            || (filterStatusAktif === 'menunggu' && statusBadge === 'menunggu');

        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

/* --------------------------------------------------
   REKAP DOSEN PEMBIMBING
   -------------------------------------------------- */
function renderRekapDosen() {
    const container = document.getElementById('dosen-rekap-grid');
    if (!container) return;
    const beban = hitungBebanDosen();
    const MAX   = 3;

    const dosenNames = Object.keys(beban).filter(n => n && n !== '-' && n !== 'Belum ditentukan');
    
    const datalistOptions = document.querySelectorAll('#dosen-list option');
    const allDosenDB = Array.from(datalistOptions).map(opt => opt.value);
    
    const allDosen = [...new Set([...allDosenDB, ...dosenNames])];

    container.innerHTML = allDosen.map(nama => {
        const jml   = beban[nama] || 0;
        const pct   = Math.min((jml / MAX) * 100, 100);
        const warna = jml === 0 ? '#28C76F' : jml < MAX ? '#FF9F43' : '#EA5455';
        const label = jml === 0 ? 'Tersedia' : jml < MAX ? 'Sebagian' : 'Penuh';
        return `
            <div class="dosen-rekap-card">
                <div class="dosen-rekap-header">
                    <div class="dosen-avatar">${nama.charAt(0)}</div>
                    <div class="dosen-rekap-info">
                        <div class="dosen-rekap-nama">${nama}</div>
                        <div class="dosen-rekap-jml">${jml} kelompok dibimbing</div>
                    </div>
                    <span class="dosen-rekap-badge" style="background:${warna}20;color:${warna};border:1px solid ${warna}50">${label}</span>
                </div>
                <div class="dosen-progress-wrap">
                    <div class="dosen-progress-bar" style="width:${pct}%;background:${warna};"></div>
                </div>
                <div class="dosen-progress-label">${jml} / ${MAX} kapasitas</div>
            </div>
        `;
    }).join('');
}

/* --------------------------------------------------
   UPDATE STAT CARDS (hitung menunggu dari tabel)
   -------------------------------------------------- */
function updateStatCards() {
    const rows = document.querySelectorAll('#tabel-kelompok tbody tr');
    let menunggu = 0;
    rows.forEach(r => {
        const badge = r.querySelector('.badge');
        if (badge && badge.textContent.trim() === 'Menunggu') menunggu++;
    });
    // Update angka di stat card (indeks 0 = Kelompok Aktif, biarkan tetap)
    // Hanya contoh perubahan visual; bisa disesuaikan logika backend
}

/* --------------------------------------------------
   TOAST NOTIFICATION
   -------------------------------------------------- */
function showToast(message, type) {
    // Hapus toast lama jika ada
    const oldToast = document.getElementById('toast-notif');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'toast-notif';
    toast.textContent = message;

    const colors = {
        success: { bg: '#28C76F', color: '#fff' },
        danger:  { bg: '#EA5455', color: '#fff' },
        info:    { bg: '#00CFE8', color: '#fff' }
    };

    const c = colors[type] || colors.info;

    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '30px',
        right: '30px',
        background: c.bg,
        color: c.color,
        padding: '14px 24px',
        borderRadius: '10px',
        fontSize: '14px',
        fontWeight: '600',
        boxShadow: '0 6px 24px rgba(0,0,0,0.18)',
        zIndex: '9999',
        fontFamily: 'Inter, sans-serif',
        opacity: '0',
        transform: 'translateY(12px)',
        transition: 'opacity 0.25s, transform 0.25s'
    });

    document.body.appendChild(toast);

    // Animasi masuk
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    // Auto hilang setelah 3 detik
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(12px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* --------------------------------------------------
   INIT: highlight nav berdasarkan URL saat ini, render rekap dosen
   -------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    highlightCurrentNav();
    // Initialize search filters for all verification tables
    // Bukti
    const searchBukti = document.getElementById('search-bukti');
    if (searchBukti) {
        searchBukti.addEventListener('input', () => filterTable('#tabel-bukti', searchBukti.value));
    }
    // Lokasi
    const searchLokasi = document.getElementById('search-lokasi');
    if (searchLokasi) {
        searchLokasi.addEventListener('input', () => filterTable('#tabel-lokasi', searchLokasi.value));
    }
    // Proposal
    const searchProposal = document.getElementById('search-proposal');
    if (searchProposal) {
        searchProposal.addEventListener('input', () => filterTable('#tabel-proposal', searchProposal.value));
    }
    // Berkas (filter group cards)
    const searchBerkas = document.getElementById('search-berkas');
    if (searchBerkas) {
        searchBerkas.addEventListener('input', () => filterBerkasDetails(searchBerkas.value));
    }
    // Generic table filter function
    function filterTable(tableSelector, query) {
        const q = query.toLowerCase();
        const rows = document.querySelectorAll(`${tableSelector} tbody tr`);
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    }
    // Berkas details filter
    function filterBerkasDetails(query) {
        const q = query.toLowerCase();
        const cards = document.querySelectorAll('details.grupo-dropdown');
        cards.forEach(card => {
            const summary = card.querySelector('summary');
            const text = summary ? summary.textContent.toLowerCase() : '';
            card.style.display = text.includes(q) ? '' : 'none';
        });
    }
});

function highlightCurrentNav() {
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.nav-subitem').forEach(item => item.classList.remove('active-sub'));

    if (currentPath === 'dashboard.php') {
        document.getElementById('nav-dashboard').classList.add('active');
    } else if (currentPath === 'plotting.php') {
        document.getElementById('nav-plotting').classList.add('active');
    } else if (currentPath.startsWith('verifikasi')) {
        document.getElementById('nav-verifikasi-toggle').classList.add('active');
        document.getElementById('verifikasi-submenu').classList.add('open');
        document.getElementById('verifikasi-arrow').classList.add('open');
        const subItems = document.querySelectorAll('.nav-subitem');
        subItems.forEach(si => {
            const href = si.getAttribute('href');
            if (href && href.includes(currentPath)) {
                si.classList.add('active-sub');
            }
        });
    } else if (currentPath === 'data_lengkap.php') {
        const navDataLengkap = document.getElementById('nav-data-lengkap');
        if (navDataLengkap) {
            navDataLengkap.classList.add('active');
        }
    }
}


function konfirmasiHapusPlot(kelompokId, kelompokNama) {
    document.getElementById('hapus-plot-kelompok-id').value = kelompokId;
    document.getElementById('hapus-plot-kelompok-nama').textContent = kelompokNama;
    document.getElementById('modal-hapus-plot').classList.add('open');
}

function konfirmasiHapusDosen(dosenId, dosenNama, count) {
    if (count > 0) {
        alert(`Dosen "${dosenNama}" tidak dapat dihapus karena sedang aktif membimbing ${count} kelompok magang.`);
        return;
    }
    document.getElementById('hapus-dosen-id').value = dosenId;
    document.getElementById('hapus-dosen-nama').textContent = dosenNama;
    document.getElementById('modal-hapus-dosen').classList.add('open');
}
