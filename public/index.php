<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_auth();

// Menggunakan JOIN untuk mengambil data produk beserta nama kategorinya
$stmt = $pdo->query("
    SELECT products.*, categories.name AS category_name 
    FROM products 
    JOIN categories ON products.category_id = categories.id 
    ORDER BY products.id DESC
");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Produk - Inventory App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h2 { color: #0284c7; margin: 0; }
        .btn { padding: 8px 15px; background: #0284c7; color: white; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn:hover { background: #0369a1; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; color: #1e293b; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>
        <div class="header">
            <h2>Daftar Produk Inventaris</h2>
            <div>
                <a href="categories.php" class="btn" style="background: #475569; margin-right: 10px;">Kelola Kategori</a>
                <a href="create.php" class="btn">+ Tambah Produk</a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Min. Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><code><?= escape($p['sku']) ?></code></td>
                            <td><strong><?= escape($p['name']) ?></strong></td>
                            <td><span class="badge"><?= escape($p['category_name']) ?></span></td>
                            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td><?= escape($p['stock']) ?></td>
                            <td><?= escape($p['minimum_stock']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #64748b;">Belum ada data produk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>