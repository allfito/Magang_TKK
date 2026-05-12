<?php
/**
 * Action: Plotting Dosen Pembimbing (Koordinator)
 */

require_once __DIR__ . '/../helpers/KoordinatorHelper.php';
require_once __DIR__ . '/../controllers/KoordinatorController.php';

KoordinatorHelper::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/koordinator/plotting.php');
    exit;
}

$kelompokId      = (int) ($_POST['kelompok_id']    ?? 0);
$dosenPembimbing = trim($_POST['dosen_pembimbing'] ?? '');

$controller = new KoordinatorController();
$result     = $controller->plotDosen($kelompokId, $dosenPembimbing);

if ($result['status']) {
    Session::setFlash('success', $result['message']);
} else {
    Session::setFlash('error', $result['message']);
}

header('Location: ../../frontend/koordinator/plotting.php');
exit;
