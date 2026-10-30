<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {
    header("Location: ../auth/login.php?error=Please login first.");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
|
| Products table se latest products fetch honge.
|
*/

$sql = "
    SELECT
        id,
        name,
        category,
        price,
        unit,
        description,
        image
    FROM products
    ORDER BY id DESC
";

$result = $conn->query($sql);


/*
|--------------------------------------------------------------------------
| DATABASE QUERY ERROR
|--------------------------------------------------------------------------
*/

if ($result === false) {
    die(
        "<h2>Database Query Error</h2>" .
        "<p>" . htmlspecialchars($conn->error) . "</p>"
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Products - Daily Bazaar</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7f6;

            color: #222;
        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            background: #198754;

            padding: 16px 30px;

            color: white;

            display: flex;

            justify-content: space-between;

            align-items: center;
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


        .nav-right a {

            color: white;

            text-decoration: none;
        }


        .logout {

            background: #ffc107;

            color: #222 !important;

            padding: 8px 15px;

            border-radius: 6px;

            font-weight: bold;
        }


        .admin-link {

            background: white;

            color: #198754 !important;

            padding: 8px 15px;

            border-radius: 6px;

            font-weight: bold;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            max-width: 1200px;

            margin: 35px auto;

            padding: 0 20px;
        }


        /* =========================
           HEADING
        ========================= */

        .heading {

            text-align: center;

            margin-bottom: 35px;
        }


        .heading h1 {

            color: #198754;

            font-size: 32px;

            margin-bottom: 8px;
        }


        .heading p {

            color: #666;
        }


        /* =========================
           PRODUCT GRID
        ========================= */

        .products-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 22px;
        }


        .product-card {

            background: white;

            border-radius: 12px;

            padding: 22px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);

            transition: 0.2s;
        }


        .product-card:hover {

            transform: translateY(-4px);
        }


        .product-image {

            width: 100%;

            height: 170px;

            object-fit: cover;

            border-radius: 8px;

            background: #e9f5ee;

            margin-bottom: 15px;

            display: block;
        }


        .category {

            display: inline-block;

            background: #e8f8ef;

            color: #198754;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            margin-bottom: 10px;
        }


        .product-name {

            font-size: 21px;

            font-weight: bold;

            margin-bottom: 8px;
        }


        .description {

            color: #666;

            font-size: 14px;

            min-height: 40px;

            margin-bottom: 15px;
        }


        .price {

            color: #198754;

            font-size: 24px;

            font-weight: bold;
        }


        .unit {

            color: #777;

            font-size: 14px;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {

            background: white;

            padding: 40px;

            text-align: center;

            border-radius: 12px;

            color: #666;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }


        /* =========================
           ERROR
        ========================= */

        .error-box {

            background: #ffe5e5;

            color: #b00020;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 25px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .products-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            .navbar {

                padding: 15px;
            }


            .nav-right span {

                display: none;
            }


            .products-grid {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">


    <a
        href="../index.php"
        class="brand"
    >
        Daily Bazaar
    </a>


    <div class="nav-right">


        <span>

            Welcome,
            <?= htmlspecialchars(
                $_SESSION["user_name"] ?? "User"
            ) ?>

        </span>


        <?php if (
            isset($_SESSION["user_role"]) &&
            $_SESSION["user_role"] === "admin"
        ): ?>

            <a
                href="../admin/dashboard.php"
                class="admin-link"
            >
                Admin
            </a>

        <?php endif; ?>


        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>


    </div>


</nav>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="container">


    <div class="heading">

        <h1>
            Today's Bazaar Prices
        </h1>

        <p>
            Fresh vegetable, fruit and grocery prices
        </p>

    </div>



    <?php if ($result->num_rows > 0): ?>


        <!-- =================================================
             PRODUCT GRID
        ================================================= -->

        <div class="products-grid">


            <?php while (
                $product = $result->fetch_assoc()
            ): ?>


                <div class="product-card">


                    <!-- PRODUCT IMAGE -->

                    <?php if (
                        !empty($product["image"])
                    ): ?>

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

                        <div class="product-image"></div>

                    <?php endif; ?>



                    <!-- CATEGORY -->

                    <span class="category">

                        <?= htmlspecialchars(
                            $product["category"]
                        ) ?>

                    </span>



                    <!-- PRODUCT NAME -->

                    <div class="product-name">

                        <?= htmlspecialchars(
                            $product["name"]
                        ) ?>

                    </div>



                    <!-- DESCRIPTION -->

                    <div class="description">

                        <?= htmlspecialchars(
                            $product["description"] ?? ""
                        ) ?>

                    </div>



                    <!-- PRICE -->

                    <div>

                        <span class="price">

                            ₹<?= number_format(
                                (float)$product["price"],
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


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- =================================================
             NO PRODUCTS
        ================================================= -->

        <div class="empty">

            <h2>
                No Products Available
            </h2>

            <p>
                Products will appear here once the admin
                adds them.
            </p>

        </div>


    <?php endif; ?>


</div>

<script src="js/script.js"></script>
</body>

</html>


<?php

/*
|--------------------------------------------------------------------------
| CLOSE DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$conn->close();

?>