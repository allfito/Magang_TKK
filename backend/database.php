<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "simagang";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

return $mysqli;
