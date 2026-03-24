<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/menumodel.php';

Auth::checkRole(['admin']);

$menuModel = new MenuModel();
$categories = $menuModel->getCategories(); 
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
        die("Error: CSRF Token Invalid!");
    }

    $name = htmlspecialchars($_POST['name'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_spicy = isset($_POST['is_spicy']) ? 1 : 0;

    $image = 'default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image']['name']); 
        $upload_dir = __DIR__ . '/../../public/uploads/menu/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
            $image = $file_name;
        } else {
            $error = "Gagal mengunggah gambar.";
        }
    }

    if (empty($error)) {
        $data = [
            'name' => $name, 'description' => $description, 'price' => $price,
            'category_id' => $category_id, 'image' => $image,
            'is_available' => $is_available, 'is_spicy' => $is_spicy
        ];

        if ($menuModel->createMenu($data)) {
            $_SESSION['flash_success'] = "Menu '$name' berhasil ditambahkan!";
            header("Location: " . BASE_URL . "/views/menu/index.php");
            exit;
        } else {
            $error = "Gagal menyimpan ke database.";
        }
    }
}

include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <a href="<?= BASE_URL ?>/views/menu/index.php" class="btn" style="background-color: #7f8c8d; margin-bottom: 20px;">← Kembali ke Daftar Menu</a>
    <h2>Tambah Menu Baru</h2>

    <div class="card">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
            
            <div class="form-group">
                <label>Nama Menu</label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Mie Goreng Spesial">
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="price" class="form-control" required min="0" step="1000">
            </div>

            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Foto Menu</label>
                <input type="file" name="image" class="form-control" accept="image/png, image/jpeg, image/jpg">
                <small style="color: #7f8c8d;">Kosongkan jika tidak ada gambar.</small>
            </div>

            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <label style="font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="is_available" value="1" checked> Tersedia
                </label>
                <label style="font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="is_spicy" value="1"> Pedas
                </label>
            </div>

            <button type="submit" class="btn" style="background-color: #2ecc71; width: 100%; padding: 12px; font-size: 16px;">Simpan Menu</button>
        </form>
    </div>
</div>

</body>
</html>