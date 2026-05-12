<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';

/**
 * MahasiswaHelper
 * Kelas utilitas untuk kebutuhan halaman mahasiswa.
 * Prinsip OOP: Encapsulation — mengelompokkan fungsi terkait dalam satu kelas.
 * Semua method static karena tidak memerlukan state instance.
 */
class MahasiswaHelper
{
    /**
     * Paksa login mahasiswa. Redirect ke login jika tidak terautentikasi.
     */
    public static function requireLogin(): void
    {
        Session::start();

        if (!Session::isLoggedIn() || Session::getRole() !== 'mahasiswa') {
            header('Location: ../../frontend/auth/login.php');
            exit;
        }
    }

    /**
     * Ambil data user yang sedang login.
     */
    public static function currentUser(): ?array
    {
        $userId = Session::getUserId();
        if ($userId === null) {
            return null;
        }

        $db        = Database::getInstance()->getConnection();
        $userModel = new User($db);
        return $userModel->findById($userId);
    }

    /**
     * Redirect dengan pesan error ke session.
     */
    public static function redirectWithError(string $message, string $location): never
    {
        Session::start();
        Session::setFlash('error', $message);
        header('Location: ' . $location);
        exit;
    }

    /**
     * Redirect dengan pesan sukses ke session.
     */
    public static function redirectWithSuccess(string $message, string $location): never
    {
        Session::start();
        Session::setFlash('success', $message);
        header('Location: ' . $location);
        exit;
    }
}
