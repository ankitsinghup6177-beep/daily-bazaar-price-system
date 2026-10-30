<?php

session_start();

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
    header("Location: ../auth/login.php?error=Please login first.");
    exit;
}

if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../products/index.php?error=Access denied.");
    exit;
}


/*
|--------------------------------------------------------------------------
| ADMIN NAME
|--------------------------------------------------------------------------
*/

$adminName = $_SESSION["user_name"] ?? "Admin";


/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/

function redirectWithMessage($type, $message)
{
    header(
        "Location: products.php?" .
        $type .
        "=" .
        urlencode($message)
    );
    exit;
}


/*
|--------------------------------------------------------------------------
| ADD PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_POST["add_product"])) {

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $unit = trim($_POST["unit"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $image = trim($_POST["image"] ?? "");
    $status = trim($_POST["status"] ?? "active");


    if (
        $name === "" ||
        $category === "" ||
        $price === "" ||
        $unit === ""
    ) {
        redirectWithMessage(
            "error",
            "Please fill all required fields."
        );
    }


    if (!in_array(
        $category,
        ["Vegetables", "Fruits", "Grocery"],
        true
    )) {
        redirectWithMessage(
            "error",
            "Invalid category selected."
        );
    }


    if (!is_numeric($price) || (float)$price < 0) {
        redirectWithMessage(
            "error",
            "Invalid product price."
        );
    }


    if (!in_array(
        $status,
        ["active", "inactive"],
        true
    )) {
        $status = "active";
    }


    $stmt = $conn->prepare(
        "INSERT INTO products
        (
            name,
            category,
            price,
            unit,
            description,
            image,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );


    if (!$stmt) {
        redirectWithMessage(
            "error",
            "Database error while preparing product."
        );
    }


    $priceValue = (float)$price;


    $stmt->bind_param(
        "ssdssss",
        $name,
        $category,
        $priceValue,
        $unit,
        $description,
        $image,
        $status
    );


    if ($stmt->execute()) {

        $stmt->close();

        redirectWithMessage(
            "success",
            "Product added successfully."
        );
    }


    $stmt->close();

    redirectWithMessage(
        "error",
        "Unable to add product."
    );
}


/*
|--------------------------------------------------------------------------
| EDIT PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_POST["edit_product"])) {

    $productId = (int)($_POST["product_id"] ?? 0);

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $unit = trim($_POST["unit"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $image = trim($_POST["image"] ?? "");
    $status = trim($_POST["status"] ?? "active");


    if ($productId <= 0) {
        redirectWithMessage(
            "error",
            "Invalid product ID."
        );
    }


    if (
        $name === "" ||
        $category === "" ||
        $price === "" ||
        $unit === ""
    ) {
        redirectWithMessage(
            "error",
            "Please fill all required fields."
        );
    }


    if (!in_array(
        $category,
        ["Vegetables", "Fruits", "Grocery"],
        true
    )) {
        redirectWithMessage(
            "error",
            "Invalid category selected."
        );
    }


    if (!is_numeric($price) || (float)$price < 0) {
        redirectWithMessage(
            "error",
            "Invalid product price."
        );
    }


    if (!in_array(
        $status,
        ["active", "inactive"],
        true
    )) {
        $status = "active";
    }


    $stmt = $conn->prepare(
        "UPDATE products
         SET
            name = ?,
            category = ?,
            price = ?,
            unit = ?,
            description = ?,
            image = ?,
            status = ?
         WHERE id = ?"
    );


    if (!$stmt) {
        redirectWithMessage(
            "error",
            "Database error while updating product."
        );
    }


    $priceValue = (float)$price;


    $stmt->bind_param(
        "ssdssssi",
        $name,
        $category,
        $priceValue,
        $unit,
        $description,
        $image,
        $status,
        $productId
    );


    if ($stmt->execute()) {

        $stmt->close();

        redirectWithMessage(
            "success",
            "Product updated successfully."
        );
    }


    $stmt->close();

    redirectWithMessage(
        "error",
        "Unable to update product."
    );
}


/*
|--------------------------------------------------------------------------
| TOGGLE ACTIVE / INACTIVE
|--------------------------------------------------------------------------
*/

if (isset($_POST["toggle_status"])) {

    $productId = (int)($_POST["product_id"] ?? 0);


    if ($productId <= 0) {
        redirectWithMessage(
            "error",
            "Invalid product ID."
        );
    }


    $stmt = $conn->prepare(
        "SELECT status
         FROM products
         WHERE id = ?
         LIMIT 1"
    );


    $stmt->bind_param(
        "i",
        $productId
    );

    $stmt->execute();

    $resultStatus = $stmt->get_result();


    if ($resultStatus->num_rows !== 1) {

        $stmt->close();

        redirectWithMessage(
            "error",
            "Product not found."
        );
    }


    $product = $resultStatus->fetch_assoc();

    $stmt->close();


    $newStatus =
        $product["status"] === "active"
            ? "inactive"
            : "active";


    $stmt = $conn->prepare(
        "UPDATE products
         SET status = ?
         WHERE id = ?"
    );


    $stmt->bind_param(
        "si",
        $newStatus,
        $productId
    );


    if ($stmt->execute()) {

        $stmt->close();

        redirectWithMessage(
            "success",
            "Product status changed successfully."
        );
    }


    $stmt->close();

    redirectWithMessage(
        "error",
        "Unable to change product status."
    );
}


/*
|--------------------------------------------------------------------------
| DELETE PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_POST["delete_product"])) {

    $productId = (int)($_POST["product_id"] ?? 0);


    if ($productId <= 0) {
        redirectWithMessage(
            "error",
            "Invalid product ID."
        );
    }


    $stmt = $conn->prepare(
        "DELETE FROM products
         WHERE id = ?"
    );


    $stmt->bind_param(
        "i",
        $productId
    );


    if ($stmt->execute()) {

        $stmt->close();

        redirectWithMessage(
            "success",
            "Product deleted successfully."
        );
    }


    $stmt->close();

    redirectWithMessage(
        "error",
        "Unable to delete product."
    );
}


/*
|--------------------------------------------------------------------------
| EDIT PRODUCT DATA
|--------------------------------------------------------------------------
*/

$editProduct = null;


if (isset($_GET["edit"])) {

    $editId = (int)$_GET["edit"];


    if ($editId > 0) {

        $stmt = $conn->prepare(
            "SELECT
                id,
                name,
                category,
                price,
                unit,
                description,
                image,
                status
             FROM products
             WHERE id = ?
             LIMIT 1"
        );


        $stmt->bind_param(
            "i",
            $editId
        );


        $stmt->execute();

        $editResult = $stmt->get_result();


        if ($editResult->num_rows === 1) {

            $editProduct =
                $editResult->fetch_assoc();
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH / FILTER
|--------------------------------------------------------------------------
*/

$search = trim(
    $_GET["search"] ?? ""
);

$categoryFilter = trim(
    $_GET["category"] ?? ""
);


/*
|--------------------------------------------------------------------------
| PRODUCT LIST QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        category,
        price,
        unit,
        description,
        image,
        status
    FROM products
    WHERE 1 = 1
";


$params = [];

$types = "";


/*
| Search
*/

if ($search !== "") {

    $sql .= "
        AND (
            name LIKE ?
            OR description LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ss";
}


/*
| Category
*/

if (
    in_array(
        $categoryFilter,
        ["Vegetables", "Fruits", "Grocery"],
        true
    )
) {

    $sql .= " AND category = ? ";

    $params[] = $categoryFilter;

    $types .= "s";
}


/*
| IMPORTANT:
| Ascending order
*/

$sql .= "
    ORDER BY
        category ASC,
        name ASC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die(
        "Database query preparation failed: " .
        htmlspecialchars($conn->error)
    );
}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$result = $stmt->get_result();

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
    Manage Products - Daily Bazaar
</title>


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


.admin-area {

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


/* CONTAINER */

.container {

    max-width: 1250px;

    margin: 35px auto;

    padding: 0 20px;
}


.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

    gap: 20px;
}


.page-header h1 {

    color: #198754;

    margin: 0 0 7px;

    font-size: 30px;
}


.page-header p {

    margin: 0;

    color: #666;
}


.dashboard-btn {

    background: #198754;

    color: white;

    text-decoration: none;

    padding: 11px 17px;

    border-radius: 7px;

    font-weight: bold;
}


/* ALERT */

.alert {

    padding: 13px 17px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-weight: bold;
}


.success {

    background: #d1e7dd;

    color: #0f5132;
}


.error {

    background: #f8d7da;

    color: #842029;
}


/* FORM */

.form-card {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    margin-bottom: 30px;
}


.form-card h2 {

    margin-top: 0;

    color: #198754;
}


.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 7px;
}


.form-group.full {

    grid-column: 1 / -1;
}


label {

    font-weight: bold;

    font-size: 14px;
}


input,
select,
textarea {

    width: 100%;

    padding: 11px 12px;

    border: 1px solid #ccc;

    border-radius: 7px;

    font-size: 15px;

    outline: none;
}


input:focus,
select:focus,
textarea:focus {

    border-color: #198754;
}


textarea {

    min-height: 90px;

    resize: vertical;
}


.form-buttons {

    display: flex;

    gap: 10px;

    margin-top: 20px;
}


.btn {

    border: none;

    padding: 11px 18px;

    border-radius: 7px;

    cursor: pointer;

    font-weight: bold;

    text-decoration: none;

    display: inline-block;
}


.btn-success {

    background: #198754;

    color: white;
}


.btn-secondary {

    background: #6c757d;

    color: white;
}


/* SEARCH */

.search-card {

    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    margin-bottom: 25px;
}


.search-form {

    display: grid;

    grid-template-columns:
        2fr 1fr auto auto;

    gap: 12px;

    align-items: end;
}


/* TABLE */

.table-card {

    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    overflow: hidden;
}


.table-wrapper {

    width: 100%;

    overflow-x: auto;
}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1050px;
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


tr:hover {

    background: #f8f9fa;
}


.product-image {

    width: 65px;

    height: 55px;

    object-fit: cover;

    border-radius: 7px;

    background: #e9f5ee;
}


.category {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 15px;

    background: #e8f8ef;

    color: #198754;

    font-size: 12px;

    font-weight: bold;
}


.price {

    color: #198754;

    font-weight: bold;

    font-size: 17px;
}


.status {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 15px;

    font-size: 12px;

    font-weight: bold;
}


.active {

    background: #d1e7dd;

    color: #0f5132;
}


.inactive {

    background: #f8d7da;

    color: #842029;
}


.actions-cell {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;
}


.action-btn {

    border: none;

    padding: 7px 10px;

    border-radius: 6px;

    cursor: pointer;

    font-weight: bold;

    text-decoration: none;

    font-size: 12px;
}


.edit-btn {

    background: #ffc107;

    color: #222;
}


.toggle-btn {

    background: #0dcaf0;

    color: #222;
}


.delete-btn {

    background: #dc3545;

    color: white;
}


.empty {

    text-align: center;

    padding: 40px;

    color: #666;
}


/* RESPONSIVE */

@media (max-width: 800px) {

    .form-grid {

        grid-template-columns: 1fr;
    }


    .form-group.full {

        grid-column: auto;
    }


    .search-form {

        grid-template-columns: 1fr;
    }


    .page-header {

        align-items: flex-start;

        flex-direction: column;
    }

}


@media (max-width: 600px) {

    .navbar {

        padding: 14px 18px;
    }


    .admin-area span {

        display: none;
    }

}

</style>

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar">

    <a
        href="../index.php"
        class="brand"
    >
        Daily Bazaar
    </a>


    <div class="admin-area">

        <span>

            👤
            <?= htmlspecialchars($adminName) ?>

        </span>


        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- MAIN -->

<main class="container">


<!-- PAGE HEADER -->

<div class="page-header">

    <div>

        <h1>
            Product Management
        </h1>

        <p>
            Add, edit, delete and manage
            Daily Bazaar products.
        </p>

    </div>


    <a
        href="../admin/dashboard.php"
        class="dashboard-btn"
    >
        ← Dashboard
    </a>

</div>


<!-- ALERTS -->

<?php if (isset($_GET["success"])): ?>

    <div class="alert success">

        <?= htmlspecialchars(
            $_GET["success"]
        ) ?>

    </div>

<?php endif; ?>


<?php if (isset($_GET["error"])): ?>

    <div class="alert error">

        <?= htmlspecialchars(
            $_GET["error"]
        ) ?>

    </div>

<?php endif; ?>


<!-- ADD / EDIT FORM -->

<div class="form-card">

    <h2>

        <?= $editProduct
            ? "✏️ Edit Product"
            : "➕ Add New Product"
        ?>

    </h2>


    <form
        method="POST"
        action="products.php"
    >

        <?php if ($editProduct): ?>

            <input
                type="hidden"
                name="product_id"
                value="<?= (int)$editProduct["id"] ?>"
            >

        <?php endif; ?>


        <div class="form-grid">


            <div class="form-group">

                <label>
                    Product Name *
                </label>

                <input
                    type="text"
                    name="name"
                    required
                    maxlength="150"
                    placeholder="Example: Potato"
                    value="<?= htmlspecialchars(
                        $editProduct["name"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    Category *
                </label>

                <select
                    name="category"
                    required
                >

                    <option value="">
                        Select Category
                    </option>

                    <?php

                    $categories = [
                        "Vegetables",
                        "Fruits",
                        "Grocery"
                    ];

                    foreach ($categories as $cat):

                    ?>

                        <option
                            value="<?= htmlspecialchars($cat) ?>"
                            <?= (
                                ($editProduct["category"] ?? "")
                                === $cat
                            )
                                ? "selected"
                                : ""
                            ?>
                        >

                            <?= htmlspecialchars($cat) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Price (₹) *
                </label>

                <input
                    type="number"
                    name="price"
                    required
                    min="0"
                    step="0.01"
                    placeholder="Example: 40"
                    value="<?= htmlspecialchars(
                        $editProduct["price"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    Unit *
                </label>

                <input
                    type="text"
                    name="unit"
                    required
                    maxlength="30"
                    placeholder="kg / litre / piece"
                    value="<?= htmlspecialchars(
                        $editProduct["unit"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    Image File Name
                </label>

                <input
                    type="text"
                    name="image"
                    maxlength="255"
                    placeholder="potato.jpg"
                    value="<?= htmlspecialchars(
                        $editProduct["image"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    Status
                </label>

                <select name="status">

                    <option
                        value="active"
                        <?= (
                            ($editProduct["status"] ?? "active")
                            === "active"
                        )
                            ? "selected"
                            : ""
                        ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?= (
                            ($editProduct["status"] ?? "")
                            === "inactive"
                        )
                            ? "selected"
                            : ""
                        ?>
                    >
                        Inactive
                    </option>

                </select>

            </div>


            <div class="form-group full">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    maxlength="255"
                    placeholder="Enter product description..."
                ><?= htmlspecialchars(
                    $editProduct["description"] ?? ""
                ) ?></textarea>

            </div>


        </div>


        <div class="form-buttons">

            <?php if ($editProduct): ?>

                <button
                    type="submit"
                    name="edit_product"
                    class="btn btn-success"
                >
                    Update Product
                </button>


                <a
                    href="products.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            <?php else: ?>

                <button
                    type="submit"
                    name="add_product"
                    class="btn btn-success"
                >
                    Add Product
                </button>

            <?php endif; ?>

        </div>

    </form>

</div>


<!-- SEARCH -->

<div class="search-card">

    <form
        method="GET"
        class="search-form"
    >

        <div class="form-group">

            <label>
                Search
            </label>

            <input
                type="text"
                name="search"
                placeholder="Search product..."
                value="<?= htmlspecialchars($search) ?>"
            >

        </div>


        <div class="form-group">

            <label>
                Category
            </label>

            <select name="category">

                <option value="">
                    All Categories
                </option>

                <option
                    value="Vegetables"
                    <?= $categoryFilter === "Vegetables"
                        ? "selected"
                        : ""
                    ?>
                >
                    Vegetables
                </option>

                <option
                    value="Fruits"
                    <?= $categoryFilter === "Fruits"
                        ? "selected"
                        : ""
                    ?>
                >
                    Fruits
                </option>

                <option
                    value="Grocery"
                    <?= $categoryFilter === "Grocery"
                        ? "selected"
                        : ""
                    ?>
                >
                    Grocery
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn btn-success"
        >
            🔎 Search
        </button>


        <a
            href="products.php"
            class="btn btn-secondary"
        >
            Reset
        </a>

    </form>

</div>


<!-- PRODUCT TABLE -->

<div class="table-card">

<div class="table-wrapper">


<?php if ($result->num_rows > 0): ?>

<table>

<thead>

<tr>

    <th>#</th>

    <th>Image</th>

    <th>Product</th>

    <th>Category</th>

    <th>Price</th>

    <th>Unit</th>

    <th>Status</th>

    <th>Actions</th>

</tr>

</thead>


<tbody>


<?php while (
    $product = $result->fetch_assoc()
): ?>

<tr>


<td>
    <?= (int)$product["id"] ?>
</td>


<td>

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
    onerror="this.style.display='none';"
>

<?php else: ?>

<div class="product-image"></div>

<?php endif; ?>

</td>


<td>

<strong>

<?= htmlspecialchars(
    $product["name"]
) ?>

</strong>


<?php if (
    !empty($product["description"])
): ?>

<br>

<small style="color:#777">

<?= htmlspecialchars(
    $product["description"]
) ?>

</small>

<?php endif; ?>

</td>


<td>

<span class="category">

<?= htmlspecialchars(
    $product["category"]
) ?>

</span>

</td>


<td>

<span class="price">

₹<?= number_format(
    (float)$product["price"],
    2
) ?>

</span>

</td>


<td>

<?= htmlspecialchars(
    $product["unit"]
) ?>

</td>


<td>

<span
    class="status <?= $product["status"] === "active"
        ? "active"
        : "inactive"
    ?>"
>

<?= ucfirst(
    htmlspecialchars(
        $product["status"]
    )
) ?>

</span>

</td>


<td>

<div class="actions-cell">


<!-- EDIT -->

<a
    href="products.php?edit=<?= (int)$product["id"] ?>"
    class="action-btn edit-btn"
>
    ✏️ Edit
</a>


<!-- TOGGLE -->

<form
    method="POST"
    style="display:inline;"
>

<input
    type="hidden"
    name="product_id"
    value="<?= (int)$product["id"] ?>"
>


<button
    type="submit"
    name="toggle_status"
    class="action-btn toggle-btn"
>

<?= $product["status"] === "active"
    ? "🔴 Deactivate"
    : "🟢 Activate"
?>

</button>

</form>


<!-- DELETE -->

<form
    method="POST"
    style="display:inline;"
    onsubmit="return confirm(
        'Are you sure you want to permanently delete this product?'
    );"
>

<input
    type="hidden"
    name="product_id"
    value="<?= (int)$product["id"] ?>"
>


<button
    type="submit"
    name="delete_product"
    class="action-btn delete-btn"
>
    🗑️ Delete
</button>

</form>


</div>

</td>


</tr>

<?php endwhile; ?>


</tbody>

</table>


<?php else: ?>


<div class="empty">

<h2>
    No Products Found
</h2>

<p>
    Add a product or change
    your search/filter.
</p>

</div>


<?php endif; ?>


</div>

</div>


</main>


</body>

</html>

<?php

$stmt->close();

$conn->close();

?>