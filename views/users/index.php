<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/usermodel.php';

Auth::checkRole(['admin']); // Hanya admin yang boleh kelola user

$userModel = new UserModel();

// Logika Hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (Csrf::verify($_POST['csrf_token'])) {
        if ($userModel->delete($_POST['id'])) {
            $_SESSION['flash_success'] = "User berhasil dihapus.";
        } else {
            $_SESSION['flash_error'] = "Gagal menghapus! Anda tidak bisa menghapus diri sendiri.";
        }
        header("Location: index.php"); exit;
    }
}

$users = $userModel->getAllUsers();
include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <h2>Manajemen User / Karyawan</h2>

    <?php if(isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <a href="create.php" class="btn btn-add">+ Tambah Karyawan Baru</a>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role / Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><b><?= htmlspecialchars($u['username']) ?></b></td>
                    <td>
                        <span style="background:#34495e; color:white; padding:3px 8px; border-radius:4px; font-size:12px;">
                            <?= strtoupper($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-edit">Edit</a>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Hapus user ini?')">
                            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-delete">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>