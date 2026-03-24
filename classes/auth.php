<?php
require_once __DIR__ . '/usermodel.php';

class Auth {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login($username, $password) {
        $user = $this->userModel->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function register($username, $password, $email, $role) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->userModel->create($username, $hashedPassword, $email, $role);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public static function checkRole($allowedRoles = []) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/views/auth/login.php");
            exit;
        }

        if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
            die("Akses Ditolak: Role Anda tidak memiliki izin untuk halaman ini.");
        }
    }
}
?>