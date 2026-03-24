<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';
require_once __DIR__ . '/../../classes/csrf.php';
require_once __DIR__ . '/../../classes/ordermodel.php';

// Guard: Hanya admin dan kasir
Auth::checkRole(['admin', 'kasir']);

$orderModel = new OrderModel();
$error = '';

// Ambil ID Order dari URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order = $orderModel->getOrderById($order_id);

// Validasi jika order tidak ditemukan atau sudah dibayar penuh
if (!$order || $order['status'] === 'paid') {
    $_SESSION['flash_error'] = "Pesanan tidak ditemukan atau sudah lunas.";
    header("Location: " . BASE_URL . "/views/orders/billing.php");
    exit;
}

$items = $orderModel->getOrderItems($order_id);

// Proses saat tombol Bayar diklik (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
        die("CSRF Token Invalid!");
    }

    $selected_items = $_POST['pay_items'] ?? [];

    if (count($selected_items) > 0) {
        $kasir_id = $_SESSION['user_id'];
        
        $new_order_id = $orderModel->processPayment($order_id, $selected_items, $kasir_id);
        
        if ($new_order_id) {
            header("Location: " . BASE_URL . "/views/orders/receipt.php?id=" . $new_order_id);
            exit;
        } else {
            $error = "Gagal memproses pembayaran. Terjadi kesalahan pada database.";
        }
    } else {
        $error = "Pilih minimal 1 menu yang akan dibayar!";
    }
}

// PANGGIL HEADER & SIDEBAR
include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <a href="<?= BASE_URL ?>/views/orders/billing.php" class="btn" style="background-color: #7f8c8d; margin-bottom: 15px;">← Kembali ke Daftar Tagihan</a>
    
    <div class="card">
        <h2>Proses Pembayaran</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div style="background: #ecf0f1; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between;">
            <div>
                <b>No. Order:</b> #<?= $order['id'] ?><br>
                <b>Meja:</b> <?= htmlspecialchars($order['table_number']) ?>
            </div>
            <div style="text-align: right;">
                <b>Kasir:</b> <?= htmlspecialchars($_SESSION['username']) ?><br>
                <b>Pelayan:</b> <?= htmlspecialchars($order['kasir_name']) ?>
            </div>
        </div>

        <p style="color: #7f8c8d; font-size: 14px;">Centang menu yang ingin dibayar pada transaksi ini (Split Bill). Centang semua untuk bayar penuh.</p>

        <form action="" method="POST" id="paymentForm">
            <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
            
            <table>
                <thead>
                    <tr>
                        <th width="5%"><input type="checkbox" id="checkAll" style="transform: scale(1.5); cursor: pointer;" checked></th>
                        <th>Item Menu</th>
                        <th>Jml</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): 
                        $subtotal = $item['quantity'] * $item['unit_price'];
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" name="pay_items[]" value="<?= $item['id'] ?>" class="item-checkbox" data-price="<?= $subtotal ?>" style="transform: scale(1.5); cursor: pointer;" checked>
                        </td>
                        <td><?= htmlspecialchars($item['menu_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>Rp <?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                        <td><b>Rp <?= number_format($subtotal, 0, ',', '.') ?></b></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px; border-top: 2px dashed #ccc; padding-top: 20px; text-align: right;">
                <div>Total yang dipilih:</div>
                <div id="displayTotal" style="font-size: 24px; font-weight: bold; color: #27ae60; margin: 10px 0;">Rp 0</div>
                
                <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                    <label>Uang Tunai (Rp): </label>
                    <input type="number" id="cashAmount" class="form-control" style="width: 200px; padding: 10px;" min="0" placeholder="Masukkan jumlah uang">
                </div>
                <div id="kembalian" style="color: #e74c3c; font-weight: bold; margin-top: 10px; font-size: 18px;">Kembalian: Rp 0</div>

                <button type="submit" class="btn" style="background-color: #2ecc71; width: 100%; padding: 12px; font-size: 16px; margin-top: 15px; font-weight: bold;">Bayar & Cetak Struk</button>
            </div>
        </form>
    </div>
</div>

<script>
    const checkAll = document.getElementById('checkAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const displayTotal = document.getElementById('displayTotal');
    const cashAmount = document.getElementById('cashAmount');
    const kembalianDisplay = document.getElementById('kembalian');
    
    let currentTotal = 0;

    function updateTotal() {
        currentTotal = 0;
        itemCheckboxes.forEach(cb => {
            if (cb.checked) {
                currentTotal += parseInt(cb.getAttribute('data-price'));
            }
        });
        displayTotal.innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
        calculateChange();
    }

    function calculateChange() {
        let cash = parseInt(cashAmount.value) || 0;
        let change = cash - currentTotal;
        if (change < 0 || cash === 0) {
            kembalianDisplay.innerText = 'Kembalian: Rp 0';
            kembalianDisplay.style.color = '#e74c3c';
        } else {
            kembalianDisplay.innerText = 'Kembalian: Rp ' + change.toLocaleString('id-ID');
            kembalianDisplay.style.color = '#27ae60';
        }
    }

    checkAll.addEventListener('change', function() {
        itemCheckboxes.forEach(cb => { cb.checked = checkAll.checked; });
        updateTotal();
    });

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) checkAll.checked = false;
            const allChecked = Array.from(itemCheckboxes).every(i => i.checked);
            if (allChecked) checkAll.checked = true;
            updateTotal();
        });
    });

    cashAmount.addEventListener('input', calculateChange);
    updateTotal();
</script>

</body>
</html>