
<?php

/* =========================================================
   ERROR REPORTING - DEVELOPMENT ONLY
========================================================= */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);


/* =========================================================
   SESSION
========================================================= */

session_start();


/* =========================================================
   DATABASE
========================================================= */

require_once "../config/db.php";


/* =========================================================
   VARIABLES
========================================================= */

$name = "";
$email = "";

$errors = [];

$success = "";


/* =========================================================
   FORM SUBMISSION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");

    $email = trim($_POST["email"] ?? "");

    $password = $_POST["password"] ?? "";

    $confirmPassword = $_POST["confirm_password"] ?? "";


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($name === "") {

        $errors[] = "Please enter your full name.";

    } elseif (strlen($name) < 3) {

        $errors[] = "Name must contain at least 3 characters.";

    } elseif (strlen($name) > 100) {

        $errors[] = "Name cannot exceed 100 characters.";
    }


    if ($email === "") {

        $errors[] = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";
    }


    if ($password === "") {

        $errors[] = "Please enter a password.";

    } elseif (strlen($password) < 6) {

        $errors[] = "Password must contain at least 6 characters.";
    }


    if ($confirmPassword === "") {

        $errors[] = "Please confirm your password.";

    } elseif ($password !== $confirmPassword) {

        $errors[] = "Passwords do not match.";
    }


    /* =====================================================
       CHECK EMAIL IF BASIC VALIDATION PASSED
    ===================================================== */

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );


        if (!$stmt) {

            $errors[] = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows > 0) {

                $errors[] =
                    "An account with this email already exists.";

            }


            $stmt->close();
        }
    }


    /* =====================================================
       CREATE ACCOUNT
    ===================================================== */

    if (empty($errors)) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        /*
         * New registered users are normal users.
         * Admin accounts should be created/assigned
         * separately from the database/admin system.
         */

        $role = "user";


        /*
         * If your users table has a status column,
         * this query will use it.
         */

        $stmt = $conn->prepare(
            "INSERT INTO users
            (name, email, password, role)
            VALUES (?, ?, ?, ?)"
        );


        if (!$stmt) {

            $errors[] =
                "Unable to create account: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashedPassword,
                $role
            );


            if ($stmt->execute()) {

                $success =
                    "Account created successfully! " .
                    "You can now login.";

                /*
                 * Clear form values after successful registration.
                 */

                $name = "";
                $email = "";

            } else {

                $errors[] =
                    "Registration failed: " .
                    $stmt->error;
            }


            $stmt->close();
        }
    }
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
        Create Account - Daily Bazaar
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;

            min-height: 100%;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #0f5132 0%,
                    #198754 45%,
                    #20c997 100%
                );

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;

            position: relative;

            overflow-x: hidden;
        }


        /* =================================================
           BACKGROUND DECORATION
        ================================================= */

        body::before {

            content: "";

            position: fixed;

            width: 420px;

            height: 420px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, 0.08);

            top: -180px;

            left: -120px;
        }


        body::after {

            content: "";

            position: fixed;

            width: 500px;

            height: 500px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, 0.07);

            bottom: -250px;

            right: -150px;
        }


        /* =================================================
           REGISTER CARD
        ================================================= */

        .register-card {

            width: 100%;

            max-width: 480px;

            background: rgba(
                255,
                255,
                255,
                0.97
            );

            border-radius: 22px;

            padding: 38px;

            box-shadow:
                0 25px 70px
                rgba(0, 0, 0, 0.25);

            position: relative;

            z-index: 2;

            animation:
                cardEnter
                0.5s
                ease;
        }


        @keyframes cardEnter {

            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        /* =================================================
           LOGO
        ================================================= */

        .logo {

            width: 68px;

            height: 68px;

            margin: 0 auto 15px;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #20c997
                );

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-size: 32px;

            box-shadow:
                0 10px 25px
                rgba(25, 135, 84, 0.3);
        }


        .brand {

            text-align: center;

            color: #198754;

            font-size: 28px;

            font-weight: 800;

            margin-bottom: 6px;
        }


        .subtitle {

            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 28px;
        }


        /* =================================================
           ALERTS
        ================================================= */

        .alert {

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            line-height: 1.5;
        }


        .error-alert {

            background: #f8d7da;

            color: #842029;

            border:
                1px solid #f1aeb5;
        }


        .success-alert {

            background: #d1e7dd;

            color: #0f5132;

            border:
                1px solid #a3cfbb;
        }


        .error-alert ul {

            margin: 0;

            padding-left: 20px;
        }


        /* =================================================
           FORM
        ================================================= */

        .form-group {

            margin-bottom: 18px;
        }


        label {

            display: block;

            font-size: 14px;

            font-weight: 700;

            color: #333;

            margin-bottom: 7px;
        }


        .input-wrapper {

            position: relative;
        }


        .input-icon {

            position: absolute;

            left: 14px;

            top: 50%;

            transform:
                translateY(-50%);

            font-size: 18px;

            color: #198754;

            pointer-events: none;
        }


        input {

            width: 100%;

            padding:
                13px
                14px
                13px
                45px;

            border:
                1px solid #d5d5d5;

            border-radius: 10px;

            font-size: 15px;

            outline: none;

            background: #fff;

            transition:
                0.2s;
        }


        input:focus {

            border-color: #198754;

            box-shadow:
                0 0 0 4px
                rgba(25, 135, 84, 0.10);
        }


        input::placeholder {

            color: #aaa;
        }


        /* =================================================
           PASSWORD TOGGLE
        ================================================= */

        .password-input {

            padding-right: 48px;
        }


        .toggle-password {

            position: absolute;

            right: 12px;

            top: 50%;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            cursor: pointer;

            color: #777;

            font-size: 17px;
        }


        .toggle-password:hover {

            color: #198754;
        }


        /* =================================================
           PASSWORD HINT
        ================================================= */

        .password-hint {

            margin-top: 6px;

            font-size: 12px;

            color: #777;
        }


        /* =================================================
           REGISTER BUTTON
        ================================================= */

        .register-btn {

            width: 100%;

            border: none;

            border-radius: 10px;

            padding: 14px;

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #20c997
                );

            color: white;

            font-size: 16px;

            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 8px 20px
                rgba(25, 135, 84, 0.25);

            transition:
                0.2s;
        }


        .register-btn:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 25px
                rgba(25, 135, 84, 0.32);
        }


        .register-btn:active {

            transform:
                translateY(0);
        }


        /* =================================================
           LOGIN LINK
        ================================================= */

        .login-text {

            text-align: center;

            margin-top: 22px;

            color: #666;

            font-size: 14px;
        }


        .login-text a {

            color: #198754;

            font-weight: 700;

            text-decoration: none;
        }


        .login-text a:hover {

            text-decoration: underline;
        }


        /* =================================================
           HOME LINK
        ================================================= */

        .home-link {

            display: block;

            text-align: center;

            margin-top: 18px;

            color: #777;

            text-decoration: none;

            font-size: 13px;
        }


        .home-link:hover {

            color: #198754;
        }


        /* =================================================
           FOOTER
        ================================================= */

        .footer-text {

            text-align: center;

            margin-top: 25px;

            font-size: 11px;

            color: #999;
        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 520px) {

            body {
                padding: 18px 12px;
            }


            .register-card {

                padding: 28px 22px;

                border-radius: 18px;
            }


            .brand {

                font-size: 25px;
            }


            .logo {

                width: 60px;

                height: 60px;

                font-size: 28px;
            }
        }

    </style>

</head>


<body>


    <div class="register-card">


        <!-- LOGO -->

        <div class="logo">
            🛒
        </div>


        <div class="brand">
            Daily Bazaar
        </div>


        <div class="subtitle">
            Create your account and start exploring
            today's bazaar prices.
        </div>


        <!-- =================================================
             ERRORS
        ================================================= -->

        <?php if (!empty($errors)): ?>

            <div class="alert error-alert">

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <!-- =================================================
             SUCCESS
        ================================================= -->

        <?php if ($success !== ""): ?>

            <div class="alert success-alert">

                <?= htmlspecialchars($success) ?>

                <br><br>

                <a
                    href="login.php"
                    style="
                        color:#0f5132;
                        font-weight:bold;
                    "
                >
                    Login to your account →
                </a>

            </div>

        <?php endif; ?>


        <!-- =================================================
             REGISTRATION FORM
        ================================================= -->

        <?php if ($success === ""): ?>

            <form
                method="POST"
                action=""
                autocomplete="off"
            >


                <!-- NAME -->

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            maxlength="100"
                            value="<?= htmlspecialchars($name) ?>"
                            required
                            autocomplete="name"
                        >

                    </div>

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            ✉️
                        </span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            maxlength="150"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                            autocomplete="email"
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="password-input"
                            placeholder="Create a password"
                            minlength="6"
                            required
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword(
                                'password',
                                this
                            )"
                            aria-label="Show password"
                        >
                            👁️
                        </button>

                    </div>


                    <div class="password-hint">

                        Minimum 6 characters

                    </div>

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔐
                        </span>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="password-input"
                            placeholder="Confirm your password"
                            minlength="6"
                            required
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword(
                                'confirm_password',
                                this
                            )"
                            aria-label="Show password"
                        >
                            👁️
                        </button>

                    </div>

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="register-btn"
                >
                    Create Account
                </button>


            </form>

        <?php endif; ?>


        <!-- LOGIN -->

        <div class="login-text">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>


        <!-- HOME -->

        <a
            href="../index.php"
            class="home-link"
        >
            ← Back to Daily Bazaar

        </a>


        <div class="footer-text">

            © <?= date("Y") ?>
            Daily Bazaar Price Update System

        </div>


    </div>


    <!-- =================================================
         JAVASCRIPT
    ================================================= -->

    <script>

        function togglePassword(
            inputId,
            button
        ) {

            const input =
                document.getElementById(inputId);


            if (
                input.type === "password"
            ) {

                input.type = "text";

                button.textContent = "🙈";

            } else {

                input.type = "password";

                button.textContent = "👁️";
            }
        }

    </script>


</body>

</html>
