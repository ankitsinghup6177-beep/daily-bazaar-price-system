<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {
    header("Location: ../auth/login.php?error=" . urlencode("Please login first."));
    exit;
}

if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "user"
) {
    header("Location: ../products/index.php?error=" . urlencode("User access only."));
    exit;
}

require_once "../config/db.php";

$userName = $_SESSION["user_name"] ?? "User";
$userEmail = $_SESSION["user_email"] ?? "";


/*
|--------------------------------------------------------------------------
| PRODUCT STATISTICS
|--------------------------------------------------------------------------
*/

$totalProducts = 0;
$totalVegetables = 0;
$totalFruits = 0;
$totalGrocery = 0;


/* Total active products */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'active'"
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalProducts = (int) $row["total"];
}

$stmt->close();


/* Vegetables */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'active'
     AND category = 'Vegetables'"
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalVegetables = (int) $row["total"];
}

$stmt->close();


/* Fruits */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'active'
     AND category = 'Fruits'"
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalFruits = (int) $row["total"];
}

$stmt->close();


/* Grocery */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'active'
     AND category = 'Grocery'"
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalGrocery = (int) $row["total"];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| LATEST ACTIVE PRODUCTS
|--------------------------------------------------------------------------
*/

$products = [];

$stmt = $conn->prepare(
    "SELECT
        id,
        name,
        category,
        price,
        unit,
        description,
        image
     FROM products
     WHERE status = 'active'
     ORDER BY
        CASE category
            WHEN 'Vegetables' THEN 1
            WHEN 'Fruits' THEN 2
            WHEN 'Grocery' THEN 3
            ELSE 4
        END,
        name ASC
     LIMIT 12"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        User Dashboard - Daily Bazaar
    </title>


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

            line-height: 1.5;
        }


        /* ==================================================
           NAVBAR
        ================================================== */

        .navbar {

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #157347
                );

            color: white;

            padding: 16px 35px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            box-shadow:
                0 3px 12px
                rgba(0, 0, 0, 0.12);
        }


        .brand {

            color: white;

            text-decoration: none;

            font-size: 25px;

            font-weight: 700;
        }


        .nav-right {

            display: flex;

            align-items: center;

            gap: 18px;
        }


        .user-name {

            font-size: 15px;

            font-weight: 500;
        }


        .logout-btn {

            background: #ffc107;

            color: #212529;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 700;

            transition: 0.2s;
        }


        .logout-btn:hover {

            background: #ffca2c;

            transform: translateY(-1px);
        }


        /* ==================================================
           MAIN CONTAINER
        ================================================== */

        .container {

            max-width: 1200px;

            margin: 35px auto;

            padding:
                0 20px;
        }


        /* ==================================================
           WELCOME
        ================================================== */

        .welcome {

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #20a064
                );

            color: white;

            border-radius: 15px;

            padding: 32px;

            margin-bottom: 30px;

            box-shadow:
                0 7px 25px
                rgba(25, 135, 84, 0.20);
        }


        .welcome h1 {

            font-size: 31px;

            margin-bottom: 7px;
        }


        .welcome p {

            opacity: 0.92;

            font-size: 16px;
        }


        /* ==================================================
           STATISTICS
        ================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 35px;
        }


        .stat-card {

            background: white;

            border-radius: 13px;

            padding: 23px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.07);

            border-left:
                5px solid #198754;

            transition:
                transform 0.2s,
                box-shadow 0.2s;
        }


        .stat-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 9px 25px
                rgba(0, 0, 0, 0.10);
        }


        .stat-title {

            color: #6c757d;

            font-size: 14px;

            margin-bottom: 8px;

            font-weight: 600;
        }


        .stat-number {

            color: #198754;

            font-size: 30px;

            font-weight: 700;
        }


        /* ==================================================
           SECTION HEADER
        ================================================== */

        .section-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;
        }


        .section-header h2 {

            color: #198754;

            font-size: 24px;
        }


        .view-all {

            background: #198754;

            color: white;

            text-decoration: none;

            padding: 9px 15px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 600;
        }


        .view-all:hover {

            background: #157347;
        }


        /* ==================================================
           PRODUCT GRID
        ================================================== */

        .products-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 22px;
        }


        .product-card {

            background: white;

            border-radius: 13px;

            overflow: hidden;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.07);

            transition:
                transform 0.2s,
                box-shadow 0.2s;
        }


        .product-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 10px 28px
                rgba(0, 0, 0, 0.11);
        }


        .product-image {

            width: 100%;

            height: 180px;

            object-fit: cover;

            display: block;

            background: #e9f5ee;
        }


        .no-image {

            height: 180px;

            background:
                linear-gradient(
                    135deg,
                    #e8f8ef,
                    #d1e7dd
                );

            display: flex;

            align-items: center;

            justify-content: center;

            color: #198754;

            font-size: 45px;
        }


        .product-content {

            padding: 20px;
        }


        .category {

            display: inline-block;

            background: #e8f8ef;

            color: #198754;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 10px;
        }


        .product-name {

            font-size: 20px;

            font-weight: 700;

            margin-bottom: 7px;
        }


        .description {

            color: #6c757d;

            font-size: 13px;

            min-height: 39px;

            margin-bottom: 14px;
        }


        .price-row {

            display: flex;

            align-items: baseline;

            gap: 5px;
        }


        .price {

            color: #198754;

            font-size: 23px;

            font-weight: 700;
        }


        .unit {

            color: #777;

            font-size: 13px;
        }


        /* ==================================================
           EMPTY STATE
        ================================================== */

        .empty {

            background: white;

            padding: 45px 25px;

            border-radius: 13px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.07);
        }


        .empty h3 {

            color: #198754;

            margin-bottom: 8px;
        }


        .empty p {

            color: #777;
        }


        /* ==================================================
           FOOTER
        ================================================== */

        .footer {

            text-align: center;

            color: #777;

            font-size: 13px;

            padding: 30px 20px;
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 950px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .products-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 650px) {

            .navbar {

                padding:
                    14px 18px;
            }


            .brand {

                font-size: 21px;
            }


            .user-name {

                display: none;
            }


            .container {

                margin:
                    25px auto;
            }


            .welcome {

                padding: 25px;
            }


            .welcome h1 {

                font-size: 25px;
            }


            .stats {

                grid-template-columns: 1fr;
            }


            .products-grid {

                grid-template-columns: 1fr;
            }


            .section-header {

                align-items:
                    flex-start;

                gap: 10px;

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<!-- ==================================================
     NAVBAR
================================================== -->

<nav class="navbar">


    <a
        href="../index.php"
        class="brand"
    >
        Daily Bazaar
    </a>


    <div class="nav-right">

        <span class="user-name">

            👤
            <?= htmlspecialchars($userName) ?>

        </span>


        <a
            href="../auth/logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>



<!-- ==================================================
     MAIN
================================================== -->

<main class="container">


    <!-- WELCOME -->

    <section class="welcome">

        <h1>
            Welcome, <?= htmlspecialchars($userName) ?> 👋
        </h1>

        <p>
            Check today's fresh vegetable,
            fruit and grocery prices.
        </p>

    </section>



    <!-- ==================================================
         STATISTICS
    ================================================== -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-title">
                Available Products
            </div>

            <div class="stat-number">
                <?= $totalProducts ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Vegetables
            </div>

            <div class="stat-number">
                <?= $totalVegetables ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Fruits
            </div>

            <div class="stat-number">
                <?= $totalFruits ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Grocery
            </div>

            <div class="stat-number">
                <?= $totalGrocery ?>
            </div>

        </div>


    </section>



    <!-- ==================================================
         PRODUCTS
    ================================================== -->

    <div class="section-header">


        <h2>
            Today's Prices
        </h2>


        <a
            href="../products/index.php"
            class="view-all"
        >
            View All Products →
        </a>


    </div>



    <?php if (count($products) > 0): ?>


        <section class="products-grid">


            <?php foreach ($products as $product): ?>


                <article class="product-card">


                    <?php if (!empty($product["image"])): ?>

                        <img
                            src="../images/<?= htmlspecialchars(
                                $product["image"]
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $product["name"]
                            ) ?>"
                            class="product-image"
                        >

                    <?php else: ?>

                        <div class="no-image">
                            🛒
                        </div>

                    <?php endif; ?>


                    <div class="product-content">


                        <span class="category">

                            <?= htmlspecialchars(
                                $product["category"]
                            ) ?>

                        </span>


                        <div class="product-name">

                            <?= htmlspecialchars(
                                $product["name"]
                            ) ?>

                        </div>


                        <div class="description">

                            <?= htmlspecialchars(
                                $product["description"] ?? ""
                            ) ?>

                        </div>


                        <div class="price-row">

                            <span class="price">

                                ₹<?= number_format(
                                    (float) $product["price"],
                                    2
                                ) ?>

                            </span>


                            <span class="unit">

                                /
                                <?= htmlspecialchars(
                                    $product["unit"]
                                ) ?>

                            </span>

                        </div>


                    </div>


                </article>


            <?php endforeach; ?>


        </section>


    <?php else: ?>


        <div class="empty">

            <h3>
                No Products Available
            </h3>

            <p>
                Products will appear here once
                the administrator adds them.
            </p>

        </div>


    <?php endif; ?>


</main>



<!-- FOOTER -->

<footer class="footer">

    © <?= date("Y") ?>
    Daily Bazaar.
    All Rights Reserved.

</footer>


</body>

</html>