<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; display: flex; height: 100vh; background-color: #f4f7f6; }
        .sidebar { width: 250px; background-color: #2c3e50; color: #ecf0f1; padding: 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h2 { text-align: center; margin-bottom: 5px; color: #1abc9c; }
        .sidebar p { text-align: center; font-size: 13px; color: #bdc3c7; margin-bottom: 20px; }
        .sidebar a { color: #ecf0f1; text-decoration: none; display: block; margin: 10px 0; padding: 12px; background: #34495e; border-radius: 5px; transition: 0.3s; }
        .sidebar a:hover { background: #1abc9c; }
        .sidebar a.active { background: #16a085; border-left: 5px solid #fff; }

        .content { flex: 1; padding: 30px; overflow-y: auto; }
        
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #34495e; color: white; }

        .btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; color: white; border: none; cursor: pointer; display: inline-block; }
        .btn-add { background-color: #27ae60; margin-bottom: 15px; }
        .btn-edit { background-color: #f39c12; }
        .btn-delete { background-color: #e74c3c; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; color: white; }
        .alert-success { background-color: #2ecc71; }
        .alert-error { background-color: #e74c3c; }

        .order-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .order-card { background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }
        .bg-pending { background-color: #e74c3c; }
        .bg-cooking { background-color: #f39c12; }
        .bg-ready   { background-color: #2ecc71; }
        .bg-served  { background-color: #3498db; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Resto App</h2>
    <p>Halo, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong><br>
    Role: <em><?= ucfirst(htmlspecialchars($_SESSION['role'])) ?></em></p>
    <hr style="border-color: #34495e; margin-bottom: 20px;">
    
    <a href="<?= BASE_URL ?>/views/dashboard/index.php">Dashboard</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/views/menu/index.php">Manajemen Menu</a>
        <a href="<?= BASE_URL ?>/views/users/index.php">Manajemen User</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/views/orders/kasir.php">Input Pesanan</a>
    <?php if ($_SESSION['role'] !== 'pelayan'): ?>
        <a href="<?= BASE_URL ?>/views/orders/billing.php">Daftar Tagihan</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/views/orders/kitchen.php">Status Kitchen</a>
    <a href="<?= BASE_URL ?>/views/auth/logout.php" style="background:#c0392b; margin-top:30px;">Logout</a>
</div>