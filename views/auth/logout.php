<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';

// Instansiasi class Auth dan jalankan method logout()
$auth = new Auth();
$auth->logout();

// Arahkan kembali ke halaman login
header("Location: " . BASE_URL . "/views/auth/login.php");
exit;
?>