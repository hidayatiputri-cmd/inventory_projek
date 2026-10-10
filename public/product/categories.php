<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_auth();

$error = '';
$success = '';

// Proses tambah kategori baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = 'Nama kategori tidak boleh kosong.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
            $success = 'Kategori berhasil ditambahkan!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Kategori dengan nama tersebut sudah ada.';
            } else {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}

// Ambil daftar kategori
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Inventory App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { color: #0284c7; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
        .btn { padding: 10px 15px; background: #0284c7; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #0369a1; }
        .alert-success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; color: #1e293b; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-size: 14px; }
        .back-link:hover { color: #334155; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>
        <h2>Kelola Kategori Produk</h2>

        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= escape($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= escape($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nama Kategori Baru</label>
                <input type="text" id="name" name="name" required>
            </div>
            <button type="submit" class="btn">Simpan Kategori</button>
        </form>

        <h3 style="margin-top: 30px;">Daftar Kategori</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= escape($cat['id']) ?></td>
                            <td><?= escape($cat['name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="text-align: center; color: #64748b;">Belum ada kategori.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>