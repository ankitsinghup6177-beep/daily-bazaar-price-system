<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

$name = getenv("ADMIN_NAME") ?: "";
$email = getenv("ADMIN_EMAIL") ?: "";
$password = getenv("ADMIN_PASSWORD") ?: "";

if (
    $name === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === ""
) {
    fwrite(
        STDERR,
        "Set ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD before running this script.\n"
    );
    exit(1);
}

require_once __DIR__ . "/../config/db.php";

$role = "admin";
$status = "active";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO users
    (name, email, password, role, status)
    VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    fwrite(STDERR, "Unable to prepare admin creation: " . $conn->error . "\n");
    exit(1);
}

$stmt->bind_param(
    "sssss",
    $name,
    $email,
    $hashedPassword,
    $role,
    $status
);

if (!$stmt->execute()) {
    fwrite(STDERR, "Unable to create admin: " . $stmt->error . "\n");
    $stmt->close();
    exit(1);
}

echo "Admin account created successfully.\n";
$stmt->close();
$conn->close();
