<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/menumodel.php';

Auth::checkRole(['admin']);

$menuModel = new MenuModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
        die("CSRF Token Invalid!");
    }
    
    $id_to_delete = $_POST['id'];
    if ($menuModel->delete($id_to_delete)) {
        $_SESSION['flash_success'] = "Data menu berhasil dihapus!";
    } else {
        $_SESSION['flash_error'] = "Gagal menghapus! Menu tidak bisa dihapus karena sudah tercatat di riwayat pesanan pelanggan.";
    }
    header("Location: " . BASE_URL . "/views/menu/index.php");
    exit;
}

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_data = $menuModel->getTotalCount();
$total_pages = ceil($total_data / $limit);
$menus = $menuModel->getPaginatedMenu($limit, $offset);

include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <h2>Daftar Menu</h2>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success']; ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['flash_error']; ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/views/menu/create.php" class="btn btn-add">+ Tambah Menu Baru</a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($menus) > 0): ?>
                <?php $no = $offset + 1; foreach ($menus as $menu): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($menu['name']) ?></td>
                    <td><?= htmlspecialchars($menu['category_name'] ?? 'Tanpa Kategori') ?></td>
                    <td>Rp <?= number_format($menu['price'], 0, ',', '.') ?></td>
                    <td>
                        <span style="color: <?= $menu['is_available'] ? '#27ae60' : '#e74c3c' ?>; font-weight: bold;">
                            <?= $menu['is_available'] ? 'Tersedia' : 'Habis' ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $menu['id'] ?>" class="btn btn-edit">Edit</a>
                        
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $menu['id'] ?>">
                            <button type="submit" class="btn btn-delete">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada data menu.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="pagination-item <?= ($i == $page) ? 'active' : '' ?>" 
               style="padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; color: #333; margin-right: 5px; border-radius: 4px; <?= ($i == $page) ? 'background: #34495e; color: white;' : 'background: white;' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

</body>
</html>