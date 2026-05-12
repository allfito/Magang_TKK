<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Magang Mahasiswa - Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="background-container"></div>

    <header class="navbar">
        <a href="index.php" class="logo-container" style="display: flex; align-items: center;">
            <img src="assets/logo-jti-new.svg" alt="Logo JTI" style="height: 40px; width: auto;">
        </a>
        <div class="nav-links">
            <a href="frontend/auth/login.php" id="link-login">Login</a>
            <a href="frontend/auth/register.php" id="link-daftar" class="btn-daftar">Daftar</a>
        </div>
    </header>

    <div id="app" class="home-padding">
        <!-- HOME VIEW -->
        <section class="home-view">
            <h1>Sistem Informasi Magang<br>Mahasiswa</h1>
            <p>Selamat Datang Mahasiswa Jurusan<br>Teknologi Informasi</p>
            <a href="frontend/auth/login.php" class="btn-login-home">Login</a>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6DQD021o6FfQ2z9F3/jzQOf/0C1CmZ5l5q2Q8Qw9TGTg" crossorigin="anonymous"></script>
</body>

</html>
