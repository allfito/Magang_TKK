<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/User.php';

/**
 * AuthController
 * Menangani autentikasi: login, register, logout.
 * Prinsip OOP: Inheritance (extends BaseController), Single Responsibility.
 */
class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User($this->db);
    }

    // ---------------------------------------------------------------
    // LOGIN
    // ---------------------------------------------------------------

    public function login(string $email, string $password, string $requestedRole): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Email tidak valid.');
        }

        if (empty($requestedRole)) {
            return $this->error('Pilih role terlebih dahulu (Mahasiswa atau Koordinator).');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return $this->error('Email tidak terdaftar.');
        }

        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            return $this->error('Password salah.');
        }

        if ($requestedRole !== $user['role']) {
            return $this->error(
                'Role tidak sesuai dengan akun Anda. Role akun Anda: ' . ucfirst($user['role'])
            );
        }

        Session::start();
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('role', $user['role']);

        return $this->success('Login berhasil.', ['role' => $user['role']]);
    }

    // ---------------------------------------------------------------
    // REGISTER
    // ---------------------------------------------------------------

    public function register(array $data): array
    {
        $validationError = $this->validateRegisterData($data);
        if ($validationError) {
            return $this->error($validationError);
        }

        if ($this->userModel->findByEmail($data['email'])) {
            return $this->error('Email sudah terdaftar.');
        }

        if ($this->userModel->create($data)) {
            return $this->success('Pendaftaran berhasil! Silakan login.');
        }

        return $this->error('Gagal membuat akun, terjadi kesalahan server.');
    }

    // ---------------------------------------------------------------
    // LOGOUT
    // ---------------------------------------------------------------

    public function logout(): void
    {
        Session::start();
        Session::destroy();
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    /**
     * Validasi data registrasi. Mengembalikan pesan error atau null jika valid.
     */
    private function validateRegisterData(array $data): ?string
    {
        if (empty($data['nama'])) {
            return 'Nama tidak boleh kosong.';
        }

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            return 'Email tidak valid.';
        }
        
        $email = $data['email'] ?? '';
        if (!str_ends_with($email, '@student.polije.ac.id')) {
            return 'Email harus menggunakan domain @student.polije.ac.id.';
        }

        $noTlp = $data['no_tlp'] ?? '';
        if (empty($noTlp)) {
            return 'Nomor telepon tidak boleh kosong.';
        }
        if (strlen($noTlp) > 15) {
            return 'Nomor telepon maksimal 15 karakter.';
        }

        $password = $data['password'] ?? '';

        if ($password !== ($data['konfirmasi_password'] ?? '')) {
            return 'Password tidak cocok.';
        }

        if (strlen($password) < 8) {
            return 'Password minimal 8 karakter.';
        }

        if (!preg_match('/[a-zA-Z]/', $password)) {
            return 'Password harus mengandung huruf.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            return 'Password harus mengandung angka.';
        }

        return null;
    }
}
