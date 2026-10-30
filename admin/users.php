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


$adminName = $_SESSION["user_name"] ?? "Admin";


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function redirectWithMessage($type, $message)
{
    header(
        "Location: users.php?" .
        $type . "=" .
        urlencode($message)
    );
    exit;
}


/*
|--------------------------------------------------------------------------
| POST ACTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | CHANGE ROLE
    |--------------------------------------------------------------------------
    */

    if (isset($_POST["change_role"])) {

        $userId = (int)($_POST["user_id"] ?? 0);

        $newRole = $_POST["role"] ?? "";

        if ($userId <= 0) {

            redirectWithMessage(
                "error",
                "Invalid user."
            );
        }


        if (
            !in_array(
                $newRole,
                ["user", "admin"],
                true
            )
        ) {

            redirectWithMessage(
                "error",
                "Invalid role selected."
            );
        }


        /*
        | Prevent admin from changing own role
        */

        if (
            $userId ===
            (int)($_SESSION["user_id"] ?? 0)
        ) {

            redirectWithMessage(
                "error",
                "You cannot change your own admin role."
            );
        }


        $stmt = $conn->prepare(
            "UPDATE users
             SET role = ?
             WHERE id = ?"
        );


        if (!$stmt) {

            redirectWithMessage(
                "error",
                "Database error: " . $conn->error
            );
        }


        $stmt->bind_param(
            "si",
            $newRole,
            $userId
        );


        if ($stmt->execute()) {

            $stmt->close();

            redirectWithMessage(
                "success",
                "User role updated successfully."
            );

        }


        $error = $stmt->error;

        $stmt->close();


        redirectWithMessage(
            "error",
            "Unable to update role: " . $error
        );
    }



    /*
    |--------------------------------------------------------------------------
    | TOGGLE STATUS
    |--------------------------------------------------------------------------
    */

    if (isset($_POST["toggle_status"])) {

        $userId = (int)($_POST["user_id"] ?? 0);


        if ($userId <= 0) {

            redirectWithMessage(
                "error",
                "Invalid user."
            );
        }


        /*
        | Prevent self deactivate
        */

        if (
            $userId ===
            (int)($_SESSION["user_id"] ?? 0)
        ) {

            redirectWithMessage(
                "error",
                "You cannot deactivate your own account."
            );
        }


        /*
        | Get current status
        */

        $stmt = $conn->prepare(
            "SELECT status
             FROM users
             WHERE id = ?
             LIMIT 1"
        );


        if (!$stmt) {

            redirectWithMessage(
                "error",
                "Database error."
            );
        }


        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        if (!$user) {

            redirectWithMessage(
                "error",
                "User not found."
            );
        }


        $newStatus =
            ($user["status"] === "active")
                ? "inactive"
                : "active";


        $stmt = $conn->prepare(
            "UPDATE users
             SET status = ?
             WHERE id = ?"
        );


        if (!$stmt) {

            redirectWithMessage(
                "error",
                "Database error."
            );
        }


        $stmt->bind_param(
            "si",
            $newStatus,
            $userId
        );


        if ($stmt->execute()) {

            $stmt->close();

            redirectWithMessage(
                "success",
                "User status changed to " .
                ucfirst($newStatus) .
                "."
            );
        }


        $error = $stmt->error;

        $stmt->close();


        redirectWithMessage(
            "error",
            "Unable to change user status: " . $error
        );
    }



    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    if (isset($_POST["delete_user"])) {

        $userId = (int)($_POST["user_id"] ?? 0);


        if ($userId <= 0) {

            redirectWithMessage(
                "error",
                "Invalid user."
            );
        }


        /*
        | Prevent deleting own account
        */

        if (
            $userId ===
            (int)($_SESSION["user_id"] ?? 0)
        ) {

            redirectWithMessage(
                "error",
                "You cannot delete your own account."
            );
        }


        $stmt = $conn->prepare(
            "DELETE FROM users
             WHERE id = ?"
        );


        if (!$stmt) {

            redirectWithMessage(
                "error",
                "Database error."
            );
        }


        $stmt->bind_param(
            "i",
            $userId
        );


        if ($stmt->execute()) {

            $stmt->close();

            redirectWithMessage(
                "success",
                "User deleted successfully."
            );
        }


        $error = $stmt->error;

        $stmt->close();


        redirectWithMessage(
            "error",
            "Unable to delete user: " . $error
        );
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH & FILTER
|--------------------------------------------------------------------------
*/

$search = trim(
    $_GET["search"] ?? ""
);

$roleFilter =
    $_GET["role"] ?? "";

$statusFilter =
    $_GET["status"] ?? "";


/*
|--------------------------------------------------------------------------
| BUILD QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        email,
        role,
        status,
        created_at
    FROM users
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
            OR email LIKE ?
        )
    ";

    $searchValue =
        "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ss";
}


/*
| Role filter
*/

if (
    in_array(
        $roleFilter,
        ["user", "admin"],
        true
    )
) {

    $sql .= "
        AND role = ?
    ";

    $params[] = $roleFilter;

    $types .= "s";
}


/*
| Status filter
*/

if (
    in_array(
        $statusFilter,
        ["active", "inactive"],
        true
    )
) {

    $sql .= "
        AND status = ?
    ";

    $params[] = $statusFilter;

    $types .= "s";
}


$sql .= "
    ORDER BY
        id DESC
";


/*
|--------------------------------------------------------------------------
| FETCH USERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

$users = [];


if ($stmt) {

    if (!empty($params)) {

        $stmt->bind_param(
            $types,
            ...$params
        );
    }


    $stmt->execute();

    $result =
        $stmt->get_result();


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $users[] = $row;
    }


    $stmt->close();

}


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalUsers = 0;
$totalAdmins = 0;
$totalNormalUsers = 0;
$totalActive = 0;
$totalInactive = 0;


/*
| Total users
*/

$query = $conn->query(
    "SELECT
        COUNT(*) AS total,
        SUM(role = 'admin') AS admins,
        SUM(role = 'user') AS normal_users,
        SUM(status = 'active') AS active_users,
        SUM(status = 'inactive') AS inactive_users
     FROM users"
);


if ($query) {

    $stats =
        $query->fetch_assoc();

    $totalUsers =
        (int)($stats["total"] ?? 0);

    $totalAdmins =
        (int)($stats["admins"] ?? 0);

    $totalNormalUsers =
        (int)($stats["normal_users"] ?? 0);

    $totalActive =
        (int)($stats["active_users"] ?? 0);

    $totalInactive =
        (int)($stats["inactive_users"] ?? 0);
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

<title>
    User Management - Daily Bazaar
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


/* ==================================================
   NAVBAR
================================================== */

.navbar {

    background: #198754;

    color: white;

    padding: 16px 30px;

    display: flex;

    justify-content:
        space-between;

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


/* ==================================================
   CONTAINER
================================================== */

.container {

    max-width: 1250px;

    margin: 35px auto;

    padding: 0 20px;
}


/* ==================================================
   PAGE HEADER
================================================== */

.page-header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;
}


.page-header h1 {

    margin: 0 0 7px;

    color: #198754;

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

    white-space: nowrap;
}


/* ==================================================
   ALERTS
================================================== */

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


/* ==================================================
   STATS
================================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 15px;

    margin-bottom: 25px;
}


.stat-card {

    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);
}


.stat-title {

    color: #777;

    font-size: 13px;

    margin-bottom: 8px;
}


.stat-number {

    color: #198754;

    font-size: 28px;

    font-weight: bold;
}


.stat-number.admin {

    color: #6f42c1;
}


.stat-number.active {

    color: #198754;
}


.stat-number.inactive {

    color: #dc3545;
}


/* ==================================================
   SEARCH
================================================== */

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
        2fr 1fr 1fr auto auto;

    gap: 12px;

    align-items: end;
}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 7px;
}


label {

    font-weight: bold;

    font-size: 14px;
}


input,
select {

    width: 100%;

    padding: 11px 12px;

    border: 1px solid #ccc;

    border-radius: 7px;

    font-size: 15px;

    outline: none;
}


input:focus,
select:focus {

    border-color: #198754;
}


/* ==================================================
   BUTTONS
================================================== */

.btn {

    border: none;

    padding: 11px 17px;

    border-radius: 7px;

    cursor: pointer;

    font-weight: bold;

    text-decoration: none;

    display: inline-block;

    text-align: center;
}


.btn-success {

    background: #198754;

    color: white;
}


.btn-secondary {

    background: #6c757d;

    color: white;
}


/* ==================================================
   TABLE
================================================== */

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

    white-space: nowrap;
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


/* ==================================================
   ROLE
================================================== */

.role {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 15px;

    font-size: 12px;

    font-weight: bold;
}


.role-admin {

    background: #eadcf8;

    color: #6f42c1;
}


.role-user {

    background: #e8f8ef;

    color: #198754;
}


/* ==================================================
   STATUS
================================================== */

.status {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 15px;

    font-size: 12px;

    font-weight: bold;
}


.status-active {

    background: #d1e7dd;

    color: #0f5132;
}


.status-inactive {

    background: #f8d7da;

    color: #842029;
}


/* ==================================================
   ACTIONS
================================================== */

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

    font-size: 12px;

    font-weight: bold;

    text-decoration: none;

    white-space: nowrap;
}


.role-btn {

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


/* ==================================================
   ROLE FORM
================================================== */

.role-form {

    display: flex;

    gap: 6px;

    align-items: center;

    margin-bottom: 7px;
}


.role-form select {

    padding: 7px;

    font-size: 12px;

    width: auto;
}


/* ==================================================
   SELF
================================================== */

.self-badge {

    display: inline-block;

    margin-left: 5px;

    padding: 3px 7px;

    background: #fff3cd;

    color: #664d03;

    border-radius: 10px;

    font-size: 10px;

    font-weight: bold;
}


/* ==================================================
   EMPTY
================================================== */

.empty {

    text-align: center;

    padding: 45px;

    color: #666;
}


.empty h2 {

    margin-bottom: 8px;

    color: #555;
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 1050px) {

    .stats {

        grid-template-columns:
            repeat(3, 1fr);
    }


    .search-form {

        grid-template-columns:
            1fr 1fr;
    }

}


@media (max-width: 700px) {

    .navbar {

        padding:
            14px 18px;
    }


    .admin-area span {

        display: none;
    }


    .page-header {

        flex-direction: column;

        align-items:
            flex-start;
    }


    .stats {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .search-form {

        grid-template-columns: 1fr;
    }

}


@media (max-width: 450px) {

    .stats {

        grid-template-columns: 1fr;
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
        href="dashboard.php"
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



<!-- ==================================================
     MAIN
================================================== -->

<main class="container">


    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>
                User Management
            </h1>

            <p>
                Manage Daily Bazaar users,
                roles and account status.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="dashboard-btn"
        >
            ← Dashboard
        </a>

    </div>



    <!-- ALERT -->

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



    <!-- ==================================================
         STATISTICS
    ================================================== -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-title">
                Total Users
            </div>

            <div class="stat-number">
                <?= $totalUsers ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Administrators
            </div>

            <div class="stat-number admin">
                <?= $totalAdmins ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Normal Users
            </div>

            <div class="stat-number">
                <?= $totalNormalUsers ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Active
            </div>

            <div class="stat-number active">
                <?= $totalActive ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Inactive
            </div>

            <div class="stat-number inactive">
                <?= $totalInactive ?>
            </div>

        </div>


    </div>



    <!-- ==================================================
         SEARCH / FILTER
    ================================================== -->

    <div class="search-card">


        <form
            method="GET"
            class="search-form"
        >


            <div class="form-group">

                <label>
                    Search User
                </label>

                <input
                    type="text"
                    name="search"
                    placeholder="Search by name or email..."
                    value="<?= htmlspecialchars(
                        $search
                    ) ?>"
                >

            </div>



            <div class="form-group">

                <label>
                    Role
                </label>

                <select name="role">

                    <option value="">
                        All Roles
                    </option>

                    <option
                        value="user"
                        <?= $roleFilter === "user"
                            ? "selected"
                            : ""
                        ?>
                    >
                        User
                    </option>

                    <option
                        value="admin"
                        <?= $roleFilter === "admin"
                            ? "selected"
                            : ""
                        ?>
                    >
                        Admin
                    </option>

                </select>

            </div>



            <div class="form-group">

                <label>
                    Status
                </label>

                <select name="status">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="active"
                        <?= $statusFilter === "active"
                            ? "selected"
                            : ""
                        ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?= $statusFilter === "inactive"
                            ? "selected"
                            : ""
                        ?>
                    >
                        Inactive
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
                href="users.php"
                class="btn btn-secondary"
            >
                Reset
            </a>


        </form>

    </div>



    <!-- ==================================================
         USER TABLE
    ================================================== -->

    <div class="table-card">


        <div class="table-wrapper">


            <?php if (count($users) > 0): ?>


                <table>


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Registered
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $users
                        as $user
                    ): ?>


                        <?php

                        $isSelf =
                            (int)$user["id"] ===
                            (int)($_SESSION["user_id"] ?? 0);

                        ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <?= (int)$user["id"] ?>

                            </td>



                            <!-- NAME -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $user["name"]
                                    ) ?>

                                </strong>


                                <?php if ($isSelf): ?>

                                    <span
                                        class="self-badge"
                                    >
                                        YOU
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- EMAIL -->

                            <td>

                                <?= htmlspecialchars(
                                    $user["email"]
                                ) ?>

                            </td>



                            <!-- ROLE -->

                            <td>


                                <span
                                    class="role
                                    <?= $user["role"] === "admin"
                                        ? "role-admin"
                                        : "role-user"
                                    ?>"
                                >

                                    <?= ucfirst(
                                        $user["role"]
                                    ) ?>

                                </span>


                            </td>



                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status
                                    <?= $user["status"] === "active"
                                        ? "status-active"
                                        : "status-inactive"
                                    ?>"
                                >

                                    <?= ucfirst(
                                        $user["status"]
                                    ) ?>

                                </span>

                            </td>



                            <!-- DATE -->

                            <td>

                                <?= !empty(
                                    $user["created_at"]
                                )
                                    ? date(
                                        "d M Y",
                                        strtotime(
                                            $user["created_at"]
                                        )
                                    )
                                    : "-"
                                ?>

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div
                                    class="actions-cell"
                                >


                                    <?php if (!$isSelf): ?>


                                        <!-- CHANGE ROLE -->

                                        <form
                                            method="POST"
                                            class="role-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user["id"] ?>"
                                            >


                                            <select
                                                name="role"
                                            >

                                                <option
                                                    value="user"
                                                    <?= $user["role"] === "user"
                                                        ? "selected"
                                                        : ""
                                                    ?>
                                                >
                                                    User
                                                </option>


                                                <option
                                                    value="admin"
                                                    <?= $user["role"] === "admin"
                                                        ? "selected"
                                                        : ""
                                                    ?>
                                                >
                                                    Admin
                                                </option>

                                            </select>


                                            <button
                                                type="submit"
                                                name="change_role"
                                                class="action-btn role-btn"
                                            >
                                                Role
                                            </button>

                                        </form>



                                        <!-- TOGGLE STATUS -->

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to change this user status?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user["id"] ?>"
                                            >


                                            <button
                                                type="submit"
                                                name="toggle_status"
                                                class="action-btn toggle-btn"
                                            >

                                                <?= $user["status"] === "active"
                                                    ? "🔴 Deactivate"
                                                    : "🟢 Activate"
                                                ?>

                                            </button>

                                        </form>



                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('WARNING: This will permanently delete this user. Continue?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user["id"] ?>"
                                            >


                                            <button
                                                type="submit"
                                                name="delete_user"
                                                class="action-btn delete-btn"
                                            >
                                                🗑️ Delete
                                            </button>

                                        </form>


                                    <?php else: ?>


                                        <span
                                            style="
                                                color:#777;
                                                font-size:12px;
                                                font-weight:bold;
                                            "
                                        >
                                            🔒 Your Account
                                        </span>


                                    <?php endif; ?>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="empty">

                    <h2>
                        No Users Found
                    </h2>

                    <p>
                        No users match your
                        current search or filters.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>


</main>


</body>

</html>


<?php

$conn->close();

?>