<?php
require_once 'config.php';

// ── Overall Summary ──────────────────────────────────────────
$summary = $conn->query("
    SELECT
        COUNT(*)                        AS total_products,
        SUM(p.stock)                    AS total_stock,
        SUM(p.price * p.stock)          AS total_value,
        AVG(p.price)                    AS avg_price,
        SUM(CASE WHEN p.stock < 20 THEN 1 ELSE 0 END) AS low_stock
    FROM products p
")->fetch_assoc();

// ── Per-Category Breakdown ───────────────────────────────────
$by_category = $conn->query("
    SELECT
        c.name                          AS category,
        COUNT(p.id)                     AS product_count,
        COALESCE(SUM(p.stock), 0)       AS total_stock,
        COALESCE(SUM(p.price * p.stock), 0) AS total_value,
        COALESCE(AVG(p.price), 0)       AS avg_price
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id, c.name
    ORDER BY total_value DESC
");

// ── Per-Supplier Breakdown ───────────────────────────────────
$by_supplier = $conn->query("
    SELECT
        s.name                          AS supplier,
        s.contact_person,
        s.phone,
        COUNT(p.id)                     AS product_count,
        COALESCE(SUM(p.stock), 0)       AS total_stock,
        COALESCE(SUM(p.price * p.stock), 0) AS total_value
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id, s.name, s.contact_person, s.phone
    ORDER BY total_value DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports – Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">


    <div class="top-bar">
        <h1>Inventory Reports</h1>
        <a href="index.php" class="btn-back">Back</a>
    </div>

    <div class="summary-cards">

        <div class="card green">
            <p class="card-value"><?= $summary['total_products'] ?></p>
            <p class="card-label">Total Products</p>
        </div>
        <div class="card blue">
            <p class="card-value"><?= number_format($summary['total_stock']) ?></p>
            <p class="card-label">Total Stock Units</p>
        </div>
        <div class="card purple">
            <p class="card-value">₱<?= number_format($summary['total_value'], 2) ?></p>
            <p class="card-label">Total Inventory Value</p>
        </div>
        <div class="card orange">
            <p class="card-value">₱<?= number_format($summary['avg_price'], 2) ?></p>
            <p class="card-label">Average Price</p>
        </div>
        <div class="card red">
            <p class="card-value"><?= $summary['low_stock'] ?></p>
            <p class="card-label">Low Stock Items (&lt;20)</p>
        </div>

    </div>

    <!-- ── Per-Category Breakdown ── -->
    <div class="section-header">
        <h2>By Category</h2>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Products</th>
                <th>Total Stock</th>
                <th>Average Price</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $by_category->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['category']) ?></td>

                <td>
                    <?php if ($row['product_count'] == 0): ?>
                        <span class="muted">No products</span>
                    <?php else: ?>
                        <?= $row['product_count'] ?>
                    <?php endif; ?>
                </td>

                <td><?= number_format($row['total_stock']) ?></td>

                <td>
                    <?= $row['product_count'] > 0
                        ? '₱' . number_format($row['avg_price'], 2)
                        : '—' ?>
                </td>

                <td class="val">
                    <?= $row['product_count'] > 0
                        ? '₱' . number_format($row['total_value'], 2)
                        : '—' ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- ── Per-Supplier Breakdown ── -->
    <div class="section-header">
        <h2>By Supplier</h2>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Products</th>
                <th>Total Stock</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $by_supplier->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['supplier']) ?></td>
                <td><?= htmlspecialchars($row['contact_person']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>

                <td>
                    <?php if ($row['product_count'] == 0): ?>
                        <span class="muted">No products</span>
                    <?php else: ?>
                        <?= $row['product_count'] ?>
                    <?php endif; ?>
                </td>

                <td><?= number_format($row['total_stock']) ?></td>

                <td class="val">
                    <?= $row['product_count'] > 0
                        ? '₱' . number_format($row['total_value'], 2)
                        : '—' ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>