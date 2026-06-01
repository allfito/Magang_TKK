<?php
require_once __DIR__ . '/../../backend/helpers/KoordinatorHelper.php';

// Check if export is requested
if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    $data = KoordinatorHelper::getCompleteGroupsData('pendaftaran');

    // ── Filter by angkatan ─────────────────────────────────────────────────
    $angkatanFilter = $_GET['angkatan'] ?? 'ALL';
    if ($angkatanFilter !== 'ALL') {
        $data = array_filter($data, function ($row) use ($angkatanFilter) {
            $nims = !empty($row['nim']) ? explode(', ', $row['nim']) : [];
            if (empty($nims) || (count($nims) === 1 && empty(trim($nims[0])))) {
                return true; // keep rows with no NIM
            }
            foreach ($nims as $nim) {
                $nim      = trim($nim);
                if (empty($nim)) continue;
                $clean    = preg_replace('/[^0-9]/', '', $nim);
                if (strlen($clean) < 2) continue;
                $area     = substr($clean, 2);
                $year     = 'unknown';
                if (preg_match('/2[0-9]/', $area, $m))  $year = '20' . $m[0];
                elseif (preg_match('/2[0-9]/', $clean, $m)) $year = '20' . $m[0];
                if ($year === $angkatanFilter) return true;
            }
            return false;
        });
        $data = array_values($data);
    }

    // ── Dynamic TA / filename ──────────────────────────────────────────────
    $taText = '2026/2027';
    if ($angkatanFilter !== 'ALL') {
        $reg    = (int)$angkatanFilter + 2;
        $taText = $reg . '/' . ($reg + 1);
    }
    $angLabel = ($angkatanFilter !== 'ALL') ? $angkatanFilter : 'ALL';
    $filename = 'Data_Magang_TA_' . str_replace('/', '-', $taText)
              . '_Angkatan_' . $angLabel
              . '_' . date('Y-m-d_H-i-s') . '.xls';

    // ── HTTP headers ───────────────────────────────────────────────────────
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8"/>
<!--[if gte mso 9]><xml>
<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
  <x:Name>Data Magang</x:Name>
  <x:WorksheetOptions>
    <x:DisplayGridlines/>
    <x:FreezePanes/>
    <x:FrozenNoSplit/>
    <x:SplitHorizontal>4</x:SplitHorizontal>
    <x:TopRowBottomPane>4</x:TopRowBottomPane>
    <x:ActivePane>2</x:ActivePane>
  </x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml><![endif]-->
<style>
/* ── Global ─────────────────────────────────────── */
body  { font-family: Calibri, sans-serif; font-size: 10pt; margin:0; }
table { border-collapse: collapse; }

/* ── Title / subtitle ───────────────────────────── */
.title {
    font-size    : 16pt;
    font-weight  : bold;
    text-align   : center;
    color        : #1A3557;
    height       : 28pt;
    padding      : 4pt 0;
}
.subtitle {
    font-size    : 12pt;
    font-weight  : bold;
    text-align   : center;
    color        : #2E74B5;
    height       : 20pt;
}
.note {
    font-size    : 9pt;
    font-style   : italic;
    color        : #888888;
    text-align   : right;
    height       : 13pt;
    padding-right: 6pt;
}
.blank { height: 8pt; }

/* ── Column header ──────────────────────────────── */
.th {
    background-color : #1F4E79;
    color            : #FFFFFF;
    font-weight      : bold;
    font-size        : 9pt;
    font-family      : Calibri, sans-serif;
    text-align       : center;
    vertical-align   : middle;
    border           : 1pt solid #144070;
    padding          : 5pt 4pt;
    white-space      : normal;
    word-wrap        : break-word;
    height           : 40pt;
}

/* ── Data cell (base) ───────────────────────────── */
.td {
    vertical-align : middle;
    border         : 0.5pt solid #9DC3E6;
    padding        : 3pt 5pt;
    font-size      : 10pt;
    font-family    : Calibri, sans-serif;
    white-space    : normal;
    word-wrap      : break-word;
    height         : 20pt;
}

/* Alignment helpers */
.c   { text-align: center; }
.l   { text-align: left;   }
.b   { font-weight: bold;  }
.mid { vertical-align: middle; }

/* Alternating row shading */
.ev  { background-color: #FFFFFF; }
.od  { background-color: #DEEAF1; }

/* ── Status cell colours ────────────────────────── */
.s-ok   { background-color:#70AD47; color:#1B3300; font-weight:bold; text-align:center; border:0.5pt solid #4E7A32; }
.s-org  { background-color:#F4B942; color:#4A2C00; font-weight:bold; text-align:center; border:0.5pt solid #C08000; }
.s-yel  { background-color:#FFE699; color:#4A3800; font-weight:bold; text-align:center; border:0.5pt solid #B8860B; }
.s-blu  { background-color:#9DC3E6; color:#1A2D4A; font-weight:bold; text-align:center; border:0.5pt solid #2E74B5; }
.s-red  { background-color:#FF7070; color:#5C0000; font-weight:bold; text-align:center; border:0.5pt solid #C00000; }
</style>
</head>
<body>
<table>
  <!-- Column widths (pt) -->
  <colgroup>
    <col style="width:34pt"/>   <!-- No. -->
    <col style="width:50pt"/>   <!-- No. Kelompok -->
    <col style="width:115pt"/>  <!-- Nama Kelompok -->
    <col style="width:145pt"/>  <!-- Nama Mahasiswa -->
    <col style="width:90pt"/>   <!-- NIM -->
    <col style="width:105pt"/>  <!-- NO HP -->
    <col style="width:145pt"/>  <!-- Lokasi Magang -->
    <col style="width:175pt"/>  <!-- Alamat Lengkap -->
    <col style="width:145pt"/>  <!-- Google Maps -->
    <col style="width:115pt"/>  <!-- Progress -->
    <col style="width:140pt"/>  <!-- Kontak Person -->
  </colgroup>

  <!-- ── Title section ─────────────────────────────── -->
  <tr><td colspan="11" class="title">MAGANG TA <?= htmlspecialchars($taText) ?></td></tr>
  <tr><td colspan="11" class="subtitle">TKK <?= ($angkatanFilter !== 'ALL') ? 'ANGKATAN ' . htmlspecialchars($angkatanFilter) : 'SEMUA ANGKATAN' ?></td></tr>
  <tr><td colspan="7" class="blank"></td><td colspan="4" class="note">untuk acc dilakukan oleh korbid</td></tr>

  <!-- ── Table header ──────────────────────────────── -->
  <thead>
  <tr>
    <th class="th">No.</th>
    <th class="th">No.&#10;Kelompok</th>
    <th class="th">Nama&#10;Kelompok</th>
    <th class="th">Nama Mahasiswa</th>
    <th class="th">NIM</th>
    <th class="th">NO HP&#10;Mahasiswa</th>
    <th class="th">Lokasi Magang&#10;(nama PT / CV)</th>
    <th class="th">Alamat Lengkap</th>
    <th class="th">Link Google Maps</th>
    <th class="th">Progress</th>
    <th class="th">Nama &amp; Kontak Person</th>
  </tr>
  </thead>

  <!-- ── Data rows ─────────────────────────────────── -->
  <tbody>
  <?php
  $counter = 1;
  foreach ($data as $gIdx => $row):
    $names  = !empty($row['nama_mahasiswa']) ? explode(', ', $row['nama_mahasiswa']) : ['-'];
    $nims   = !empty($row['nim'])            ? explode(', ', $row['nim'])            : ['-'];
    $phones = !empty($row['no_hp'])          ? explode(', ', $row['no_hp'])          : ['-'];
    $cnt    = max(1, count($names));

    $maps = KoordinatorHelper::generateGoogleMapsLink(
        $row['latitude'], $row['longitude'], $row['alamat_lengkap']
    );

    // Status
    $st = $row['status_progress'] ?: 'Pengurusan Surat Pengantar';
    $sc = 's-org'; // default
    if     ($st === 'Surat Penerimaan Magang')                                              $sc = 's-ok';
    elseif (in_array($st, ['ACC Pembuatan Proposal','Pengajuan Tempat']))                   $sc = 's-blu';
    elseif ($st === 'REVISI TEMPAT')                                                        $sc = 's-yel';
    elseif (in_array($st, ['DI TOLAK','Pengajuan di Tolak Lokasi','Ditolak Perusahaan']))  $sc = 's-red';

    // Contact person — store raw values; we'll escape in HTML output
    $cpName   = trim($row['cp_nama'] ?? '-');


    $bg = ($gIdx % 2 === 0) ? 'ev' : 'od';
  ?>
  <?php for ($i = 0; $i < $cnt; $i++):
        $nim   = htmlspecialchars(trim($nims[$i]   ?? '-'));
        $phone = htmlspecialchars(trim($phones[$i] ?? '-'));
        $name  = htmlspecialchars(trim($names[$i]  ?? '-'));
  ?>
  <tr>
    <!-- No. running -->
    <td class="td c b <?= $bg ?>"><?= $counter++ ?></td>

    <!-- No. Kelompok (merged) -->
    <?php if ($i === 0): ?>
    <td class="td c b <?= $bg ?>" rowspan="<?= $cnt ?>" style="font-size:13pt;"><?= $gIdx + 1 ?></td>
    <?php endif; ?>

    <!-- Nama Kelompok (merged) -->
    <?php if ($i === 0): ?>
    <td class="td c <?= $bg ?>" rowspan="<?= $cnt ?>"><?= htmlspecialchars($row['kelompok_nama']) ?></td>
    <?php endif; ?>

    <!-- Nama Mahasiswa -->
    <td class="td l <?= $bg ?>"><?= $name ?></td>

    <!-- NIM — x:str forces Excel to keep as text (prevents scientific notation) -->
    <td class="td c <?= $bg ?>" x:str><?= $nim ?></td>

    <!-- NO HP — x:str forces text -->
    <td class="td l <?= $bg ?>" x:str><?= $phone ?></td>

    <!-- Merged: Lokasi, Alamat, Maps, Progress, Kontak -->
    <?php if ($i === 0): ?>
    <td class="td l <?= $bg ?>" rowspan="<?= $cnt ?>"><?= htmlspecialchars($row['lokasi_magang'] ?? '-') ?></td>

    <td class="td l <?= $bg ?>" rowspan="<?= $cnt ?>"><?= htmlspecialchars($row['alamat_lengkap'] ?? '-') ?></td>

    <td class="td l <?= $bg ?>" rowspan="<?= $cnt ?>">
      <?php if ($maps !== '-'): ?>
        <a href="<?= htmlspecialchars($maps) ?>" style="color:#1A56A8;word-break:break-all;"><?= htmlspecialchars($maps) ?></a>
      <?php else: ?>-<?php endif; ?>
    </td>

    <td class="td mid <?= $sc ?>" rowspan="<?= $cnt ?>"><?= htmlspecialchars($st) ?></td>

    <!-- Kontak Person — hanya nama saja -->
    <td class="td l <?= $bg ?>" rowspan="<?= $cnt ?>"><?= htmlspecialchars($cpName) ?></td>
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
