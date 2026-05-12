<?php
/**
 * Action: Verifikasi Dokumen (Koordinator)
 */

require_once __DIR__ . '/../helpers/KoordinatorHelper.php';
require_once __DIR__ . '/../controllers/KoordinatorController.php';

KoordinatorHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/dashboard.php');
    exit;
}

$action     = $_POST['action'] ?? '';
$type       = $_POST['type']   ?? '';
$id         = (int) ($_POST['id'] ?? 0);

$controller = new KoordinatorController();
$result     = $controller->verifyDocument($action, $type, $id);

if ($result['status']) {
    Session::setFlash('success', $result['message']);
} else {
    Session::setFlash('error', $result['message']);
}

header('Location: ../../frontend/koordinator/' . ($result['redirect'] ?? 'dashboard.php'));
exit;
