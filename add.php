<?php
require_once 'config.php';

$message = '';
$name = $description = $price = $stock = $category_id = $supplier_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $price       = $_POST['price'] ?? '';
    $stock       = $_POST['stock'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? '';

    if (empty($name) || empty($category_id) || empty($supplier_id)) {
        $message = '<p style="color:red;">Name, category, and supplier are required.</p>';
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $message = '<p style="color:red;">Please enter a valid price.</p>';
    } elseif (!is_numeric($stock) || (int)$stock < 0) {
        $message = '<p style="color:red;">Please enter a valid stock quantity.</p>';
    } else {
        $price_val = (float)$price;
        $stock_val = (int)$stock;
        $cat_val   = (int)$category_id;
        $sup_val   = (int)$supplier_id;

        $sql = "INSERT INTO products (name, description, price, stock, category_id, supplier_id)
                VALUES ('$name', '$description', $price_val, $stock_val, $cat_val, $sup_val)";

        if ($conn->query($sql)) {
            echo '<p style="color:green; font-size:1.2em;">Product added! Redirecting...</p>';
            header('Refresh: 2; URL=index.php');
            exit;
        } else {
            $message = '<p style="color:red;">Error: ' . $conn->error . '</p>';
        }
    }
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
$suppliers  = $conn->query("SELECT id, name FROM suppliers ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container form-page">
        <h1>Add Product</h1>

        <?= $message ?>

        <form method="POST" action="add.php">

            <label>Product Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required placeholder="e.g. Wireless Mouse">

            <label>Description</label>
            <textarea name="description" rows="3" placeholder="e.g. 2.4GHz cordless mouse"><?= htmlspecialchars($description) ?></textarea>

            <label>Price (₱)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" min="0" required placeholder="e.g. 499.00">

            <label>Stock</label>
            <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>" min="0" required placeholder="e.g. 50">

            <label>Category</label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Supplier</label>
            <select name="supplier_id" required>
                <option value="">-- Select Supplier --</option>
                <?php while ($sup = $suppliers->fetch_assoc()): ?>
                    <option value="<?= $sup['id'] ?>"
                        <?= ($supplier_id == $sup['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sup['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Add Product</button>
            <a href="index.php" class="cancel">Cancel</a>

        </form>
    </div>
</body>
</html>