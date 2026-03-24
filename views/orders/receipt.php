<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/ordermodel.php';

Auth::checkRole(['admin', 'kasir']);

$orderModel = new OrderModel();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$order = $orderModel->getOrderById($order_id);
$items = $orderModel->getOrderItems($order_id);

if (!$order) {
    die("Data struk tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #<?= $order['id'] ?></title>
    <style>
        /* Desain khusus ukuran kertas Thermal 80mm */
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 80mm; 
            margin: 0 auto; 
            padding: 10px;
            font-size: 12px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        .header h2 { margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        .item-name { padding-top: 5px; }
        .item-detail { font-size: 11px; padding-bottom: 5px; }
        
        .footer { margin-top: 20px; font-size: 10px; }

        /* Tombol navigasi (sembunyi saat di-print) */
        .no-print { 
            margin-bottom: 20px; 
            display: flex; 
            gap: 10px; 
            justify-content: center;
        }
        .btn { 
            padding: 8px 15px; 
            text-decoration: none; 
            color: white; 
            border-radius: 4px; 
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .btn-print { background: #2c3e50; cursor: pointer; border: none; }
        .btn-back { background: #7f8c8d; }

        @media print {
            .no-print { display: none; }
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">Cetak Struk</button>
        <a href="billing.php" class="btn btn-back">Kembali</a>
    </div>

    <div class="header text-center">
        <h2>RESTO APP</h2>
        <p>Jl. Lebak No. 77, Indonesia<br>
        Telp: 0812-3456-7890</p>
    </div>

    <div class="line"></div>

    <table>
        <tr>
            <td>Tgl: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            <td class="text-right">Nota: #<?= $order['id'] ?></td>
        </tr>
        <tr>
            <td>Meja: <?= htmlspecialchars($order['table_number']) ?></td>
            <td class="text-right">Kasir: <?= htmlspecialchars($order['kasir_name']) ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        <?php foreach ($items as $item): ?>
            <tr>
                <td colspan="2" class="item-name"><?= htmlspecialchars($item['menu_name']) ?></td>
            </tr>
            <tr>
                <td class="item-detail"><?= $item['quantity'] ?> x <?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                <td class="text-right item-detail">Rp <?= number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td><b>TOTAL</b></td>
            <td class="text-right"><b>Rp <?= number_format($order['total'], 0, ',', '.') ?></b></td>
        </tr>
        <tr>
            <td>Status</td>
            <td class="text-right">LUNAS (PAID)</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="footer text-center">
        <p>Terima kasih atas kunjungan Anda!<br>
        Selamat Menikmati!</p>
    </div>

    <script>
        // Otomatis buka dialog print saat halaman dimuat
        window.onload = function() {
            // window.print(); // Aktifkan ini jika ingin langsung print otomatis
        };
    </script>
</body>
</html>