<?php
require_once 'config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "
SELECT p.id, p.name, p.description, p.price, p.stock, p.created_at,
       c.name AS category,
       s.name AS supplier
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN suppliers s ON p.supplier_id = s.id
WHERE 1=1
";

if (!empty($search)) {
    $sql .= " AND (
        p.name LIKE '%" . $conn->real_escape_string($search) . "%'
        OR p.description LIKE '%" . $conn->real_escape_string($search) . "%'
    )";
}

if (!empty($category)) {
    $sql .= " AND c.name = '" . $conn->real_escape_string($category) . "'";
}

$sql .= " ORDER BY p.id ASC";
$result = $conn->query($sql);
$categories = $conn->query("
SELECT DISTINCT name
FROM categories
ORDER BY name
");

$stats_sql = "
SELECT COUNT(*) AS total,
       SUM(p.stock) AS total_stock,
       SUM(p.price * p.stock) AS total_value,
       SUM(CASE WHEN p.stock < 20 THEN 1 ELSE 0 END) AS low_stock
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN suppliers s ON p.supplier_id = s.id
WHERE 1=1
";

if (!empty($search)) {
    $stats_sql .= " AND (
        p.name LIKE '%" . $conn->real_escape_string($search) . "%'
        OR p.description LIKE '%" . $conn->real_escape_string($search) . "%'
    )";
}

if (!empty($category)) {
    $stats_sql .= " AND c.name = '" . $conn->real_escape_string($category) . "'";
}

$stats = $conn->query($stats_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="top-bar">
        <h1>Inventory System</h1>
        <div style="display:flex; gap:10px;">
            <a href="report.php" class="btn-add" style="background:#2196F3;">Reports</a>
            <a href="add.php" class="btn-add">+ Add Product</a>
        </div>
    </div>

    <form class="search-bar" method="GET">
        <input
            type="text"
            name="search"
            placeholder="Search Product"
            value="<?= htmlspecialchars($search) ?>">

        <select name="category">
            <option value="">All Categories</option>

            <?php while ($c = $categories->fetch_assoc()): ?>
                <option value="<?= $c['name'] ?>"
                    <?= ($category == $c['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter / Search</button>
    </form>

    <div class="stats">
        <p>Total Products: <?= $stats['total'] ?></p>
        <p>Total Stock: <?= $stats['total_stock'] ?></p>
        <p>Total Inventory Value: ₱<?= number_format($stats['total_value'], 2) ?></p>
        <p>Low Stock Items: <?= $stats['low_stock'] ?></p>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Category</th>
            <th>Supplier</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="<?= ($row['stock'] < 20) ? 'low-stock' : '' ?>">

            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>₱<?= number_format($row['price'], 2) ?></td>
            <td><?= $row['stock'] ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['supplier']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn-edit">
                    Edit
                </a>
                <a href="delete.php?id=<?= $row['id'] ?>"
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to delete this product?');">
                    Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p class="count">
        Total: <?= $result->num_rows ?> product(s)
    </p>
</div>

</body>
</html>