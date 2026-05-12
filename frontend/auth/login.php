<?php
session_start();
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
$successMessage = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMM</title>
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

    <div id="app">
        <section class="split-layout">
            <div class="split-half">
                <div class="form-container" style="margin-left: auto; margin-right: 15%;">
                    <h2>Login</h2>
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success py-2 mb-3" style="font-size: 14px;">
                            <?= htmlspecialchars($successMessage) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger py-2 mb-3" style="font-size: 13px;">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="../../backend/auth/login.php" id="login-form">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="login-email" class="form-control" placeholder="sultansalahuddin@students.college.ac.id" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" id="login-role" required class="form-select">
                                <option value="">-- Pilih Role --</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="korbid">Koordinator Bidang</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit">Login</button>
                    </form>
                </div>
            </div>
            <div class="split-half flex-start">
                <div class="text-block" style="padding-left: 10%;">
                    <h2>SIMM</h2>
                    <p>Silahkan login untuk mengakses sistem pendaftaran magang online program studi teknik komputer.</p>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6DQD021o6FfQ2z9F3/jzQOf/0C1CmZ5l5q2Q8Qw9TGTg" crossorigin="anonymous"></script>
</body>

</html>
