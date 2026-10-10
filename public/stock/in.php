<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_auth();

$errors = [];
$success = '';

// Ambil daftar produk untuk pilihan dropdown
$stmt_prod = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC");
$products = $stmt_prod->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $notes = trim($_POST['notes'] ?? '');
    $user_id = $_SESSION['user']['id'];

    if (!$product_id) $errors['product_id'] = 'Pilih produk yang valid.';
    if ($quantity === false || $quantity <= 0) $errors['quantity'] = 'Jumlah masuk harus lebih dari 0.';

    if (empty($errors)) {
        try {
            // Gunakan Database Transaction untuk konsistensi data stok & histori
            $pdo->beginTransaction();

            // 1. Tambah stok pada tabel products
            $updateStmt = $pdo->prepare("UPDATE products SET stock = stock + :quantity WHERE id = :product_id");
            $updateStmt->execute([
                'quantity' => $quantity,
                'product_id' => $product_id
            ]);

            // 2. Catat histori pada tabel stock_movements
            $historyStmt = $pdo->prepare("
                INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) 
                VALUES (:product_id, :user_id, 'IN', :quantity, :notes)
            ");
            $historyStmt->execute([
                'product_id' => $product_id,
                'user_id' => $user_id,
                'quantity' => $quantity,
                'notes' => $notes
            ]);

            $pdo->commit();
            $success = 'Transaksi barang masuk berhasil dicatat dan stok diperbarui!';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['db'] = 'Gagal memproses transaksi: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Barang Masuk - Inventory App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { color: #0284c7; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        .btn { padding: 10px 15px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn:hover { background: #059669; }
        .error-text { color: #ef4444; font-size: 12px; margin-top: 4px; }
        .alert-error { background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>
        <h2>Catat Barang Masuk (Restock)</h2>

        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= escape($success) ?></div>
        <?php endif; ?>
        <?php if (isset($errors['db'])): ?>
            <div class="alert-error"><?= escape($errors['db']) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="product_id">Pilih Produk</label>
                <select id="product_id" name="product_id" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            [<?= escape($p['sku']) ?>] <?= escape($p['name']) ?> (Stok Saat Ini: <?= $p['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['product_id'])) echo '<div class="error-text">' . $errors['product_id'] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="quantity">Jumlah Barang Masuk</label>
                <input type="number" id="quantity" name="quantity" min="1" required>
                <?php if (isset($errors['quantity'])) echo '<div class="error-text">' . $errors['quantity'] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="notes">Keterangan / Catatan (Opsional)</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Contoh: Kiriman dari supplier utama..."></textarea>
            </div>

            <button type="submit" class="btn">Simpan Barang Masuk</button>
        </form>
    </div>
</body>
</html>