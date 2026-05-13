<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'SIMM Mahasiswa';
$activePage = $activePage ?? '';
$extraHead = $extraHead ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <?= $extraHead ?>
</head>
<body>
    <div class="dashboard-layout">
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
                <a href="dashboard.php" class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="kelompok.php" class="nav-item <?= $activePage === 'kelompok' ? 'active' : '' ?>">Kelompok</a>
                <a href="pendaftaran.php" class="nav-item <?= $activePage === 'pendaftaran' ? 'active' : '' ?>">Pendaftaran</a>

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
        <main class="main-content">
