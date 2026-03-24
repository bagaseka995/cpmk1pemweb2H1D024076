<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/usermodel.php';

Auth::checkRole(['admin']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Csrf::verify($_POST['csrf_token'])) {
        $userModel = new UserModel();
        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        if ($userModel->createUser($username, $password, $role)) {
            $_SESSION['flash_success'] = "User $username berhasil ditambahkan!";
            header("Location: index.php"); exit;
        } else {
            $error = "Gagal menambah user. Username mungkin sudah terdaftar.";
        }
    }
}

include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <a href="index.php" style="text-decoration:none; color:#7f8c8d;">← Kembali ke Daftar User</a>
    <h2 style="margin-top:10px;">Tambah Karyawan Baru</h2>

    <div class="card" style="max-width: 500px;">
        <?php if($error): ?><div class="alert alert-error" style="background:#e74c3c; color:white; padding:10px; border-radius:4px; margin-bottom:15px;"><?= $error ?></div><?php endif; ?>
        
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Username</label>
                <input type="text" name="username" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" required placeholder="Contoh: kasir_budi">
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Password</label>
                <input type="password" name="password" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" required>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Role / Jabatan</label>
                <select name="role" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                    <option value="pelayan">Pelayan / Dapur</option>
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin / Owner</option>
                </select>
            </div>

            <button type="submit" class="btn" style="background:#2ecc71; color:white; border:none; width:100%; padding:12px; font-weight:bold; border-radius:4px; cursor:pointer;">Simpan Karyawan</button>
        </form>
    </div>
</div>