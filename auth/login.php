<?php
session_start();
/* ALREADY LOGGED-IN USER */
if (
    isset($_SESSION["logged_in"]) &&
    $_SESSION["logged_in"] === true
) {
    if (
        isset($_SESSION["user_role"]) &&
        $_SESSION["user_role"] === "admin"
    ) {
        header("Location: ../admin/dashboard.php");
        exit;
    }
    header("Location: ../products/index.php");
    exit;
}
$error = $_GET["error"] ?? "";
$success = $_GET["success"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to Daily Bazaar Price Update System">
    <title>Login - Daily Bazaar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #e8f8ef 0%,
                    #f8fbfa 45%,
                    #dff5e8 100%
                );
            color: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            overflow-x: hidden;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background:
                rgba(25, 135, 84, 0.12);
            top: -150px;
            left: -120px;
            z-index: 0;
        }
        body::after {
            content: "";
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background:
                rgba(255, 193, 7, 0.13);
            bottom: -150px;
            right: -100px;
            z-index: 0;
        }
        .login-wrapper {
            width: 100%;
            max-width: 1050px;
            min-height: 620px;
            display: grid;
            grid-template-columns:
                1fr 1fr;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 25px 70px
                rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
        }
        .login-info {
            background:
                linear-gradient(
                    145deg,
                    #198754,
                    #146c43
                );
            color: white;
            padding: 55px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .login-info::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background:
                rgba(255, 255, 255, 0.08);
            top: -110px;
            right: -100px;
        }
        .login-info::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background:
                rgba(255, 193, 7, 0.12);
            bottom: -110px;
            left: -80px;
        }
        .brand-logo {
            width: 78px;
            height: 78px;
            border-radius: 20px;
            background:
                rgba(255,255,255,0.15);
            border:
                1px solid
                rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 26px;
            position: relative;
            z-index: 1;
            box-shadow:
                0 10px 25px
                rgba(0,0,0,0.12);
        }
        .login-info h1 {
            font-size: 43px;
            line-height: 1.15;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .login-info h1 span {
            color: #ffc107;
        }
        .subtitle {
            max-width: 430px;
            font-size: 17px;
            line-height: 1.7;
            color:
                rgba(255,255,255,0.88);
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }
        .features {
            list-style: none;
            position: relative;
            z-index: 1;
        }
        .features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 15px;
        }
        .feature-icon {
            width: 33px;
            height: 33px;
            min-width: 33px;
            border-radius: 50%;
            background:
                rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .login-form-area {
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }
        .form-heading {
            margin-bottom: 28px;
        }
        .form-heading h2 {
            font-size: 31px;
            color: #198754;
            margin-bottom: 8px;
        }
        .form-heading p {
            color: #777;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert {
            padding: 13px 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.4;
        }
        .alert-error {
            background: #f8d7da;
            border:
                1px solid
                #f1aeb5;
            color: #842029;
        }
        .alert-success {
            background: #d1e7dd;
            border:
                1px solid
                #a3cfbb;
            color: #0f5132;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform:
                translateY(-50%);
            font-size: 17px;
            color: #777;
            pointer-events: none;
        }
        .form-control {
            width: 100%;
            height: 52px;
            padding:
                0 48px;
            border:
                1px solid
                #d5d5d5;
            border-radius: 9px;
            background: #fafafa;
            color: #222;
            font-size: 15px;
            outline: none;
            transition:
                all 0.2s ease;
        }
        .form-control::placeholder {
            color: #999;
        }
        .form-control:hover {
            border-color: #aaa;
        }
        .form-control:focus {
            background: #ffffff;
            border-color: #198754;
            box-shadow:
                0 0 0 4px
                rgba(25,135,84,0.10);
        }
        .password-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform:
                translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 17px;
            color: #777;
            padding: 5px;
        }
        .password-toggle:hover {
            color: #198754;
        }
        .login-button {
            width: 100%;
            height: 53px;
            border: none;
            border-radius: 9px;
            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #157347
                );
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 4px;
            box-shadow:
                0 8px 20px
                rgba(25,135,84,0.22);
            transition:
                all 0.2s ease;
        }
        .login-button:hover {
            transform:
                translateY(-2px);
            box-shadow:
                0 12px 25px
                rgba(25,135,84,0.30);
        }
        .login-button:active {
            transform:
                translateY(0);
        }
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 25px 0;
            color: #aaa;
            font-size: 12px;
        }
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e5e5e5;
        }
        .register-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .register-text a {
            color: #198754;
            text-decoration: none;
            font-weight: bold;
        }
        .register-text a:hover {
            text-decoration: underline;
        }
        .home-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: #777;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        .home-link:hover {
            color: #198754;
        }
        .security-note {
            margin-top: 25px;
            padding-top: 18px;
            border-top:
                1px solid
                #eeeeee;
            text-align: center;
            color: #999;
            font-size: 12px;
            line-height: 1.5;
        }
        @media (max-width: 900px) {
            .login-wrapper {
                max-width: 550px;
                grid-template-columns: 1fr;
            }
            .login-info {
                padding: 38px 35px;
                min-height: 320px;
            }
            .login-info h1 {
                font-size: 35px;
            }
            .subtitle {
                margin-bottom: 22px;
            }
            .features {
                display: grid;
                grid-template-columns:
                    repeat(2, 1fr);
                gap: 8px 15px;
            }
            .features li {
                margin-bottom: 5px;
            }
            .login-form-area {
                padding: 40px 35px;
            }
        }
        @media (max-width: 550px) {
            body {
                padding: 12px;
            }
            .login-wrapper {
                border-radius: 18px;
            }
            .login-info {
                padding: 30px 25px;
            }
            .brand-logo {
                width: 65px;
                height: 65px;
                font-size: 31px;
                margin-bottom: 20px;
            }
            .login-info h1 {
                font-size: 30px;
            }
            .subtitle {
                font-size: 14px;
            }
            .features {
                grid-template-columns: 1fr;
            }
            .login-form-area {
                padding: 30px 25px;
            }
            .form-heading h2 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
<!-- MAIN LOGIN WRAPPER -->

<div class="login-wrapper">
    <section class="login-info">
        <div class="brand-logo">🛒</div>
        <h1>Daily<span>Bazaar</span></h1>
        <p class="subtitle">Your simple and smart daily market price update system.</p>
        <ul class="features">
            <li>
                <span class="feature-icon"> ✓</span>
                Check today's market prices
            </li>
            <li>
                <span class="feature-icon"> ✓</span>
                Vegetables, fruits & grocery
            </li>
            <li>
                <span class="feature-icon"> ✓</span>
                Updated prices in one place
            </li>
            <li>
                <span class="feature-icon"> ✓</span>
                Secure account access
            </li>
        </ul>
    </section>
    <section class="login-form-area">
        <div class="form-heading">
            <h2>Welcome Back!</h2>
            <p> Login to continue to your
                Daily Bazaar account.
            </p>
        </div>
        <?php if ($error !== ""): ?>
            <div class="alert alert-error">
                ⚠️
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success !== ""): ?>
            <div class="alert alert-success">
                ✓
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="authenticate.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        ✉
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        autocomplete="email"
                        maxlength="150"
                        required
                    >
                </div>
            </div>
            <div class="form-group">
                <label for="password"> Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        🔒
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="password-toggle"
                        id="passwordToggle"
                        aria-label="Show password"
                    >
                        👁
                    </button>
                </div>
            </div>
            <button
                type="submit"
                class="login-button"
            >
                Login to Account
            </button>
        </form>
        <div class="divider">
            OR
        </div>
        <div class="register-text">
            Don't have an account?
            <a href="register.php">
                Create Account
            </a>
        </div>
        <a href="../index.php" class="home-link">
            ←
            Back to Daily Bazaar Home
        </a>
        <div class="security-note">
            🔐 Your account information is
            securely processed.
        </div>
    </section>
</div>



<!-- JAVASCRIPT -->

<script>
    const passwordInput =
        document.getElementById(
            "password"
        );
    const passwordToggle =
        document.getElementById(
            "passwordToggle"
        );
    passwordToggle.addEventListener(
        "click",
        function () {
            if (
                passwordInput.type ===
                "password"
            ) {
                passwordInput.type =
                    "text";
                passwordToggle.textContent =
                    "🙈";
                passwordToggle.setAttribute(
                    "aria-label",
                    "Hide password"
                );
            } else {
                passwordInput.type =
                    "password";
                passwordToggle.textContent =
                    "👁";
                passwordToggle.setAttribute(
                    "aria-label",
                    "Show password"
                );
            }
        }
    );
</script>
</body>
</html>