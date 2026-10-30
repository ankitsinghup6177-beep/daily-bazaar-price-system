<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

/* Get Login Form Data */
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

/* Basic Validation */
if ($email === "" || $password === "") {
    header(
        "Location: login.php?error=" .
        urlencode("Please fill all fields.")
    );
    exit;
}

/* Email Validation */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header(
        "Location: login.php?error=" .
        urlencode("Invalid email address.")
    );
    exit;
}
/* Find User */
$stmt = $conn->prepare(
    "SELECT id, name, email, password, role
     FROM users
     WHERE email = ?
     LIMIT 1"
);


if (!$stmt) {
    header(
        "Location: login.php?error=" .
        urlencode("Database error. Please try again.")
    );
    exit;
}
$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    $stmt->close();
    header(
        "Location: login.php?error=" .
        urlencode("Unable to process login.")
    );
    exit;
}
$result = $stmt->get_result();

/* User Found? */
if ($result->num_rows !== 1) {
    $stmt->close();
    header(
        "Location: login.php?error=" .
        urlencode("Invalid email or password.")
    );
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();


/* Verify Password */
if (!password_verify($password, $user["password"])) {
    header(
        "Location: login.php?error=" .
        urlencode("Invalid email or password.")
    );
    exit;
}
session_regenerate_id(true);


/* Store Session */
$_SESSION["logged_in"] = true;
$_SESSION["user_id"] = (int) $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_role"] = strtolower(trim($user["role"]));


/*ROLE BASED REDIRECT*/
/* ADMIN */

if ($_SESSION["user_role"] === "admin") {
    header("Location: ../admin/dashboard.php");
    exit;
}
if ($_SESSION["user_role"] === "user") {
    header("Location: ../products/index.php");
    exit;
}
session_unset();
session_destroy();
header(
    "Location: login.php?error=" .
    urlencode("Invalid account role.")
);
exit;
?>