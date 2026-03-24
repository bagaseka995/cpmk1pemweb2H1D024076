<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/ordermodel.php';

// Guard: Hanya admin dan kasir
Auth::checkRole(['admin', 'kasir']);

$orderModel = new OrderModel();
// Ambil daftar order yang belum dibayar (status != 'paid')
$unpaidOrders = $orderModel->getActiveOrders();

// PANGGIL HEADER & SIDEBAR
include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <h2>Daftar Meja Belum Bayar</h2>
    <p>Silakan pilih meja yang akan melakukan pembayaran (Full / Split Bill).</p>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['flash_error']; ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No Order</th>
                <th>Meja</th>
                <th>Status Dapur</th>
                <th>Total Sementara</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($unpaidOrders) > 0): ?>
                <?php foreach($unpaidOrders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><b>Meja <?= htmlspecialchars($order['table_number']) ?></b></td>
                        <td>
                            <?php 
                                // Pewarnaan badge sesuai status dapur
                                $color = $order['status'] == 'served' ? '#3498db' : ($order['status'] == 'ready' ? '#2ecc71' : '#f39c12'); 
                            ?>
                            <span style="background-color: <?= $color ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                                <?= $order['status'] ?>
                            </span>
                        </td>
                        <td><b>Rp <?= number_format($order['total'], 0, ',', '.') ?></b></td>
                        <td>
                            <a href="<?= BASE_URL ?>/views/orders/pay.php?id=<?= $order['id'] ?>" class="btn" style="background-color: #27ae60;">
                                Proses Bayar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada tagihan yang belum dibayar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>