<?php require_once __DIR__ . '/functions.php'; 

// Detect current page
$currentPage = basename($_SERVER['PHP_SELF']);
$isVerifikasiPage = in_array($currentPage, ['verifikasi_lokasi.php', 'verifikasi_proposal.php', 'verifikasi_berkas.php', 'verifikasi_bukti.php']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Koordinator Bidang - SIMM</title>
    <meta name="description"
        content="Dashboard koordinator bidang untuk sistem manajemen magang JTI - monitoring kelompok, verifikasi, dan plotting.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/koordinator.css">
</head>

<body>
    <div class="dashboard-layout">

        <!-- Sidebar -->
        <aside class="sidebar">
            <a class="logo-container">
                <div class="logo-icons">
                    <div class="logo-icon y"></div>
                    <div class="logo-icon b"></div>
                    <div class="logo-icon c"></div>
                </div>
                <div class="logo-text-inner">
                    <span class="logo-text-jti">JTI</span>
                    <span class="logo-text-desc">JURUSAN<br>TEKNOLOGI<br>INFORMASI</span>
                </div>
            </a>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" id="nav-dashboard">Dashboard</a>

                <!-- Verifikasi dengan submenu -->
                <div class="nav-group" id="nav-verifikasi-group">
                    <div class="nav-item nav-parent <?= $isVerifikasiPage ? 'active' : '' ?>" id="nav-verifikasi-toggle" onclick="toggleVerifikasi()">
                        <span>Verifikasi</span>
                        <span class="nav-arrow <?= $isVerifikasiPage ? 'open' : '' ?>" id="verifikasi-arrow">&#x276F;</span>
                    </div>
                    <div class="nav-submenu <?= $isVerifikasiPage ? 'open' : '' ?>" id="verifikasi-submenu">
                        <a href="verifikasi_lokasi.php" class="nav-subitem <?= $currentPage === 'verifikasi_lokasi.php' ? 'active-sub' : '' ?>">Lokasi Magang</a>
                        <a href="verifikasi_proposal.php" class="nav-subitem <?= $currentPage === 'verifikasi_proposal.php' ? 'active-sub' : '' ?>">Proposal</a>
                        <a href="verifikasi_berkas.php" class="nav-subitem <?= $currentPage === 'verifikasi_berkas.php' ? 'active-sub' : '' ?>">Berkas</a>
                        <a href="verifikasi_bukti.php" class="nav-subitem <?= $currentPage === 'verifikasi_bukti.php' ? 'active-sub' : '' ?>">Bukti Diterima</a>
                    </div>
                </div>

                <a href="plotting.php" class="nav-item <?= $currentPage === 'plotting.php' ? 'active' : '' ?>" id="nav-plotting">Plotting</a>

                <a href="data_lengkap.php" class="nav-item <?= $currentPage === 'data_lengkap.php' ? 'active' : '' ?>" id="nav-data-lengkap">Data Lengkap</a>
            </nav>

            <div class="sidebar-footer">
                <a href="../../backend/auth/logout.php" class="nav-item logout-btn">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">