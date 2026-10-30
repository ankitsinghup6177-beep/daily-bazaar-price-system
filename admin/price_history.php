<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../config/db.php";

/* ADMIN AUTHENTICATION */

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
$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");
$date = trim($_GET["date"] ?? "");


/* BUILD QUERY */

$sql = "SELECT
        pu.id,
        pu.old_price,
        pu.new_price,
        pu.update_date,
        pu.created_at,
        p.name AS product_name,
        p.category,
        p.unit,
        u.name AS updated_by
    FROM price_updates pu
    INNER JOIN products p
        ON pu.product_id = p.id
    INNER JOIN users u
        ON pu.updated_by = u.id
    WHERE 1=1
";
$params = [];
$types = "";


if ($search !== "") {
    $sql .= "
        AND (
            p.name LIKE ?
            OR u.name LIKE ?
        )
    ";
    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "ss";
}
if ($category !== "") {
    $sql .= "
        AND p.category = ?
    ";
    $params[] = $category;
    $types .= "s";
}
if ($date !== "") {
    $sql .= "
        AND pu.update_date = ?
    ";
    $params[] = $date;
    $types .= "s";
}
$sql .= "
    ORDER BY
        pu.update_date DESC,
        pu.id DESC
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(
        "Database Query Error: " .
        htmlspecialchars($conn->error)
    );
}
if (count($params) > 0) {
    $stmt->bind_param(
        $types,
        ...$params
    );
}
$stmt->execute();
$result = $stmt->get_result();
$totalHistory = 0;
$totalIncrease = 0;
$totalDecrease = 0;
$statsQuery = "SELECT
        COUNT(*) AS total,
        SUM(
            CASE
                WHEN new_price > old_price
                THEN 1
                ELSE 0
            END
        ) AS increases,

        SUM(
            CASE
                WHEN new_price < old_price
                THEN 1
                ELSE 0
            END
        ) AS decreases
    FROM price_updates
";
$statsResult = $conn->query($statsQuery);
if ($statsResult) {
    $stats = $statsResult->fetch_assoc();
    $totalHistory =
        (int) ($stats["total"] ?? 0);
    $totalIncrease =
        (int) ($stats["increases"] ?? 0);
    $totalDecrease =
        (int) ($stats["decreases"] ?? 0);
}


/* ADMIN NAME */

$adminName =
    $_SESSION["user_name"] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price History - Daily Bazaar</title>
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
        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .nav-right span {
            color: white;
        }
        .dashboard {
            background: white;
            color: #198754 !important;
            padding: 8px 15px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout {
            background: #ffc107;
            color: #222 !important;
            padding: 8px 15px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }
        /* CONTAINER */

        .container {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .heading {
            margin-bottom: 25px;
        }
        .heading h1 {
            color: #198754;
            font-size: 30px;
            margin-bottom: 7px;
        }
        .heading p {
            color: #666;
        }
        .stats {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
        }
        .stat-title {
            color: #777;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .stat-number {
            font-size: 30px;
            font-weight: bold;
            color: #198754;
        }
        .increase-number {
            color: #dc3545;
        }
        .decrease-number {
            color: #198754;
        }
        .filter-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }
        .filter-card h2 {
            color: #198754;
            font-size: 20px;
            margin-bottom: 18px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns:
                2fr 1fr 1fr auto auto;
            gap: 12px;
            align-items: end;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 7px;
        }
        input,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 14px;
        }
        input:focus,
        select:focus {
            outline: none;
            border-color: #198754;
        }
        .btn {
            display: inline-block;
            border: none;
            padding: 10px 17px;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-success {
            background: #198754;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .table-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
        }
        .table-card h2 {
            color: #198754;
            font-size: 20px;
            margin-bottom: 18px;
        }
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }
        th {
            background: #198754;
            color: white;
            padding: 13px;
            text-align: left;
            white-space: nowrap;
        }
        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .category {
            display: inline-block;
            background: #e8f8ef;
            color: #198754;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
        }
        .old-price {
            color: #6c757d;
            font-weight: bold;
        }
        .new-price {
            font-weight: bold;
        }
        .price-up {
            color: #dc3545;
        }
        .price-down {
            color: #198754;
        }
        .price-same {
            color: #6c757d;
        }
        .arrow {
            color: #777;
            padding: 0 5px;
        }
        .change {
            font-weight: bold;
            font-size: 13px;
        }
        .empty {
            text-align: center;
            padding: 45px 20px;
            color: #777;
        }
        .empty h3 {
            margin-bottom: 8px;
            color: #555;
        }
        @media (max-width: 950px) {
            .filter-grid {
                grid-template-columns:
                    1fr 1fr;
            }
        }
        @media (max-width: 700px) {
            .navbar {
                padding: 14px 18px;
            }
            .nav-right span {
                display: none;
            }
            .stats {
                grid-template-columns: 1fr;
            }
            .filter-grid {
                grid-template-columns: 1fr;
            }
            .heading h1 {
                font-size: 25px;
            }
        }
    </style>
</head>
<body>


<!-- NAVBAR -->

<nav class="navbar">
    <a href="dashboard.php" class="brand">Daily Bazaar</a>
    <div class="nav-right">
        <span>👤<?= htmlspecialchars($adminName) ?></span>
        <a href="dashboard.php" class="dashboard"> Dashboard</a>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>
<!--  MAIN -->

<main class="container">
    <div class="heading">
        <h1>Price History</h1>
        <p> Track all daily product price changes.</p>
    </div>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-title">Total Price Updates</div>
            <div class="stat-number"><?= $totalHistory ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Price Increases</div>
            <div class="stat-number increase-number"><?= $totalIncrease ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Price Decreases</div>
            <div class="stat-number decrease-number"><?= $totalDecrease ?></div>
        </div>
    </div>
    <div class="filter-card">
        <h2>Search & Filter</h2>
        <form method="GET" class="filter-grid">
            <div class="form-group">
                <label>Search</label>
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Product name or admin name"
                >
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <option
                        value="Vegetables"
                        <?= $category === "Vegetables"
                            ? "selected"
                            : "" ?>
                    >Vegetables</option>
                    <option
                        value="Fruits"
                        <?= $category === "Fruits"
                            ? "selected"
                            : "" ?>
                    >Fruits</option>
                    <option
                        value="Grocery"
                        <?= $category === "Grocery"
                            ? "selected"
                            : "" ?>
                    >Grocery</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input
                    type="date"
                    name="date"
                    value="<?= htmlspecialchars($date) ?>"
                >
            </div>
            <div>
                <button
                    type="submit"
                    class="btn btn-success"
                >Search</button>
            </div>
            <div>
                <a href="price_history.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-card">
        <h2>Price Change Records</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Old Price</th>
                            <th>New Price</th>
                            <th>Change</th>
                            <th>Date</th>
                            <th>Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        ?>
                        <?php while (
                            $row = $result->fetch_assoc()
                        ): ?>
                            <?php
                            $oldPrice =
                                (float) $row["old_price"];
                            $newPrice =
                                (float) $row["new_price"];
                            $difference =
                                $newPrice - $oldPrice;
                            ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $row["product_name"]
                                        ) ?>
                                    </strong>
                                    <small
                                        style="color:#777;"
                                    >
                                        /
                                        <?= htmlspecialchars(
                                            $row["unit"]
                                        ) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="category">
                                        <?= htmlspecialchars(
                                            $row["category"]
                                        ) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="old-price">
                                        ₹<?= number_format(
                                            $oldPrice,
                                            2
                                        ) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $newPriceClass =
                                        "new-price";
                                    if ($difference > 0) {
                                        $newPriceClass .=
                                            " price-up";
                                    } elseif (
                                        $difference < 0
                                    ) {
                                        $newPriceClass .=
                                            " price-down";
                                    }
                                    ?>
                                    <span
                                        class="<?= $newPriceClass ?>"
                                    >
                                        ₹<?= number_format(
                                            $newPrice,
                                            2
                                        ) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (
                                        $difference > 0
                                    ): ?>
                                        <span
                                            class="change price-up"
                                        >
                                            ↑ ₹<?= number_format(
                                                abs($difference),
                                                2
                                            ) ?>
                                        </span>
                                    <?php elseif (
                                        $difference < 0
                                    ): ?>
                                        <span
                                            class="change price-down"
                                        >
                                            ↓ ₹<?= number_format(
                                                abs($difference),
                                                2
                                            ) ?>
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="change price-same"
                                        >
                                            No Change
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $row["update_date"]
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $row["updated_by"]
                                    ) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                <h3>No Price History Found</h3>
                <p>Price updates will appear here after the admin changes a product price.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>