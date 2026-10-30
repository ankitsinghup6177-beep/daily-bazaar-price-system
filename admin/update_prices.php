<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {
    header("Location: ../auth/login.php?error=" . urlencode("Please login first."));
    exit;
}

if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../products/index.php?error=" . urlencode("Admin access required."));
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| UPDATE PRICE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_id = (int) ($_POST["product_id"] ?? 0);

    $new_price = trim($_POST["new_price"] ?? "");

    $update_date = $_POST["update_date"] ?? date("Y-m-d");


    if ($product_id <= 0) {

        $error = "Please select a valid product.";

    } elseif (
        $new_price === "" ||
        !is_numeric($new_price) ||
        $new_price < 0
    ) {

        $error = "Please enter a valid price.";

    } else {

        $new_price = (float) $new_price;


        /*
        |--------------------------------------------------------------------------
        | GET CURRENT PRODUCT PRICE
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id, name, price
             FROM products
             WHERE id = ?
             LIMIT 1"
        );


        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("i", $product_id);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows !== 1) {

                $error = "Product not found.";

            } else {

                $product = $result->fetch_assoc();

                $old_price = (float) $product["price"];


                /*
                |--------------------------------------------------------------------------
                | CHECK SAME PRICE
                |--------------------------------------------------------------------------
                */

                if ($old_price === $new_price) {

                    $error = "New price is same as current price.";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | START TRANSACTION
                    |--------------------------------------------------------------------------
                    */

                    $conn->begin_transaction();


                    try {


                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE PRODUCTS TABLE
                        |--------------------------------------------------------------------------
                        */

                        $updateStmt = $conn->prepare(
                            "UPDATE products
                             SET price = ?,
                                 updated_at = CURRENT_TIMESTAMP
                             WHERE id = ?"
                        );


                        if (!$updateStmt) {
                            throw new Exception($conn->error);
                        }


                        $updateStmt->bind_param(
                            "di",
                            $new_price,
                            $product_id
                        );


                        if (!$updateStmt->execute()) {
                            throw new Exception($updateStmt->error);
                        }


                        $updateStmt->close();


                        /*
                        |--------------------------------------------------------------------------
                        | INSERT PRICE HISTORY
                        |--------------------------------------------------------------------------
                        */

                        $historyStmt = $conn->prepare(
                            "INSERT INTO price_updates
                             (
                                product_id,
                                old_price,
                                new_price,
                                updated_by,
                                update_date
                             )
                             VALUES (?, ?, ?, ?, ?)"
                        );


                        if (!$historyStmt) {
                            throw new Exception($conn->error);
                        }


                        $admin_id = (int) $_SESSION["user_id"];


                        $historyStmt->bind_param(
                            "iddis",
                            $product_id,
                            $old_price,
                            $new_price,
                            $admin_id,
                            $update_date
                        );


                        if (!$historyStmt->execute()) {
                            throw new Exception($historyStmt->error);
                        }


                        $historyStmt->close();


                        /*
                        |--------------------------------------------------------------------------
                        | COMMIT
                        |--------------------------------------------------------------------------
                        */

                        $conn->commit();


                        $message =
                            "Price updated successfully for " .
                            $product["name"] .
                            ".";


                    } catch (Exception $e) {

                        $conn->rollback();

                        $error =
                            "Price update failed: " .
                            $e->getMessage();
                    }
                }
            }


            $stmt->close();
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET ALL ACTIVE PRODUCTS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT id, name, category, price, unit
     FROM products
     WHERE status = 'active'
     ORDER BY category ASC, name ASC"
);


if (!$result) {

    die(
        "Database Query Error: " .
        htmlspecialchars($conn->error)
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

    <title>Update Prices - Daily Bazaar</title>


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


        /* NAVBAR */

        .navbar {

            background: #198754;

            color: white;

            padding: 16px 30px;

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


        .nav-right span {

            color: white;
        }


        .dashboard {

            background: white;

            color: #198754 !important;

            padding: 8px 15px;

            border-radius: 6px;

            text-decoration: none;

            font-weight: bold;
        }


        .logout {

            background: #ffc107;

            color: #222 !important;

            padding: 8px 15px;

            border-radius: 6px;

            text-decoration: none;

            font-weight: bold;
        }


        /* CONTAINER */

        .container {

            max-width: 1200px;

            margin: 35px auto;

            padding: 0 20px;
        }


        h1 {

            color: #198754;

            margin-bottom: 8px;
        }


        .subtitle {

            color: #666;

            margin-bottom: 30px;
        }


        /* ALERT */

        .alert {

            padding: 15px 18px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-weight: 500;
        }


        .success {

            background: #d1e7dd;

            color: #0f5132;
        }


        .error {

            background: #f8d7da;

            color: #842029;
        }


        /* CARD */

        .card {

            background: white;

            border-radius: 12px;

            padding: 25px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
        }


        .card h2 {

            margin-top: 0;

            color: #198754;
        }


        /* FORM */

        .form-grid {

            display: grid;

            grid-template-columns:
                2fr 1fr 1fr auto;

            gap: 15px;

            align-items: end;
        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;
        }


        select,
        input {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 7px;

            font-size: 15px;
        }


        select:focus,
        input:focus {

            outline: none;

            border-color: #198754;
        }


        .btn {

            border: none;

            padding: 11px 18px;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;
        }


        .btn-success {

            background: #198754;

            color: white;
        }


        .btn-success:hover {

            background: #157347;
        }


        /* TABLE */

        table {

            width: 100%;

            border-collapse: collapse;
        }


        th {

            background: #198754;

            color: white;

            padding: 13px;

            text-align: left;
        }


        td {

            padding: 13px;

            border-bottom: 1px solid #eee;
        }


        tr:hover {

            background: #f8f9fa;
        }


        .price {

            color: #198754;

            font-weight: bold;
        }


        .category {

            background: #e8f8ef;

            color: #198754;

            padding: 5px 9px;

            border-radius: 15px;

            font-size: 12px;
        }


        /* RESPONSIVE */

        @media (max-width: 850px) {

            .form-grid {

                grid-template-columns: 1fr;
            }


            .navbar {

                padding: 15px;
            }


            .nav-right span {

                display: none;
            }


            .card {

                overflow-x: auto;
            }


            table {

                min-width: 700px;
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
        href="dashboard.php"
        class="brand"
    >
        Daily Bazaar
    </a>


    <div class="nav-right">

        <span>

            Welcome,
            <?= htmlspecialchars(
                $_SESSION["user_name"] ?? "Admin"
            ) ?>

        </span>


        <a
            href="dashboard.php"
            class="dashboard"
        >
            Dashboard
        </a>


        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>


</nav>



<!-- =====================================================
     MAIN
===================================================== -->

<div class="container">


    <h1>
        Daily Price Update
    </h1>


    <p class="subtitle">
        Update today's market prices and automatically save price history.
    </p>



    <!-- ALERTS -->

    <?php if ($message !== ""): ?>

        <div class="alert success">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="alert error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>



    <!-- =================================================
         UPDATE PRICE FORM
    ================================================== -->

    <div class="card">

        <h2>
            Update Product Price
        </h2>


        <form
            method="POST"
            class="form-grid"
        >


            <div>

                <label>
                    Select Product
                </label>


                <select
                    name="product_id"
                    required
                >

                    <option value="">
                        -- Select Product --
                    </option>


                    <?php while (
                        $product = $result->fetch_assoc()
                    ): ?>

                        <option
                            value="<?= (int) $product["id"] ?>"
                        >

                            <?= htmlspecialchars(
                                $product["name"]
                            ) ?>

                            -
                            <?= htmlspecialchars(
                                $product["category"]
                            ) ?>

                            -
                            ₹<?= number_format(
                                (float) $product["price"],
                                2
                            ) ?>

                            / <?= htmlspecialchars(
                                $product["unit"]
                            ) ?>

                        </option>

                    <?php endwhile; ?>


                </select>

            </div>


            <div>

                <label>
                    New Price (₹)
                </label>


                <input
                    type="number"
                    name="new_price"
                    min="0"
                    step="0.01"
                    placeholder="Enter new price"
                    required
                >

            </div>


            <div>

                <label>
                    Update Date
                </label>


                <input
                    type="date"
                    name="update_date"
                    value="<?= date("Y-m-d") ?>"
                    required
                >

            </div>


            <div>

                <button
                    type="submit"
                    class="btn btn-success"
                >
                    Update Price
                </button>

            </div>


        </form>

    </div>



    <!-- =================================================
         CURRENT PRODUCTS
    ================================================== -->

    <div class="card">

        <h2>
            Current Product Prices
        </h2>


        <?php

        /*
        |--------------------------------------------------------------------------
        | Fetch Products Again
        |--------------------------------------------------------------------------
        */

        $productsResult = $conn->query(
            "SELECT id, name, category, price, unit
             FROM products
             WHERE status = 'active'
             ORDER BY category ASC, name ASC"
        );

        ?>


        <table>

            <thead>

                <tr>

                    <th>
                        #
                    </th>

                    <th>
                        Product
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Current Price
                    </th>

                    <th>
                        Unit
                    </th>

                </tr>

            </thead>


            <tbody>


                <?php if (
                    $productsResult &&
                    $productsResult->num_rows > 0
                ): ?>


                    <?php while (
                        $product = $productsResult->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>
                                <?= (int) $product["id"] ?>
                            </td>


                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $product["name"]
                                    ) ?>
                                </strong>
                            </td>


                            <td>

                                <span class="category">

                                    <?= htmlspecialchars(
                                        $product["category"]
                                    ) ?>

                                </span>

                            </td>


                            <td class="price">

                                ₹<?= number_format(
                                    (float) $product["price"],
                                    2
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $product["unit"]
                                ) ?>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="5"
                            style="text-align:center;"
                        >

                            No active products found.

                        </td>

                    </tr>


                <?php endif; ?>


            </tbody>

        </table>


    </div>


</div>


</body>

</html>

<?php

$conn->close();

?>