<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/menumodel.php';

Auth::checkRole(['admin']);

$menuModel = new MenuModel();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$menu = $menuModel->getById($id);

if (!$menu) die("Menu tidak ditemukan!");

$categories = $menuModel->getCategories();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
        die("CSRF Token Invalid!");
    }

    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $price = (int)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_spicy = isset($_POST['is_spicy']) ? 1 : 0;

    $image = $menu['image']; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = time() . '_' . $_FILES['image']['name'];
        $upload_dir = __DIR__ . '/../../public/uploads/menu/';
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name)) {
            if ($menu['image'] !== 'default.png' && file_exists($upload_dir . $menu['image'])) {
                unlink($upload_dir . $menu['image']);
            }
            $image = $file_name;
        }
    }

    $data = [
        'name' => $name, 'description' => $description, 'price' => $price,
        'category_id' => $category_id, 'image' => $image,
        'is_available' => $is_available, 'is_spicy' => $is_spicy
    ];

    if ($menuModel->updateMenu($id, $data)) {
        $_SESSION['flash_success'] = "Menu berhasil diperbarui!";
        header("Location: " . BASE_URL . "/views/menu/index.php");
        exit;
    } else {
        $error = "Gagal mengupdate data.";
    }
}

include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <a href="index.php" class="btn" style="background:#7f8c8d; margin-bottom:15px;">← Kembali</a>
    <h2>Edit Produk: <?= htmlspecialchars($menu['name']) ?></h2>

    <div class="card">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
            
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($menu['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Foto Saat Ini</label><br>
                <img src="<?= BASE_URL ?>/public/uploads/menu/<?= $menu['image'] ?>" width="120" style="border-radius:8px; margin-bottom:10px;">
                <input type="file" name="image" class="form-control" accept="image/*">
                <small>*Biarkan kosong jika tidak ingin mengganti foto.</small>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="price" class="form-control" value="<?= $menu['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id" class="form-control">
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $menu['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 20px; margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                <label style="font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_available" value="1" <?= $menu['is_available'] ? 'checked' : '' ?> style="transform: scale(1.3);"> 
                    Stok Tersedia (Available)
                </label>
                
                <label style="font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_spicy" value="1" <?= $menu['is_spicy'] ? 'checked' : '' ?> style="transform: scale(1.3);"> 
                    Rasa Pedas
                </label>
            </div>

            <button type="submit" class="btn" style="background:#27ae60; width:100%; padding:12px; font-weight:bold;">Simpan Perubahan</button>
        </form>
    </div>
</div>
</body>
</html>