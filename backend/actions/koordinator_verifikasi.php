<?php
/**
 * Action: Verifikasi Dokumen (Koordinator)
 * Mendukung AJAX (fetch) maupun form POST biasa
 */

require_once __DIR__ . '/../helpers/KoordinatorHelper.php';
require_once __DIR__ . '/../controllers/KoordinatorController.php';

KoordinatorHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$type   = $_POST['type']   ?? '';
$id     = (int) ($_POST['id'] ?? 0);

// Deteksi apakah request berasal dari AJAX (fetch)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$controller = new KoordinatorController();
$result     = $controller->verifyDocument($action, $type, $id);

if ($isAjax) {
    // Kembalikan JSON untuk AJAX — tanpa redirect
    header('Content-Type: application/json');
    echo json_encode([
        'success' => (bool) $result['status'],
        'message' => $result['message'] ?? '',
        'action'  => $action,
        'type'    => $type,
        'id'      => $id,
    ]);
    exit;
}

// Fallback: perilaku lama untuk request non-AJAX
if ($result['status']) {
    Session::setFlash('success', $result['message']);
} else {
    Session::setFlash('error', $result['message']);
}

header('Location: ../../frontend/koordinator/' . ($result['redirect'] ?? 'dashboard.php'));
exit;