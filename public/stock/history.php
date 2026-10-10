<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_auth();

// Ambil data histori transaksi dengan JOIN ke products dan users
$stmt = $pdo->query("
    SELECT sm.*, p.name AS product_name, p.sku, u.username 
    FROM stock_movements sm
    JOIN products p ON sm.product_id = p.id
    JOIN users u ON sm.user_id = u.id
    ORDER BY sm.created_at DESC
");
$histories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Histori Stok - Inventory App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h2 { color: #0284c7; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; color: #1e293b; }
        .badge-in { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .badge-out { background: #fee2e2; color: #ef4444; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>
        <div class="header">
            <h2>Histori Transaksi Stok (Masuk & Keluar)</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>SKU & Produk</th>
                    <th>Jumlah</th>
                    <th>Pencatat</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($histories) > 0): ?>
                    <?php foreach ($histories as $h): ?>
                        <tr>
                            <td><?= escape($h['created_at']) ?></td>
                            <td>
                                <?php if ($h['type'] === 'IN'): ?>
                                    <span class="badge-in">MASUK (IN)</span>
                                <?php else: ?>
                                    <span class="badge-out">KELUAR (OUT)</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?= escape($h['sku']) ?></code> - <strong><?= escape($h['product_name']) ?></strong></td>
                            <td><strong><?= escape($h['quantity']) ?></strong></td>
                            <td><?= escape($h['username']) ?></td>
                            <td><?= escape($h['notes'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #64748b;">Belum ada histori transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>