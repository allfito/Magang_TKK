<?php
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../controllers/AuthController.php';

Session::start();
$controller = new AuthController();
$controller->logout();
header('Location: ../../frontend/auth/login.php');
exit;
