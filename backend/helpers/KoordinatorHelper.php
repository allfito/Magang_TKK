<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../controllers/KoordinatorViewController.php';

/**
 * KoordinatorHelper
 * Kelas utilitas untuk kebutuhan halaman koordinator.
 * Prinsip OOP: Encapsulation — semua method static, bukan fungsi prosedural tersebar.
 */
class KoordinatorHelper
{
    private static ?KoordinatorViewController $controller = null;

    /**
     * Paksa login koordinator. Redirect ke login jika tidak terautentikasi.
     */
    public static function requireLogin(): void
    {
        Session::start();

        if (!Session::isLoggedIn() || Session::getRole() !== 'korbid') {
            header('Location: ../../frontend/auth/login.php');
            exit;
        }
    }

    /**
     * Lazy-load KoordinatorViewController (Singleton dalam request ini).
     */
    private static function getController(): KoordinatorViewController
    {
        if (self::$controller === null) {
            self::$controller = new KoordinatorViewController();
        }
        return self::$controller;
    }

    // ---------------------------------------------------------------
    // Proxy ke KoordinatorViewController
    // ---------------------------------------------------------------

    public static function getActiveGroupCount(): int
    {
        return self::getController()->getActiveGroupCount();
    }

    public static function getPendingLocationCount(): int
    {
        return self::getController()->getPendingLocationCount();
    }

    public static function getPendingProposalCount(): int
    {
        return self::getController()->getPendingProposalCount();
    }

    public static function getPendingBerkasCount(): int
    {
        return self::getController()->getPendingBerkasCount();
    }

    public static function getPendingBuktiCount(): int
    {
        return self::getController()->getPendingBuktiCount();
    }

    public static function getGroupsPendingVerification(): array
    {
        return self::getController()->getGroupsPendingVerification();
    }

    public static function getMembersCount(int $kelompokId): int
    {
        return self::getController()->getMembersCount($kelompokId);
    }

    public static function getGroupsForLocationVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        return self::getController()->getGroupsForLocationVerification($sortBy);
    }

    public static function getGroupsForProposalVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        return self::getController()->getGroupsForProposalVerification($sortBy);
    }

    public static function getGroupsForBerkasVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        return self::getController()->getGroupsForBerkasVerification($sortBy);
    }

    public static function getBerkasByGroup(int $kelompokId): array
    {
        return self::getController()->getBerkasByGroup($kelompokId);
    }

    public static function getGroupsForBuktiVerification(string $sortBy = 'tanggal_terbaru'): array
    {
        return self::getController()->getGroupsForBuktiVerification($sortBy);
    }

    public static function getGroupsForPlotting(string $sortBy = 'nama_a'): array
    {
        return self::getController()->getGroupsForPlotting($sortBy);
    }

    public static function getPlottingSummary(): array
    {
        return self::getController()->getPlottingSummary();
    }

    public static function getCompleteGroupsData(string $sortBy = 'nama_a'): array
    {
        return self::getController()->getCompleteGroupsData($sortBy);
    }

    public static function getAllDosen(): array
    {
        return self::getController()->getAllDosen();
    }

    // ---------------------------------------------------------------
    // UTILITAS TAMPILAN
    // ---------------------------------------------------------------

    /**
     * Format tanggal ke format Indonesia (d M Y).
     */
    public static function formatDateIndo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        return $timestamp ? date('d M Y', $timestamp) : '-';
    }

    /**
     * Kembalikan CSS class badge berdasarkan status verifikasi.
     */
    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'disetujui', 'selesai' => 'badge-success-status',
            'ditolak'              => 'badge-danger',
            default                => 'badge-warning',
        };
    }

    /**
     * Buat link Google Maps dari koordinat.
     */
    public static function generateGoogleMapsLink(string $latitude, string $longitude): string
    {
        if (empty($latitude) || empty($longitude)) {
            return '-';
        }
        return "https://maps.google.com/?q={$latitude},{$longitude}";
    }
}
