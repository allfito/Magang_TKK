<?php
include 'functions.php';

// Check if export is requested
if ($_GET['action'] === 'export_excel') {
    $data = getCompleteGroupsData();
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="Data_Magang_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    // Start output
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    
    // Create table with headers
    echo "MAGANG TA 2026/2027\n";
    echo "TKK ANGKATAN 2024\n\n";
    
    echo "Jumlah MHS\t";
    echo "Nomor Kelompok\t";
    echo "Nama Mahasiswa\t";
    echo "NIM\t";
    echo "NO HP mahasiswa\t";
    echo "Lokasi Magang\t";
    echo "Alamat Lengkap\t";
    echo "Link Google maps\t";
    echo "Progress Pengajuan Proposal\t";
    echo "NAMA DAN KONTAK PERSON\n";
    
    // Output data rows
    foreach ($data as $row) {
        $googleMapsLink = generateGoogleMapsLink($row['latitude'], $row['longitude']);
        $proposalStatus = ucfirst(str_replace('_', ' ', $row['status_proposal']));
        $kontakPerson = $row['ketua_nama'] . ' (' . $row['kontak_ketua'] . ', ' . $row['email_ketua'] . ')';
        
        echo $row['jumlah_mhs'] . "\t";
        echo $row['kelompok_nama'] . "\t";
        echo $row['nama_mahasiswa'] . "\t";
        echo $row['nim'] . "\t";
        echo $row['no_hp'] . "\t";
        echo $row['lokasi_magang'] . "\t";
        echo $row['alamat_lengkap'] . "\t";
        echo $googleMapsLink . "\t";
        echo $proposalStatus . "\t";
        echo $kontakPerson . "\n";
    }
    
    exit;
}
?>
