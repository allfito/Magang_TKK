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
            }
            .title-header {
                font-size: 16pt;
                font-weight: bold;
                text-align: left;
                padding-bottom: 5px;
            }
            .subtitle-header {
                font-size: 14pt;
                font-weight: bold;
                text-align: left;
                padding-bottom: 20px;
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
            .status-disetujui {
                background-color: #10B981;
                color: #FFFFFF;
                text-align: center;
                font-weight: bold;
            }
            .status-menunggu {
                background-color: #FDBA74;
                color: #000000;
                text-align: center;
                font-weight: bold;
            }
            .status-belum {
                background-color: #FDE047;
                color: #000000;
                text-align: center;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <td colspan="10" class="title-header">PENGAJUAN TEMPAT MAGANG</td>
            </tr>
            <tr>
                <td colspan="10" class="subtitle-header">TA 2026/2027 - ANGKATAN 2024</td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td colspan="4" style="text-align: right; font-style: italic; font-size: 9pt;">untuk acc dilakukan oleh korbid</td>
            </tr>
            <thead>
                <tr>
                    <th class="table-header">Jumlah MHS</th>
                    <th class="table-header">Nomoa Kelompok</th>
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
                <?php foreach ($data as $groupIndex => $row): ?>
                    <?php 
                        $names = explode(', ', $row['nama_mahasiswa'] ?? '');
                        $nims = explode(', ', $row['nim'] ?? '');
                        $phones = explode(', ', $row['no_hp'] ?? '');
                        $rowCount = count($names);
                        
                        $googleMapsLink = KoordinatorHelper::generateGoogleMapsLink($row['latitude'], $row['longitude'], $row['alamat_lengkap']);
                        
                        $statusClass = 'status-belum';
                        $statusText = 'Pengurusan Surat Pengantar';
                        
                        if ($row['status_proposal'] === 'disetujui') {
                            $statusClass = 'status-disetujui';
                            $statusText = 'Surat Penerimaan Magang';
                        } elseif ($row['status_proposal'] === 'menunggu') {
                            $statusClass = 'status-menunggu';
                            $statusText = 'Pengiriman Proposal';
                        }
                        
                        $cpInfo = $row['cp_nama'] . " (" . $row['cp_tlp'] . ")";
                    ?>
                    
                    <?php for ($i = 0; $i < $rowCount; $i++): ?>
                        <tr>
                            <?php if ($i === 0): ?>
                                <td class="cell-data text-center" rowspan="<?= $rowCount ?>"><?= $row['jumlah_mhs'] ?></td>
                                <td class="cell-data text-center" rowspan="<?= $rowCount ?>"><?= $groupIndex + 1 ?></td>
                            <?php endif; ?>

                            <td class="cell-data"><?= htmlspecialchars($names[$i] ?? '-') ?></td>
                            <td class="cell-data text-center"><?= htmlspecialchars($nims[$i] ?? '-') ?></td>
                            <td class="cell-data"><?= htmlspecialchars($phones[$i] ?? '-') ?></td>

                            <?php if ($i === 0): ?>
                                <td class="cell-data" rowspan="<?= $rowCount ?>"><?= htmlspecialchars($row['lokasi_magang']) ?></td>
                                <td class="cell-data" rowspan="<?= $rowCount ?>"><?= htmlspecialchars($row['alamat_lengkap']) ?></td>
                                <td class="cell-data" rowspan="<?= $rowCount ?>">
                                    <?php if ($googleMapsLink !== '-'): ?>
                                        <a href="<?= $googleMapsLink ?>"><?= $googleMapsLink ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="cell-data <?= $statusClass ?>" rowspan="<?= $rowCount ?>">
                                    <?= $statusText ?>
                                </td>
                                <td class="cell-data" rowspan="<?= $rowCount ?>">
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
