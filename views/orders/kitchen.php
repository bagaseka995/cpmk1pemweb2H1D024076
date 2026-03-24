<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/ordermodel.php';

// Guard: Hanya admin dan pelayan/dapur yang boleh buka
Auth::checkRole(['admin', 'pelayan']);

$orderModel = new OrderModel();

// Logika untuk mengubah status order via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
        die("CSRF Token Invalid!");
    }
    
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    if ($orderModel->updateStatus($order_id, $new_status)) {
        $_SESSION['flash_success'] = "Status pesanan #$order_id berhasil diubah menjadi: " . strtoupper($new_status);
    }
    header("Location: " . BASE_URL . "/views/orders/kitchen.php");
    exit;
}

// Ambil data pesanan aktif
$orders = $orderModel->getActiveOrders();

// PANGGIL HEADER & SIDEBAR
include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <h2>Monitor Dapur (Status Kitchen)</h2>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success']; ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="order-grid">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <?php 
                    // Logika penentuan warna dan tombol
                    $bg_class = 'bg-' . $order['status']; 
                    $next_status = '';
                    $btn_text = '';
                    $btn_color = '';
                    
                    if ($order['status'] == 'pending') {
                        $next_status = 'cooking'; $btn_text = 'Mulai Masak'; $btn_color = '#f39c12';
                    } elseif ($order['status'] == 'cooking') {
                        $next_status = 'ready'; $btn_text = 'Selesai Masak (Ready)'; $btn_color = '#2ecc71';
                    } elseif ($order['status'] == 'ready') {
                        $next_status = 'served'; $btn_text = 'Sajikan ke Meja'; $btn_color = '#3498db';
                    }
                    
                    $items = $orderModel->getOrderItems($order['id']);
                ?>
                
                <div class="order-card">
                    <div class="order-header <?= $bg_class ?>" style="padding: 15px; color: white; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                        <span>Meja <?= htmlspecialchars($order['table_number']) ?></span>
                        <span style="font-size: 11px; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.5); padding: 2px 5px; border-radius: 3px;"><?= $order['status'] ?></span>
                    </div>
                    <div class="order-body" style="padding: 15px; flex: 1; background: white;">
                        <small style="color:#7f8c8d;"><?= date('H:i', strtotime($order['created_at'])) ?> | <?= htmlspecialchars($order['waiter_name']) ?></small>
                        <p style="margin: 10px 0;"><b>Catatan:</b> <?= htmlspecialchars($order['notes']) ?: '-' ?></p>
                        <hr style="border:0; border-top:1px solid #eee;">
                        <ul style="margin: 10px 0; padding-left: 20px; font-size: 14px;">
                            <?php foreach ($items as $item): ?>
                                <li style="margin-bottom: 5px;"><?= $item['quantity'] ?>x <?= htmlspecialchars($item['menu_name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <?php if ($next_status != ''): ?>
                    <div class="order-footer" style="padding: 15px; background: #f9f9f9; border-top: 1px solid #eee;">
                        <form action="" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $next_status ?>">
                            <button type="submit" class="btn" style="background-color: <?= $btn_color ?>; width: 100%; font-weight: bold;">
                                Lanjut ➔ <?= $btn_text ?>
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; color: #7f8c8d; margin-top: 50px;">Belum ada pesanan aktif di dapur.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>