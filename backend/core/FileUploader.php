<?php

/**
 * FileUploader
 * Enkapsulasi logika upload file agar tidak tersebar di berbagai controller.
 * Prinsip OOP: Encapsulation & Single Responsibility.
 */
class FileUploader
{
    private string $uploadDir;
    private array  $allowedMimes;
    private array  $allowedExtensions;
    private int    $maxSizeBytes;

    public function __construct(
        string $uploadDir,
        array  $allowedMimes,
        array  $allowedExtensions,
        int    $maxSizeBytes
    ) {
        $this->uploadDir         = rtrim($uploadDir, '/') . '/';
        $this->allowedMimes      = $allowedMimes;
        $this->allowedExtensions = $allowedExtensions;
        $this->maxSizeBytes      = $maxSizeBytes;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Validasi dan pindahkan file yang diupload.
     *
     * @param array  $fileData   Data dari $_FILES
     * @param string $prefix     Awalan nama file yang disimpan
     * @return array ['status' => bool, 'message' => string, 'path' => string]
     */
    public function upload(array $fileData, string $prefix = 'file'): array
    {
        if (!isset($fileData['error']) || $fileData['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'Tidak ada file yang diupload atau terjadi kesalahan.', 'path' => ''];
        }

        if ($fileData['size'] > $this->maxSizeBytes) {
            $maxMb = round($this->maxSizeBytes / 1024 / 1024);
            return ['status' => false, 'message' => "Ukuran file maksimal {$maxMb}MB.", 'path' => ''];
        }

        $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions, true)) {
            return ['status' => false, 'message' => 'Ekstensi file tidak diizinkan.', 'path' => ''];
        }

        if (!in_array($fileData['type'], $this->allowedMimes, true)) {
            return ['status' => false, 'message' => 'Tipe MIME file tidak diizinkan.', 'path' => ''];
        }

        $filename = uniqid($prefix . '_') . '.' . $ext;
        $fullPath = $this->uploadDir . $filename;

        if (!move_uploaded_file($fileData['tmp_name'], $fullPath)) {
            return ['status' => false, 'message' => 'Gagal memindahkan file ke server.', 'path' => ''];
        }

        // Kembalikan path relatif dari root project
        $relativePath = $this->getRelativePath($fullPath);

        return ['status' => true, 'message' => 'File berhasil diupload.', 'path' => $relativePath];
    }

    /**
     * Buat path relatif dari root project (dua level di atas /backend).
     */
    private function getRelativePath(string $fullPath): string
    {
        // Root project: dua level di atas /backend/core
        $root = realpath(__DIR__ . '/../../') . '/';
        return str_replace($root, '', $fullPath);
    }

    // ---------------------------------------------------------------
    // Factory methods untuk tiap jenis file
    // ---------------------------------------------------------------

    public static function forProposal(): self
    {
        return new self(
            __DIR__ . '/../../uploads/proposals/',
            ['application/pdf'],
            ['pdf'],
            5 * 1024 * 1024
        );
    }

    public static function forBukti(): self
    {
        return new self(
            __DIR__ . '/../../uploads/bukti/',
            ['application/pdf', 'image/jpeg', 'image/png'],
            ['pdf', 'jpg', 'jpeg', 'png'],
            5 * 1024 * 1024
        );
    }

    public static function forBerkas(): self
    {
        return new self(
            __DIR__ . '/../../uploads/berkas/',
            ['application/pdf', 'application/x-pdf', 'image/jpeg', 'image/jpg', 'image/png'],
            ['pdf', 'jpeg', 'jpg', 'png'],
            2 * 1024 * 1024
        );
    }
}
