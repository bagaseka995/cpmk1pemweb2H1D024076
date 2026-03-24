<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/ordermodel.php';

// Guard: Hanya admin dan kasir
Auth::checkRole(['admin', 'kasir']);

$orderModel = new OrderModel();
$tables = $orderModel->getTables();
$menus = $orderModel->getMenus();
$error = '';

// Proses Simpan Pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
        die("CSRF Token Invalid!");
    }

    $table_id = $_POST['table_id'];
    $waiter_id = $_SESSION['user_id'];
    $notes = htmlspecialchars($_POST['notes'] ?? '');
    
    $menu_ids = $_POST['menu_id'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    
    $items = [];
    foreach ($menu_ids as $index => $m_id) {
        $qty = (int)$qtys[$index];
        if (!empty($m_id) && $qty > 0) {
            $price = 0;
            foreach ($menus as $m) {
                if ($m['id'] == $m_id) {
                    $price = $m['price'];
                    break;
                }
            }
            $items[] = ['menu_id' => $m_id, 'qty' => $qty, 'price' => $price];
        }
    }

    if (count($items) > 0) {
        if ($orderModel->createOrder($table_id, $waiter_id, $notes, $items)) {
            $_SESSION['flash_success'] = "Pesanan berhasil dibuat! Dapur sekarang bisa melihatnya.";
            header("Location: " . BASE_URL . "/views/orders/kasir.php");
            exit;
        } else {
            $error = "Terjadi kesalahan sistem saat menyimpan pesanan.";
        }
    } else {
        $error = "Pilih minimal 1 menu dengan jumlah lebih dari 0!";
    }
}

// PANGGIL HEADER & SIDEBAR
include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <h2>Buat Pesanan Baru</h2>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success']; ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
            
            <div class="form-group">
                <label>Pilih Meja</label>
                <select name="table_id" class="form-control" required>
                    <option value="">-- Pilih Meja Pelanggan --</option>
                    <?php foreach ($tables as $t): ?>
                        <option value="<?= $t['id'] ?>">Meja <?= htmlspecialchars($t['table_number']) ?> (<?= $t['status'] === 'occupied' ? 'Terisi' : 'Kosong' ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Catatan Umum (Opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="Contoh: Atas nama Bapak Budi">
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <label><b>Daftar Pesanan</b></label>

            <div id="items-container" style="margin-top: 10px;">
                <div class="item-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <select name="menu_id[]" class="form-control" required style="flex: 3;">
                        <option value="">-- Pilih Menu --</option>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?> - Rp <?= number_format($m['price'], 0, ',', '.') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="qty[]" class="form-control" required min="1" value="1" style="flex: 1;" placeholder="Jml">
                </div>
            </div>

            <button type="button" class="btn" style="background-color: #3498db; margin-bottom: 20px;" onclick="addMenuRow()">+ Tambah Menu Lain</button>

            <button type="submit" class="btn" style="background-color: #27ae60; width: 100%; padding: 12px; font-size: 16px;">Proses Pesanan ke Dapur</button>
        </form>
    </div>
</div>

<script>
    function addMenuRow() {
        const container = document.getElementById('items-container');
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = "";
        newRow.querySelector('input').value = "1";
        container.appendChild(newRow);
    }
</script>

</body>
</html> 