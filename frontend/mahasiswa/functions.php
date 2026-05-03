<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireMahasiswaLogin(): void
{
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
        header('Location: ../../frontend/auth/login.php');
        exit;
    }
}

function currentMahasiswa(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $mysqli = require __DIR__ . '/../../backend/database.php';
    $id = (int) $_SESSION['user_id'];
    $sql = "SELECT id, nama, email, no_tlp, role, created_at FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}
