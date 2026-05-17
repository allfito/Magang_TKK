<?php
require_once __DIR__ . '/../../backend/helpers/KoordinatorHelper.php';

// Check if export is requested
if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    $data = KoordinatorHelper::getCompleteGroupsData();
    
    $filename = "Data_Magang_" . date('Y-m-d_H-i-s') . ".xls";
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Output HTML Table for Excel
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
        <style>
            .table-header {
                background-color: #E2E8F0;
                color: #000000;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
                border: 0.5pt solid #000000;
                font-family: Calibri, sans-serif;
            }
            .title-header {
                font-size: 14pt;
                font-weight: bold;
                text-align: center;
                padding-bottom: 5px;
                font-family: Calibri, sans-serif;
            }
            .subtitle-header {
                font-size: 12pt;
                font-weight: bold;
                text-align: center;
                padding-bottom: 20px;
                font-family: Calibri, sans-serif;
            }
            .cell-data {
                vertical-align: middle;
                border: 0.5pt solid #000000;
                padding: 5px;
                font-family: Calibri, sans-serif;
            }
            .text-center {
                text-align: center;
            }
            .status-penerimaan {
                background-color: #4ADE80 !important;
                color: #000000 !important;
                text-align: center;
                font-weight: bold;
            }
            .status-pengiriman {
                background-color: #FB923C !important;
                color: #000000 !important;
                text-align: center;
                font-weight: bold;
            }
            .status-revisi {
                background-color: #FACC15 !important;
                color: #000000 !important;
                text-align: center;
                font-weight: bold;
            }
            .status-acc {
                background-color: #60A5FA !important;
                color: #000000 !important;
                text-align: center;
                font-weight: bold;
            }
            .status-pengurusan {
                background-color: #FB923C !important;
                color: #000000 !important;
                text-align: center;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <td colspan="10" class="title-header">MAGANG TA 2026/2027</td>
            </tr>
            <tr>
                <td colspan="10" class="subtitle-header">TKK ANGKATAN 2024</td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td colspan="4" style="text-align: right; font-style: italic; font-size: 9pt; font-family: Calibri, sans-serif;">untuk acc dilakukan oleh korbid</td>
            </tr>
            <thead>
                <tr>
                    <th class="table-header">Jumlah MHS</th>
                    <th class="table-header">Nama Kelompok</th>
                    <th class="table-header">Nama Mahasiswa</th>
                    <th class="table-header">NIM</th>
                    <th class="table-header">NO HP mahasiswa</th>
                    <th class="table-header">Lokasi Magang (tuliskan nama PT atau CV dengan benar)</th>
                    <th class="table-header">Alamat Lengkap</th>
                    <th class="table-header">Link Google maps</th>
                    <th class="table-header">Progress Pengajuan Proposal</th>
                    <th class="table-header">NAMA DAN KONTAK PERSON</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $globalStudentCounter = 1;
                foreach ($data as $groupIndex => $row): 
                ?>
                    <?php 
                        $names = explode(', ', $row['nama_mahasiswa'] ?? '');
                        $nims = explode(', ', $row['nim'] ?? '');
                        $phones = explode(', ', $row['no_hp'] ?? '');
                        $rowCount = count($names);
                        
                        $googleMapsLink = KoordinatorHelper::generateGoogleMapsLink($row['latitude'], $row['longitude'], $row['alamat_lengkap']);
                        
                        // Status mapping with new schema logic
                        $statusClass = 'status-pengurusan';
                        $statusText = 'Pengurusan Surat Pengantar';
                        
                        if ($row['status_proposal'] === 'disetujui') {
                            $statusClass = 'status-penerimaan';
                            $statusText = 'Surat Penerimaan Magang';
                        } elseif ($row['status_proposal'] === 'menunggu') {
                            $statusClass = 'status-pengiriman';
                            $statusText = 'Pengiriman Proposal';
                        } elseif ($row['status_proposal'] === 'ditolak' || (isset($row['status_lokasi']) && $row['status_lokasi'] === 'ditolak')) {
                            $statusClass = 'status-revisi';
                            $statusText = 'REVISI TEMPAT';
                        } elseif (isset($row['status_lokasi']) && $row['status_lokasi'] === 'disetujui') {
                            $statusClass = 'status-acc';
                            $statusText = 'ACC Pembuatan Proposal';
                        }
                        
                        $cpInfo = $row['cp_nama'] . " (" . $row['cp_tlp'] . ")";
                        
                        // Alternate background color per group for clean visual separating
                        $groupBg = ($groupIndex % 2 === 1) ? 'background-color: #F1F5F9;' : 'background-color: #FFFFFF;';
                    ?>
                    
                    <?php for ($i = 0; $i < $rowCount; $i++): ?>
                        <tr>
                            <!-- Column 1: Jumlah MHS (Running student counter) - NOT merged! -->
                            <td class="cell-data text-center" style="<?= $groupBg ?>"><?= $globalStudentCounter++ ?></td>

                            <!-- Column 2: Nama Kelompok (group index number) - MERGED -->
                            <?php if ($i === 0): ?>
                                <td class="cell-data text-center" style="<?= $groupBg ?>" rowspan="<?= $rowCount ?>"><?= $groupIndex + 1 ?></td>
                            <?php endif; ?>

                            <!-- Column 3, 4, 5: Student Info - NOT merged! -->
                            <td class="cell-data" style="<?= $groupBg ?>"><?= htmlspecialchars($names[$i] ?? '-') ?></td>
                            <td class="cell-data text-center" style="<?= $groupBg ?>"><?= htmlspecialchars($nims[$i] ?? '-') ?></td>
                            <td class="cell-data" style="<?= $groupBg ?>"><?= htmlspecialchars($phones[$i] ?? '-') ?></td>

                            <!-- Column 6 to 10: Group Info - MERGED -->
                            <?php if ($i === 0): ?>
                                <td class="cell-data" style="<?= $groupBg ?>" rowspan="<?= $rowCount ?>"><?= htmlspecialchars($row['lokasi_magang']) ?></td>
                                <td class="cell-data" style="<?= $groupBg ?>" rowspan="<?= $rowCount ?>"><?= htmlspecialchars($row['alamat_lengkap']) ?></td>
                                <td class="cell-data" style="<?= $groupBg ?>" rowspan="<?= $rowCount ?>">
                                    <?php if ($googleMapsLink !== '-'): ?>
                                        <a href="<?= htmlspecialchars($googleMapsLink) ?>"><?= htmlspecialchars($googleMapsLink) ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="cell-data <?= $statusClass ?>" rowspan="<?= $rowCount ?>">
                                    <?= htmlspecialchars($statusText) ?>
                                </td>
                                <td class="cell-data" style="<?= $groupBg ?>" rowspan="<?= $rowCount ?>">
                                    <?= htmlspecialchars($cpInfo) ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endfor; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}
?>
