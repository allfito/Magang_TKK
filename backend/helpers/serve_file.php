<?php
/**
 * serve_file.php
 * Melayani file upload secara aman dengan validasi sesi.
 * Akses: /backend/helpers/serve_file.php?path=uploads/proposals/xxx.pdf
 *
 * Mendukung dua format path yang mungkin tersimpan di DB:
 *  1. Path relatif   : uploads/proposals/proposal_abc123.pdf  (format baru)
 *  2. Path absolut   : C:\laragon\www\Y\Magang_TKK\...        (format lama)
 */

session_start();

// Wajib login
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

$root         = realpath(__DIR__ . '/../../');
$uploadsDir   = $root . DIRECTORY_SEPARATOR . 'uploads';
$requestedPath = $_GET['path'] ?? '';

// ---------------------------------------------------------------
// Normalkan path: tangani path absolut Windows maupun relatif
// ---------------------------------------------------------------

// Ganti backslash menjadi forward slash untuk proses seragam
$requestedPath = str_replace('\\', '/', $requestedPath);

// Jika path absolut (misal C:/laragon/...), ambil bagian "uploads/..." saja
if (preg_match('/uploads\/.+/i', $requestedPath, $m)) {
    $requestedPath = $m[0];
}

// Bersihkan traversal
$requestedPath = str_replace(['../', './'], '', $requestedPath);
$requestedPath = ltrim($requestedPath, '/');

// Hanya izinkan path yang dimulai dengan uploads/
if (!str_starts_with($requestedPath, 'uploads/')) {
    http_response_code(403);
    exit('Path tidak diizinkan.');
}

// Bangun full path menggunakan separator OS
$fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $requestedPath);
$realPath = realpath($fullPath);

// Validasi: file harus benar-benar berada di dalam folder uploads/
if (!$realPath || !str_starts_with($realPath, $uploadsDir . DIRECTORY_SEPARATOR)) {
    http_response_code(403);
    exit('Akses ditolak.');
}

if (!is_file($realPath)) {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

// Deteksi MIME type berdasarkan ekstensi
$ext     = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

// Kirim file ke browser
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($realPath) . '"');
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: private, max-age=3600');
readfile($realPath);
exit;
