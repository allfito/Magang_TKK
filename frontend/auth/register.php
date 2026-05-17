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
            <div class="split-half flex-start" style="padding-left: 15%;">
                <div class="text-block">
                    <h2>SIMM</h2>
                    <p>Silahkan isi data pendaftaran untuk membuat akun mahasiswa baru.</p>
                </div>
            </div>
            <div class="split-half">
                <div class="form-container">
                    <h2>Daftar Akun</h2>
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger py-2 mb-3" style="font-size: 13px;">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>
                    <div id="js-error-msg" class="alert alert-danger py-2 mb-3" style="display:none; font-size: 13px;"></div>
                    <form action="../../backend/auth/register.php" method="POST" id="registerForm">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="nama" id="reg-nama" class="form-control" placeholder="Sultan Salahuddin" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="reg-email" class="form-control" placeholder="nama@student.polije.ac.id" required>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="tel" name="no_tlp" id="reg-tlp" class="form-control" placeholder="081234567890" pattern="08[0-9]{8,11}" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Nomor telepon harus diawali 08 dan terdiri dari 10-13 angka" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="reg-pass" class="form-control" placeholder="Sultan3587" required>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" id="reg-conf" class="form-control" placeholder="Sultan3587" required>
                        </div>
                        <button type="submit" class="btn-submit">Daftar</button>
                    </form>

                    <script>
                        document.getElementById('registerForm').addEventListener('submit', function(e) {
                            var nama = document.getElementById('reg-nama').value.trim();
                            var email = document.getElementById('reg-email').value.trim();
                            var tlp = document.getElementById('reg-tlp').value.trim();
                            var pass = document.getElementById('reg-pass').value.trim();
                            var conf = document.getElementById('reg-conf').value.trim();

                            var errorMsgBox = document.getElementById('js-error-msg');
                            errorMsgBox.style.display = 'none';

                            if (!nama || !email || !tlp || !pass || !conf) {
                                e.preventDefault();
                                errorMsgBox.textContent = 'Peringatan: Seluruh data pendaftaran wajib diisi!';
                                errorMsgBox.style.display = 'block';
                                return;
                            }

                            if (!email.endsWith('@student.polije.ac.id')) {
                                e.preventDefault();
                                errorMsgBox.textContent = 'Peringatan: Email pendaftaran harus menggunakan domain @student.polije.ac.id!';
                                errorMsgBox.style.display = 'block';
                                return;
                            }

                            if (pass !== conf) {
                                e.preventDefault();
                                errorMsgBox.textContent = 'Peringatan: Password dan Konfirmasi Password tidak cocok!';
                                errorMsgBox.style.display = 'block';
                                return;
                            }
                        });
                    </script>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6DQD021o6FfQ2z9F3/jzQOf/0C1CmZ5l5q2Q8Qw9TGTg" crossorigin="anonymous"></script>
</body>

</html>
