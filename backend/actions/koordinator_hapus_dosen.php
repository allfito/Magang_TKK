<?php
/**
 * Action: Hapus Dosen Pembimbing (Koordinator)
 */

require_once __DIR__ . '/../helpers/KoordinatorHelper.php';
require_once __DIR__ . '/../controllers/KoordinatorController.php';

KoordinatorHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$dosenId = (int) ($_POST['dosen_id'] ?? 0);

$controller = new KoordinatorController();
$result     = $controller->deleteDosen($dosenId);

if ($result['status']) {
    Session::setFlash('success', $result['message']);
} else {
    Session::setFlash('error', $result['message']);
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
