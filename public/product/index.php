<?php
// public/products/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_auth();

// Ambil data produk dari database
$stmt = $pdo->query("SELECT products.*, categories.name as category_name FROM products JOIN categories ON products.category_id = categories.id ORDER BY products.id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Inventory App</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        a { text-decoration: none; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .btn { display: inline-block; padding: 8px 12px; background: #007bff; color: white; border-radius: 4px; margin-bottom: 15px; }
        .btn-back { background: #6c75d7; margin-bottom: 20px; display: inline-block; color: white; padding: 6px 12px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="btn-back">&larr; Kembali ke Dashboard</a>
        <h2>Kelola Produk</h2>
        <a href="create.php" class="btn">+ Tambah Produk Baru</a>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7" style="text-align: center;">Belum ada produk.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $index => $p): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= escape($p['sku']) ?></td>
                        <td><?= escape($p['name']) ?></td>
                        <td><?= escape($p['category_name']) ?></td>
                        <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                        <td><?= escape($p['stock']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $p['id'] ?>">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>