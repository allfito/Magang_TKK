<?php
session_start();
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SIMM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <div class="background-container"></div>

    <header class="navbar">
        <a href="../../index.php" class="logo-container" style="display: flex; align-items: center;">
            <img src="../../assets/logo-jti-new.svg" alt="Logo JTI" style="height: 40px; width: auto;">
        </a>
        <div class="nav-links">
            <a href="login.php" id="link-login">Login</a>
            <a href="register.php" id="link-daftar" class="btn-daftar">Daftar</a>
        </div>
    </header>

    <?php if ($errorMessage): ?>
        <div id="error-toast" class="toast align-items-center text-bg-danger border-0 show"
             style="position: fixed; top: 20px; right: 20px; z-index: 1100; min-width: 280px;"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-medium">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <script>
            setTimeout(function() {
                var toastEl = document.getElementById('error-toast');
                if (toastEl) {
                    var toast = bootstrap.Toast.getOrCreateInstance(toastEl, { autohide: false });
                    toastEl.style.opacity = '0';
                    toastEl.style.transition = 'opacity 0.5s';
                    setTimeout(function() { toastEl.style.display = 'none'; }, 500);
                }
            }, 3500);
        </script>
    <?php endif; ?>

    <div id="app">
        <section class="split-layout">
            <div class="split-half flex-start" style="padding-left: 15%;">
                <div class="text-block">
                    <h2>SIMM</h2>
                    <p>Silahkan isi data pendaftaran untuk membuat akun mahasiswa baru.</p>
                </div>
            </div>
            <div class="split-half">
                <div class="form-container">
                    <h2>Daftar Akun</h2>
                    <form action="../../backend/auth/register.php" method="POST" novalidate>
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-control" placeholder="Sultan Salahuddin" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="sultansalahuddin@students.college.ac.id" required>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="tel" name="no_tlp" class="form-control" placeholder="081234567890" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Sultan3587" required>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control" placeholder="Sultan3587" required>
                        </div>
                        <button type="submit" class="btn-submit">Daftar</button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6DQD021o6FfQ2z9F3/jzQOf/0C1CmZ5l5q2Q8Qw9TGTg" crossorigin="anonymous"></script>
</body>

</html>
