<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_auth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data produk berdasarkan ID menggunakan prepared statement
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Ambil daftar kategori untuk dropdown
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $minimum_stock = filter_input(INPUT_POST, 'minimum_stock', FILTER_VALIDATE_INT);

    if (empty($sku)) $errors['sku'] = 'SKU wajib diisi.';
    if (empty($name)) $errors['name'] = 'Nama produk wajib diisi.';
    if (!$category_id) $errors['category_id'] = 'Pilih kategori yang valid.';
    if ($price === false || $price < 0) $errors['price'] = 'Harga tidak boleh negatif.';
    if ($minimum_stock === false || $minimum_stock < 0) $errors['minimum_stock'] = 'Minimum stok tidak boleh negatif.';

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE products 
                SET category_id = :category_id, sku = :sku, name = :name, price = :price, minimum_stock = :minimum_stock 
                WHERE id = :id
            ");
            $updateStmt->execute([
                'category_id' => $category_id,
                'sku' => $sku,
                'name' => $name,
                'price' => $price,
                'minimum_stock' => $minimum_stock,
                'id' => $id
            ]);

            header("Location: index.php?status=updated");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors['sku'] = 'SKU sudah digunakan oleh produk lain.';
            } else {
                $errors['db'] = 'Gagal memperbarui produk: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Inventory App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { color: #0284c7; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        .btn { padding: 10px 15px; background: #0284c7; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn:hover { background: #0369a1; }
        .error-text { color: #ef4444; font-size: 12px; margin-top: 4px; }
        .alert-error { background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">&larr; Kembali ke Daftar Produk</a>
        <h2>Edit Produk</h2>

        <?php if (isset($errors['db'])): ?>
            <div class="alert-error"><?= escape($errors['db']) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="sku">SKU (Kode Unik Produk)</label>
                <input type="text" id="sku" name="sku" value="<?= escape($product['sku']) ?>" required>
                <?php if (isset($errors['sku'])) echo '<div class="error-text">' . $errors['sku'] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" name="name" value="<?= escape($product['name']) ?>" required>
                <?php if (isset($errors['name'])) echo '<div class="error-text">' . $errors['name'] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= escape($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])) echo '<div class="error-text">' . $errors['category_id'] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= escape($product['price']) ?>" required>
                <?php if (isset($errors['price'])) echo '<div class="error-text">' . $errors['price'] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="minimum_stock">Batas Minimum Stok (Peringatan)</label>
                <input type="number" id="minimum_stock" name="minimum_stock" value="<?= escape($product['minimum_stock']) ?>" required>
                <?php if (isset($errors['minimum_stock'])) echo '<div class="error-text">' . $errors['minimum_stock'] . '</div>'; ?>
            </div>

            <button type="submit" class="btn">Perbarui Produk</button>
        </form>
    </div>
</body>
</html>