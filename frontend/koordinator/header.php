<?php require_once __DIR__ . '/../../backend/helpers/KoordinatorHelper.php';
KoordinatorHelper::requireLogin(); 

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/koordinator.css">
    
</head>

<body>
    <div class="dashboard-layout">

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a class="logo-container">
                    <img src="../../assets/logo-jti-new.svg" alt="Logo JTI">
            </a>

                <button class="hamburger-btn" onclick="toggleSidebar()">
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="sidebar-nav" id="sidebar-nav">
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

            <div class="sidebar-footer" id="sidebar-footer">
                <a href="../../backend/auth/logout.php" class="nav-item logout-btn">Logout</a>
            </div>
        </aside>
        
        <script>
            function toggleSidebar() {
                document.getElementById('sidebar-nav').classList.toggle('mobile-open');
                document.getElementById('sidebar-footer').classList.toggle('mobile-open');
            }
        </script>

        <!-- Main Content -->
        <main class="main-content">