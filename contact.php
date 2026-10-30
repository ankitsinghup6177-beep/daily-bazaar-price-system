<?php
session_start();
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

/* PAGE VARIABLES */

$successMessage = "";
$errorMessage = "";
$name = "";
$email = "";
$subject = "";
$message = "";

/* LOGGED-IN USER */
$isLoggedIn = (
    isset($_SESSION["logged_in"]) &&
    $_SESSION["logged_in"] === true
);
$userName = $_SESSION["user_name"] ?? "";

/*  CONTACT FORM */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");


    /*
    |--------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------
    */

    if ($name === "") {

        $errorMessage = "Please enter your name.";

    } elseif (strlen($name) < 2) {

        $errorMessage = "Name must contain at least 2 characters.";

    } elseif ($email === "") {

        $errorMessage = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage = "Please enter a valid email address.";

    } elseif ($subject === "") {

        $errorMessage = "Please select a subject.";

    } elseif ($message === "") {

        $errorMessage = "Please enter your message.";

    } elseif (strlen($message) < 10) {

        $errorMessage = "Message must contain at least 10 characters.";

    } else {

        /*
        |----------------------------------------------------------
        | SUCCESS
        |
        | This version displays a success message.
        | Database/email integration can be added later.
        |----------------------------------------------------------
        */

        $successMessage =
            "Thank you, " .
            htmlspecialchars($name) .
            "! Your message has been received. We will get back to you soon.";

        /*
        | Clear form
        */

        $name = "";
        $email = "";
        $subject = "";
        $message = "";
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

    <meta
        name="description"
        content="Contact Daily Bazaar for product, price and website related queries."
    >

    <meta
        name="theme-color"
        content="#198754"
    >

    <title>
        Contact Us - Daily Bazaar
    </title>


    <style>

        /*
        ============================================================
        RESET
        ============================================================
        */

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;
        }


        /*
        ============================================================
        BODY
        ============================================================
        */

        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #f4f8f6,
                    #eef8f2
                );

            color: #222;

            min-height: 100vh;

            line-height: 1.6;
        }


        /*
        ============================================================
        NAVBAR
        ============================================================
        */

        .navbar {

            width: 100%;

            background: #198754;

            color: white;

            padding: 15px 5%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            position: sticky;

            top: 0;

            z-index: 1000;

            box-shadow:
                0 3px 15px
                rgba(0, 0, 0, 0.12);
        }


        .brand {

            color: white;

            text-decoration: none;

            font-size: 25px;

            font-weight: 800;

            letter-spacing: 0.3px;

            white-space: nowrap;
        }


        .nav-links {

            display: flex;

            align-items: center;

            gap: 8px;

            list-style: none;
        }


        .nav-links a {

            color: white;

            text-decoration: none;

            padding: 9px 13px;

            border-radius: 7px;

            font-size: 15px;

            font-weight: 600;

            transition:
                background 0.2s ease,
                color 0.2s ease;
        }


        .nav-links a:hover {

            background:
                rgba(255, 255, 255, 0.15);
        }


        .nav-links a.active {

            background: white;

            color: #198754;
        }


        .user-area {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .user-name {

            color: white;

            font-size: 14px;

            font-weight: 600;
        }


        .login-btn {

            background: white !important;

            color: #198754 !important;

            padding:
                8px 14px !important;
        }


        /*
        ============================================================
        MOBILE MENU BUTTON
        ============================================================
        */

        .menu-btn {

            display: none;

            border: none;

            background: transparent;

            color: white;

            font-size: 28px;

            cursor: pointer;
        }


        /*
        ============================================================
        HERO
        ============================================================
        */

        .hero {

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #157347
                );

            color: white;

            padding:
                65px 20px;

            text-align: center;
        }


        .hero-icon {

            width: 75px;

            height: 75px;

            margin:
                0 auto 18px;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                rgba(255, 255, 255, 0.16);

            border-radius: 50%;

            font-size: 38px;
        }


        .hero h1 {

            font-size: 42px;

            margin-bottom: 10px;
        }


        .hero p {

            max-width: 700px;

            margin: 0 auto;

            color:
                rgba(255,255,255,0.9);

            font-size: 17px;
        }


        /*
        ============================================================
        MAIN CONTAINER
        ============================================================
        */

        .container {

            max-width: 1150px;

            margin:
                45px auto;

            padding:
                0 20px;
        }


        /*
        ============================================================
        ALERTS
        ============================================================
        */

        .alert {

            padding:
                14px 18px;

            border-radius: 9px;

            margin-bottom: 25px;

            font-weight: 600;

            border-left:
                5px solid;
        }


        .alert-success {

            background: #d1e7dd;

            color: #0f5132;

            border-color: #198754;
        }


        .alert-error {

            background: #f8d7da;

            color: #842029;

            border-color: #dc3545;
        }


        /*
        ============================================================
        CONTACT GRID
        ============================================================
        */

        .contact-grid {

            display: grid;

            grid-template-columns:
                0.9fr 1.4fr;

            gap: 28px;

            align-items: start;
        }


        /*
        ============================================================
        CONTACT INFO
        ============================================================
        */

        .info-card {

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 7px 25px
                rgba(0, 0, 0, 0.08);
        }


        .info-card h2 {

            color: #198754;

            font-size: 25px;

            margin-bottom: 10px;
        }


        .info-card > p {

            color: #666;

            margin-bottom: 25px;

            font-size: 14px;
        }


        .info-item {

            display: flex;

            align-items: flex-start;

            gap: 15px;

            padding:
                17px 0;

            border-bottom:
                1px solid #eeeeee;
        }


        .info-item:last-child {

            border-bottom: none;
        }


        .info-icon {

            min-width: 45px;

            height: 45px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #e8f8ef;

            color: #198754;

            border-radius: 10px;

            font-size: 21px;
        }


        .info-content h3 {

            font-size: 15px;

            margin-bottom: 3px;

            color: #333;
        }


        .info-content p {

            color: #666;

            font-size: 14px;
        }


        .info-content a {

            color: #198754;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;
        }


        .info-content a:hover {

            text-decoration: underline;
        }


        /*
        ============================================================
        FORM CARD
        ============================================================
        */

        .form-card {

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 7px 25px
                rgba(0, 0, 0, 0.08);
        }


        .form-card h2 {

            color: #198754;

            font-size: 25px;

            margin-bottom: 7px;
        }


        .form-card > p {

            color: #666;

            font-size: 14px;

            margin-bottom: 25px;
        }


        /*
        ============================================================
        FORM GRID
        ============================================================
        */

        .form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;
        }


        .form-group {

            display: flex;

            flex-direction: column;

            gap: 7px;
        }


        .form-group.full {

            grid-column:
                1 / -1;
        }


        label {

            color: #333;

            font-size: 14px;

            font-weight: 700;
        }


        .required {

            color: #dc3545;
        }


        input,
        select,
        textarea {

            width: 100%;

            border:
                1px solid #d3d3d3;

            border-radius: 8px;

            padding:
                12px 13px;

            font-family: inherit;

            font-size: 15px;

            color: #222;

            background: #fff;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #198754;

            box-shadow:
                0 0 0 3px
                rgba(25, 135, 84, 0.12);
        }


        textarea {

            min-height: 145px;

            resize: vertical;
        }


        .char-note {

            color: #888;

            font-size: 12px;

            margin-top: 2px;
        }


        /*
        ============================================================
        SUBMIT BUTTON
        ============================================================
        */

        .submit-area {

            margin-top: 22px;
        }


        .submit-btn {

            width: 100%;

            border: none;

            background: #198754;

            color: white;

            padding:
                13px 20px;

            border-radius: 8px;

            font-size: 16px;

            font-weight: 700;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .submit-btn:hover {

            background: #157347;

            transform:
                translateY(-1px);

            box-shadow:
                0 5px 15px
                rgba(25, 135, 84, 0.25);
        }


        .submit-btn:active {

            transform:
                translateY(0);
        }


        /*
        ============================================================
        FAQ / HELP SECTION
        ============================================================
        */

        .help-section {

            margin-top: 35px;

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 7px 25px
                rgba(0, 0, 0, 0.07);
        }


        .help-section h2 {

            color: #198754;

            text-align: center;

            margin-bottom: 25px;
        }


        .help-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        .help-card {

            background: #f8fbf9;

            border:
                1px solid #e4eee8;

            border-radius: 10px;

            padding: 22px;

            text-align: center;
        }


        .help-card .icon {

            font-size: 30px;

            margin-bottom: 8px;
        }


        .help-card h3 {

            color: #198754;

            font-size: 17px;

            margin-bottom: 6px;
        }


        .help-card p {

            color: #666;

            font-size: 13px;

            line-height: 1.5;
        }


        /*
        ============================================================
        FOOTER
        ============================================================
        */

        .footer {

            margin-top: 55px;

            background: #145c3a;

            color: white;

            padding:
                35px 20px;

            text-align: center;
        }


        .footer-brand {

            font-size: 22px;

            font-weight: 800;

            margin-bottom: 7px;
        }


        .footer p {

            color:
                rgba(255,255,255,0.78);

            font-size: 13px;

            margin-bottom: 15px;
        }


        .footer-links {

            display: flex;

            justify-content: center;

            flex-wrap: wrap;

            gap: 15px;
        }


        .footer-links a {

            color: white;

            text-decoration: none;

            font-size: 13px;

            opacity: 0.85;
        }


        .footer-links a:hover {

            opacity: 1;

            text-decoration: underline;
        }


        .copyright {

            margin-top: 20px;

            padding-top: 15px;

            border-top:
                1px solid
                rgba(255,255,255,0.15);

            font-size: 12px;

            color:
                rgba(255,255,255,0.65);
        }


        /*
        ============================================================
        RESPONSIVE - TABLET
        ============================================================
        */

        @media (max-width: 900px) {

            .nav-links {

                gap: 2px;
            }


            .nav-links a {

                padding:
                    8px 9px;

                font-size: 14px;
            }


            .contact-grid {

                grid-template-columns: 1fr;
            }


            .help-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        /*
        ============================================================
        RESPONSIVE - MOBILE
        ============================================================
        */

        @media (max-width: 700px) {

            .navbar {

                padding:
                    14px 18px;
            }


            .menu-btn {

                display: block;
            }


            .nav-links {

                display: none;

                position: absolute;

                top: 63px;

                left: 0;

                right: 0;

                background: #198754;

                padding: 12px 18px;

                flex-direction: column;

                align-items: stretch;

                box-shadow:
                    0 8px 15px
                    rgba(0,0,0,0.12);
            }


            .nav-links.show {

                display: flex;
            }


            .nav-links a {

                display: block;

                padding: 11px 12px;
            }


            .user-area {

                display: none;
            }


            .hero {

                padding:
                    45px 20px;
            }


            .hero h1 {

                font-size: 32px;
            }


            .hero p {

                font-size: 15px;
            }


            .container {

                margin:
                    30px auto;
            }


            .form-grid {

                grid-template-columns: 1fr;
            }


            .form-group.full {

                grid-column: auto;
            }


            .help-grid {

                grid-template-columns: 1fr;
            }


            .info-card,
            .form-card,
            .help-section {

                padding: 22px;
            }

        }


        /*
        ============================================================
        SMALL MOBILE
        ============================================================
        */

        @media (max-width: 450px) {

            .brand {

                font-size: 21px;
            }


            .hero h1 {

                font-size: 28px;
            }


            .hero-icon {

                width: 65px;

                height: 65px;

                font-size: 32px;
            }


            .info-item {

                gap: 10px;
            }


            .info-icon {

                min-width: 40px;

                height: 40px;

                font-size: 18px;
            }

        }

    </style>

</head>


<body>


<!-- ============================================================
     NAVBAR
============================================================ -->

<nav class="navbar">


    <a
        href="index.php"
        class="brand"
    >
        Daily Bazaar
    </a>


    <button
        type="button"
        class="menu-btn"
        id="menuBtn"
        aria-label="Open navigation menu"
    >
        ☰
    </button>


    <ul
        class="nav-links"
        id="navLinks"
    >

        <li>
            <a href="index.php">
                Home
            </a>
        </li>

        <li>
            <a href="about.php">
                About
            </a>
        </li>

        <li>
            <a
                href="contact.php"
                class="active"
            >
                Contact
            </a>
        </li>

        <li>
            <a href="products/index.php">
                Products
            </a>
        </li>

    </ul>


    <div class="user-area">

        <?php if ($isLoggedIn): ?>

            <span class="user-name">

                👤
                <?= htmlspecialchars($userName) ?>

            </span>

        <?php else: ?>

            <a
                href="auth/login.php"
                class="nav-links login-btn"
            >
                Login
            </a>

        <?php endif; ?>

    </div>


</nav>



<!-- ============================================================
     HERO
============================================================ -->

<section class="hero">


    <div class="hero-icon">
        💬
    </div>


    <h1>
        Contact Us
    </h1>


    <p>
        Have a question, suggestion or need help?
        We're here to help you.
    </p>


</section>



<!-- ============================================================
     MAIN
============================================================ -->

<main class="container">


    <!-- ALERT SUCCESS -->

    <?php if ($successMessage !== ""): ?>

        <div class="alert alert-success">

            ✅
            <?= $successMessage ?>

        </div>

    <?php endif; ?>


    <!-- ALERT ERROR -->

    <?php if ($errorMessage !== ""): ?>

        <div class="alert alert-error">

            ❌
            <?= htmlspecialchars($errorMessage) ?>

        </div>

    <?php endif; ?>



    <!-- ========================================================
         CONTACT GRID
    ======================================================== -->

    <div class="contact-grid">


        <!-- ====================================================
             CONTACT INFORMATION
        ==================================================== -->

        <section class="info-card">


            <h2>
                Get In Touch
            </h2>


            <p>
                You can contact the Daily Bazaar
                team using any of the options below.
            </p>



            <!-- EMAIL -->

            <div class="info-item">

                <div class="info-icon">
                    ✉️
                </div>


                <div class="info-content">

                    <h3>
                        Email
                    </h3>

                    <a
                        href="mailto:support@dailybazaar.com"
                    >
                        support@dailybazaar.com
                    </a>

                </div>

            </div>



            <!-- PHONE -->

            <div class="info-item">

                <div class="info-icon">
                    📞
                </div>


                <div class="info-content">

                    <h3>
                        Phone
                    </h3>

                    <a
                        href="tel:+919876543210"
                    >
                        +91 98765 43210
                    </a>

                </div>

            </div>



            <!-- ADDRESS -->

            <div class="info-item">

                <div class="info-icon">
                    📍
                </div>


                <div class="info-content">

                    <h3>
                        Address
                    </h3>

                    <p>
                        Daily Bazaar Market,
                        New Delhi, India
                    </p>

                </div>

            </div>



            <!-- WORKING HOURS -->

            <div class="info-item">

                <div class="info-icon">
                    🕒
                </div>


                <div class="info-content">

                    <h3>
                        Working Hours
                    </h3>

                    <p>
                        Monday - Saturday
                        <br>
                        9:00 AM - 6:00 PM
                    </p>

                </div>

            </div>



        </section>



        <!-- ====================================================
             CONTACT FORM
        ==================================================== -->

        <section class="form-card">


            <h2>
                Send Us a Message
            </h2>


            <p>
                Fill in the form below and our team
                will get back to you as soon as possible.
            </p>


            <form
                method="POST"
                action="contact.php"
                id="contactForm"
            >


                <div class="form-grid">


                    <!-- NAME -->

                    <div class="form-group">

                        <label for="name">

                            Full Name

                            <span class="required">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            id="name"
                            name="name"
                            maxlength="100"
                            placeholder="Enter your name"
                            value="<?= htmlspecialchars($name) ?>"
                            required
                        >

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">

                            Email Address

                            <span class="required">
                                *
                            </span>

                        </label>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            maxlength="150"
                            placeholder="example@email.com"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                        >

                    </div>



                    <!-- SUBJECT -->

                    <div class="form-group full">

                        <label for="subject">

                            Subject

                            <span class="required">
                                *
                            </span>

                        </label>


                        <select
                            id="subject"
                            name="subject"
                            required
                        >

                            <option value="">
                                Select a subject
                            </option>

                            <option
                                value="Product Question"
                                <?= $subject === "Product Question"
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Product Question
                            </option>

                            <option
                                value="Price Related"
                                <?= $subject === "Price Related"
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Price Related
                            </option>

                            <option
                                value="Website Issue"
                                <?= $subject === "Website Issue"
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Website Issue
                            </option>

                            <option
                                value="Suggestion"
                                <?= $subject === "Suggestion"
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Suggestion
                            </option>

                            <option
                                value="Other"
                                <?= $subject === "Other"
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>



                    <!-- MESSAGE -->

                    <div class="form-group full">

                        <label for="message">

                            Message

                            <span class="required">
                                *
                            </span>

                        </label>


                        <textarea
                            id="message"
                            name="message"
                            maxlength="1000"
                            placeholder="Write your message here..."
                            required
                        ><?= htmlspecialchars($message) ?></textarea>


                        <span class="char-note">
                            Maximum 1000 characters.
                        </span>

                    </div>


                </div>



                <!-- SUBMIT -->

                <div class="submit-area">

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        📩 Send Message
                    </button>

                </div>


            </form>


        </section>


    </div>



    <!-- ========================================================
         HELP SECTION
    ======================================================== -->

    <section class="help-section">


        <h2>
            How Can We Help?
        </h2>


        <div class="help-grid">


            <!-- PRODUCT -->

            <div class="help-card">

                <div class="icon">
                    🛒
                </div>

                <h3>
                    Products
                </h3>

                <p>
                    Questions about vegetables,
                    fruits or grocery products?
                    Contact us for assistance.
                </p>

            </div>



            <!-- PRICE -->

            <div class="help-card">

                <div class="icon">
                    💰
                </div>

                <h3>
                    Market Prices
                </h3>

                <p>
                    Found an incorrect price?
                    Let us know so we can check
                    and update it.
                </p>

            </div>



            <!-- SUPPORT -->

            <div class="help-card">

                <div class="icon">
                    🛠️
                </div>

                <h3>
                    Technical Support
                </h3>

                <p>
                    Facing a website problem?
                    Send us the details and
                    we'll help you.
                </p>

            </div>


        </div>


    </section>


</main>



<!-- ============================================================
     FOOTER
============================================================ -->

<footer class="footer">


    <div class="footer-brand">
        Daily Bazaar
    </div>


    <p>
        Fresh market information at your fingertips.
    </p>


    <div class="footer-links">

        <a href="index.php">
            Home
        </a>

        <a href="about.php">
            About
        </a>

        <a href="contact.php">
            Contact
        </a>

        <a href="products/index.php">
            Products
        </a>

        <a href="auth/login.php">
            Login
        </a>

    </div>


    <div class="copyright">

        © <?= date("Y") ?>
        Daily Bazaar.
        All Rights Reserved.

    </div>


</footer>



<!-- ============================================================
     JAVASCRIPT
============================================================ -->

<script>

    /*
    ==============================================================
    MOBILE MENU
    ==============================================================
    */

    const menuBtn =
        document.getElementById("menuBtn");

    const navLinks =
        document.getElementById("navLinks");


    if (menuBtn && navLinks) {

        menuBtn.addEventListener(
            "click",
            function () {

                navLinks.classList.toggle("show");

            }
        );


        /*
        ----------------------------------------------------------
        CLOSE MENU AFTER CLICK
        ----------------------------------------------------------
        */

        const links =
            navLinks.querySelectorAll("a");


        links.forEach(
            function (link) {

                link.addEventListener(
                    "click",
                    function () {

                        navLinks.classList.remove(
                            "show"
                        );

                    }
                );

            }
        );

    }



    /*
    ==============================================================
    FORM CLIENT-SIDE VALIDATION
    ==============================================================
    */

    const contactForm =
        document.getElementById("contactForm");


    if (contactForm) {

        contactForm.addEventListener(
            "submit",
            function (event) {

                const name =
                    document
                        .getElementById("name")
                        .value
                        .trim();

                const email =
                    document
                        .getElementById("email")
                        .value
                        .trim();

                const subject =
                    document
                        .getElementById("subject")
                        .value
                        .trim();

                const message =
                    document
                        .getElementById("message")
                        .value
                        .trim();


                if (name.length < 2) {

                    alert(
                        "Please enter a valid name."
                    );

                    event.preventDefault();

                    return;
                }


                if (!email.includes("@")) {

                    alert(
                        "Please enter a valid email address."
                    );

                    event.preventDefault();

                    return;
                }


                if (subject === "") {

                    alert(
                        "Please select a subject."
                    );

                    event.preventDefault();

                    return;
                }


                if (message.length < 10) {

                    alert(
                        "Message must contain at least 10 characters."
                    );

                    event.preventDefault();

                    return;
                }

            }
        );

    }

</script>


</body>

</html>