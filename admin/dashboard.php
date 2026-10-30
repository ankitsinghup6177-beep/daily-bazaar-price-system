<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {
    header(
        "Location: ../auth/login.php?error=" .
        urlencode("Please login first.")
    );
    exit;
}

if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header(
        "Location: ../products/index.php?error=" .
        urlencode("Admin access required.")
    );
    exit;
}

require_once "../config/db.php";
$adminName = $_SESSION["user_name"] ?? "Admin";


/* TOTAL PRODUCTS */

$totalProducts = 0;
$productQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM products"
);

if ($productQuery) {
    $row = $productQuery->fetch_assoc();
    $totalProducts = (int)($row["total"] ?? 0);
}


/* TOTAL USERS */

$totalUsers = 0;
$userQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);
if ($userQuery) {
    $row = $userQuery->fetch_assoc();
    $totalUsers = (int)($row["total"] ?? 0);
}


/* TOTAL PRICE UPDATES */

$totalUpdates = 0;
$updateQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM price_updates"
);
if ($updateQuery) {
    $row = $updateQuery->fetch_assoc();
    $totalUpdates = (int)($row["total"] ?? 0);
}


/* TODAY'S PRICE UPDATES */

$todayUpdates = 0;
$todayQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM price_updates
     WHERE update_date = CURDATE()"
);
if ($todayQuery) {
    $row = $todayQuery->fetch_assoc();
    $todayUpdates = (int)($row["total"] ?? 0);
}


$recentUpdates = [];
$recentQuery = $conn->query(
    "SELECT
        pu.id,
        pu.old_price,
        pu.new_price,
        pu.update_date,
        p.name AS product_name,
        p.category,
        u.name AS updated_by
     FROM price_updates pu
     INNER JOIN products p
        ON pu.product_id = p.id
     INNER JOIN users u
        ON pu.updated_by = u.id

     ORDER BY
        pu.update_date DESC,
        pu.id DESC
     LIMIT 5"
);
if ($recentQuery) {
    while ($row = $recentQuery->fetch_assoc()) {
        $recentUpdates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Daily Bazaar</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            background: #f4f7f6;
            color: #222;
        }
        .navbar {
            background: #198754;
            color: white;
            padding: 16px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .logout {
            background: white;
            color: #198754;
            padding: 9px 16px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout:hover {
            background: #f1f1f1;
        }


        /* CONTAINER */

        .container {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }


        /* WELCOME */

        .welcome {
            margin-bottom: 25px;
        }
        .welcome h1 {
            font-size: 30px;
            color: #198754;
            margin-bottom: 7px;
        }
        .welcome p {
            color: #666;
            line-height: 1.5;
        }
        .stats {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
            transition: 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-title {
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #198754;
        }
        .section-title {
            margin-bottom: 18px;
            font-size: 22px;
            color: #222;
        }
        .actions {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: #222;
            transition: 0.2s;
        }
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 10px 25px
                rgba(0, 0, 0, 0.12);
        }
        .action-icon {
            font-size: 35px;
            margin-bottom: 12px;
        }
        .action-card h3 {
            color: #198754;
            margin-bottom: 8px;
        }
        .action-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }      
        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
        }
        .recent-section h2 {
            margin-bottom: 20px;
            color: #198754;
        }
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        th {
            background: #198754;
            color: white;
            padding: 13px;
            text-align: left;
        }
        td {
            padding: 13px;
            border-bottom:
                1px solid #eee;
            vertical-align: middle;
        }
        tbody tr:hover {
            background: #f8f9fa;
        }
        .category {
            display: inline-block;
            background: #e8f8ef;
            color: #198754;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .old-price {
            color: #dc3545;
            font-weight: bold;
        }
        .new-price {
            color: #198754;
            font-weight: bold;
        }
        .arrow {
            padding: 0 5px;
            color: #777;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #777;
        }
        .no-data h3 {
            margin-bottom: 8px;
            color: #555;
        }
        @media (max-width: 900px) {
            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }
            .actions {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }
        @media (max-width: 600px) {
            .navbar {
                padding: 14px 18px;
            }
            .admin-info span {
                display: none;
            }
            .stats {
                grid-template-columns: 1fr;
            }
            .actions {
                grid-template-columns: 1fr;
            }
            .welcome h1 {
                font-size: 25px;
            }
            .container {
                margin-top: 25px;
            }
        }
    </style>
</head>
<body>


<!--  NAVBAR -->

<nav class="navbar">
    <a href="dashboard.php" class="brand">Daily Bazaar</a>
    <div class="admin-info">
        <span>👤<?= htmlspecialchars($adminName) ?></span>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>

<!--  MAIN -->

<main class="container">
    <div class="welcome">
        <h1>Admin Dashboard</h1>
        <p>Manage products, prices and users from one place.</p>
    </div>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-title">Total Products</div>
            <div class="stat-number"><?= $totalProducts ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Total Users</div>
            <div class="stat-number"><?= $totalUsers ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Total Price Updates</div>
            <div class="stat-number"><?= $totalUpdates ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Today's Updates</div>
            <div class="stat-number"><?= $todayUpdates ?></div>
        </div>
    </div>

    <!-- MANAGEMENT -->
    <h2 class="section-title">Management</h2>
    <div class="actions">
        <a href="../products/products.php" class="action-card">
            <div class="action-icon">🛒</div>
            <h3>Manage Products</h3>
            <p> Add, edit, delete and activate/deactivate products.</p>
        </a>
        <a href="update_prices.php" class="action-card">
            <div class="action-icon">💰</div>
            <h3>Update Prices</h3>
            <p> Update today's market prices and maintain price history.</p>
        </a>
        <a href="../products/index.php" class="action-card">
            <div class="action-icon">👁️</div>
            <h3>View Products</h3>
            <p> View active products and current prices available to users.</p>
        </a>

        <a href="../index.php" class="action-card">
            <div class="action-icon">🏠</div>
            <h3>Visit Website</h3>
            <p> Go back to the public Daily Bazaar homepage.</p>
        </a>

        <a href="../auth/logout.php" class="action-card">
            <div class="action-icon">🔐</div>
            <h3>Logout</h3>
            <p> Securely logout from the administrator account.</p>
        </a>
    </div>
    <div class="recent-section">
        <h2>Recent Price Updates</h2>
        <?php if (!empty($recentUpdates)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price Change</th>
                            <th>Date</th>
                            <th>Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (
                            $recentUpdates
                            as $update
                        ): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $update["product_name"]
                                        ) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="category">
                                        <?= htmlspecialchars(
                                            $update["category"]
                                        ) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="old-price">
                                        ₹<?= number_format(
                                            (float)$update["old_price"],
                                            2
                                        ) ?>
                                    </span>
                                    <span class="arrow">→</span>
                                    <span class="new-price">
                                        ₹<?= number_format(
                                            (float)$update["new_price"],
                                            2
                                        ) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $update["update_date"]
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $update["updated_by"]
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <h3>No Price Updates Yet</h3>
                <p> Once an admin updates a product price, the latest changes will
                    appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
<?php
$conn->close();
?>