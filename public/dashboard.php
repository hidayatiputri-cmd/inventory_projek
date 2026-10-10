<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';

check_auth();

// Hitung Widget Statistik menggunakan Fungsi Agregat SQL (SUM, COUNT)
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalInventoryValue = $pdo->query("SELECT SUM(stock * price) FROM products")->fetchColumn() ?: 0;
$lowStockCount = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= minimum_stock")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Inventory App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: bold; }
        .btn-logout:hover { background: #dc2626; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f1f5f9; padding: 20px; border-radius: 12px; border-left: 5px solid #0284c7; }
        .stat-card.warning { border-left-color: #f59e0b; }
        .stat-title { font-size: 14px; color: #64748b; font-weight: 600; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #1e293b; }

        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .menu-btn { display: block; padding: 15px; background: #e0f2fe; color: #0369a1; text-align: center; border-radius: 10px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .menu-btn:hover { background: #bae6fd; }
        .menu-btn.in { background: #dcfce7; color: #166534; }
        .menu-btn.in:hover { background: #bbf7d0; }
        .menu-btn.out { background: #fee2e2; color: #991b1b; }
        .menu-btn.out:hover { background: #fecaca; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2 style="margin: 0; color: #0284c7;">Dashboard Inventaris</h2>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #64748b;">Halo, <strong><?= escape($_SESSION['user']['username']) ?></strong> (Role: <span style="text-transform: uppercase;"><?= escape($_SESSION['user']['role']) ?></span>)</p>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <!-- Widget Statistik (Agregat SQL) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Jenis Produk</div>
                <div class="stat-value"><?= number_format($totalProducts) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Nilai Inventaris</div>
                <div class="stat-value">Rp <?= number_format($totalInventoryValue, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card warning">
                <div class="stat-title">Produk Stok Rendah</div>
                <div class="stat-value"><?= number_format($lowStockCount) ?></div>
            </div>
        </div>

        <h3 style="margin-bottom: 15px; color: #1e293b;">Menu Navigasi Utama</h3>
        <div class="menu-grid">
            <a href="products/index.php" class="menu-btn">📦 Kelola Produk & Kategori</a>
            <a href="stock/in.php" class="menu-btn in">📥 Catat Barang Masuk</a>
            <a href="stock/out.php" class="menu-btn out">📤 Catat Barang Keluar</a>
            <a href="stock/history.php" class="menu-btn">📊 Lihat Histori Stok</a>
        </div>
    </div>
</body>
</html>