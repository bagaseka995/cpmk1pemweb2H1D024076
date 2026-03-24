<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/menumodel.php';

$menuModel = new MenuModel();

// Ambil 3 menu terlaris untuk rekomendasi
$recommendations = $menuModel->getTopRecommendations();

// Ambil semua menu publik
$rawMenus = $menuModel->getPublicMenus();

// Logika PHP: Mengelompokkan data array berdasarkan nama kategori
$groupedMenus = [];
foreach ($rawMenus as $item) {
    $groupedMenus[$item['category_name']][] = $item;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Menu - Resto App</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .header { background-color: #e74c3c; color: white; padding: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }

        .login-btn-header {
            position: absolute;
            right: 20px;
            top: 20px;
            color: white;
            text-decoration: none;
            font-size: 12px;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 5px 10px;
            border-radius: 4px;
            transition: 0.3s;
        }
        .login-btn-header:hover { background-color: rgba(255,255,255,0.1); border-color: white; }
        
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        
        /* Style Seksi Rekomendasi */
        .recommendation-section { margin-bottom: 30px; }
        .rec-title { font-size: 18px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .rec-scroll { display: flex; gap: 15px; overflow-x: auto; padding: 10px 5px; scrollbar-width: none; }
        .rec-scroll::-webkit-scrollbar { display: none; }
        .rec-card { 
            min-width: 180px; background: white; border-radius: 12px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden; 
            border: 1px solid #ffeaa7; flex-shrink: 0; 
        }
        .rec-img { width: 100%; height: 100px; object-fit: cover; }
        .rec-info { padding: 10px; text-align: center; }
        .best-seller-tag { background: #f1c40f; color: #000; font-size: 9px; padding: 2px 8px; border-radius: 10px; font-weight: bold; display: inline-block; margin-bottom: 5px; }

        .category-title { border-bottom: 2px solid #e74c3c; color: #c0392b; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; }
        .menu-card { background: white; border-radius: 8px; padding: 15px; margin-bottom: 15px; display: flex; box-shadow: 0 2px 5px rgba(0,0,0,0.05); gap: 15px; align-items: center; }
        .menu-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background-color: #eee; flex-shrink: 0; }
        .menu-info { flex: 1; }
        .menu-name { font-size: 16px; font-weight: bold; margin: 0 0 5px 0; display: flex; align-items: center; gap: 8px; }
        .menu-desc { font-size: 13px; color: #7f8c8d; margin: 0 0 8px 0; line-height: 1.4; }
        .menu-price { font-weight: bold; color: #27ae60; margin: 0; }
        .badge-spicy { background-color: #e74c3c; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: normal; }
    </style>
</head>
<body>

<div class="header">
    <a href="<?= BASE_URL ?>/views/auth/login.php" class="login-btn-header">Login</a>
    <h1>Resto App</h1>
    <p>Scan QR. Pilih Menu. Panggil Pelayan.</p>
</div>

<div class="container">
    
    <?php if (!empty($recommendations)): ?>
    <div class="recommendation-section">
        <div class="rec-title">Menu Terfavorit</div>
        <div class="rec-scroll">
            <?php foreach ($recommendations as $rec): ?>
                <div class="rec-card">
                    <img src="<?= BASE_URL ?>/public/uploads/menu/<?= htmlspecialchars($rec['image']) ?>" class="rec-img">
                    <div class="rec-info">
                        <span class="best-seller-tag">BEST SELLER</span>
                        <div style="font-weight: bold; font-size: 13px;"><?= htmlspecialchars($rec['name']) ?></div>
                        <div style="color: #27ae60; font-size: 12px; font-weight: bold;">Rp <?= number_format($rec['price'], 0, ',', '.') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($groupedMenus)): ?>
        <p style="text-align:center; margin-top:50px;">Mohon maaf, menu sedang tidak tersedia.</p>
    <?php else: ?>
        <?php foreach ($groupedMenus as $categoryName => $menus): ?>
            <h2 class="category-title"><?= htmlspecialchars($categoryName) ?></h2>
            <?php foreach ($menus as $menu): ?>
                <div class="menu-card">
                    <img src="<?= BASE_URL ?>/public/uploads/menu/<?= htmlspecialchars($menu['image']) ?>" alt="Menu" class="menu-img">
                    <div class="menu-info">
                        <h3 class="menu-name">
                            <?= htmlspecialchars($menu['name']) ?>
                            <?php if($menu['is_spicy']): ?>
                                <span class="badge-spicy">🌶️ Pedas</span>
                            <?php endif; ?>
                        </h3>
                        <p class="menu-desc"><?= htmlspecialchars($menu['description']) ?></p>
                        <p class="menu-price">Rp <?= number_format($menu['price'], 0, ',', '.') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>