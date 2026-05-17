<?php
/**
 * Action: Hapus Plotting Dosen (Koordinator)
 */

require_once __DIR__ . '/../helpers/KoordinatorHelper.php';
require_once __DIR__ . '/../controllers/KoordinatorController.php';

KoordinatorHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$kelompokId = (int) ($_POST['kelompok_id'] ?? 0);

$controller = new KoordinatorController();
$result     = $controller->deletePlotting($kelompokId);

if ($result['status']) {
    Session::setFlash('success', $result['message']);
} else {
    Session::setFlash('error', $result['message']);
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
