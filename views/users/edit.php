<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/usermodel.php';

Auth::checkRole(['admin']);

$userModel = new UserModel();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = $userModel->getById($id);

if (!$user) die("User tidak ditemukan!");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Csrf::verify($_POST['csrf_token'])) {
        $username = htmlspecialchars($_POST['username']);
        $role = $_POST['role'];
        $password = !empty($_POST['password']) ? $_POST['password'] : null;

        if ($userModel->updateUser($id, $username, $role, $password)) {
            $_SESSION['flash_success'] = "Data user $username berhasil diperbarui!";
            header("Location: index.php"); exit;
        } else {
            $error = "Gagal memperbarui data user.";
        }
    }
}

include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <a href="index.php" style="text-decoration:none; color:#7f8c8d;">← Kembali ke Daftar User</a>
    <h2 style="margin-top:10px;">Edit Data Karyawan</h2>

    <div class="card" style="max-width: 500px;">
        <?php if($error): ?><div class="alert alert-error" style="background:#e74c3c; color:white; padding:10px; border-radius:4px; margin-bottom:15px;"><?= $error ?></div><?php endif; ?>
        
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Username</label>
                <input type="text" name="username" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Ganti Password (Opsional)</label>
                <input type="password" name="password" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" placeholder="Kosongkan jika tidak ganti password">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Role / Jabatan</label>
                <select name="role" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                    <option value="pelayan" <?= $user['role'] == 'pelayan' ? 'selected' : '' ?>>Pelayan / Dapur</option>
                    <option value="kasir" <?= $user['role'] == 'kasir' ? 'selected' : '' ?>>Kasir</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin / Owner</option>
                </select>
            </div>

            <button type="submit" class="btn" style="background:#3498db; color:white; border:none; width:100%; padding:12px; font-weight:bold; border-radius:4px; cursor:pointer;">Simpan Perubahan</button>
        </form>
    </div>
</div>