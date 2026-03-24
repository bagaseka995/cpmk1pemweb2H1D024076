<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/auth.php';

// Middleware: Pastikan user sudah login
Auth::checkRole(); 

// Panggil Header & Sidebar (Isinya CSS dan Sidebar yang tadi kita buat)
include __DIR__ . '/../layout/header.php';
?>

<div class="content">
    <h1>Selamat Datang di Dashboard</h1>
    
    <div class="card">
        <h3>Informasi Sistem</h3>
        <p>Login berhasil! Ini adalah halaman utama yang dilindungi oleh session. Perhatikan menu di sebelah kiri, menu tersebut akan berubah sesuai dengan hak akses kamu.</p>
        <p>Silakan coba tombol logout di bawah untuk mengetes penghapusan session.</p>
        
        <a href="<?= BASE_URL ?>/views/auth/logout.php" class="btn-logout" style="background-color: #e74c3c; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 20px; font-weight: bold;">Keluar (Logout)</a>
    </div>
</div>

</body>
</html>