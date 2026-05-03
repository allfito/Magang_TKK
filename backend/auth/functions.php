<?php
function redirectWithError(string $message, string $location): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['error'] = $message;
    header('Location: ' . $location);
    exit;
}

function redirectWithSuccess(string $message, string $location): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['success'] = $message;
    header('Location: ' . $location);
    exit;
}

function findUserByEmail(mysqli $mysqli, string $email): ?array
{
    $sql = "SELECT * FROM user WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (! $stmt) {
        return null;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

function validatePassword(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password minimal 8 karakter';
    }
    if (! preg_match('/[a-z]/i', $password)) {
        return 'Password harus mengandung huruf';
    }
    if (! preg_match('/[0-9]/', $password)) {
        return 'Password harus mengandung angka';
    }
    return null;
}
