<?php

/*
|--------------------------------------------------------------------------
| DAILY BAZAAR - ABOUT PAGE
|--------------------------------------------------------------------------
*/

session_start();

$loggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$userName = $_SESSION["user_name"] ?? "";

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
        content="Learn about Daily Bazaar - a simple platform for checking daily vegetable, fruit and grocery prices."
    >

    <meta
        name="keywords"
        content="Daily Bazaar, vegetable prices, fruit prices, grocery prices, daily market prices"
    >

    <meta
        name="author"
        content="Daily Bazaar"
    >

    <title>
        About Us - Daily Bazaar
    </title>


    <style>

        /* ==================================================
           RESET
        ================================================== */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                #f4f7f6;

            color:
                #222;

            line-height:
                1.6;
        }


        a {
            text-decoration: none;
        }


        /* ==================================================
           NAVBAR
        ================================================== */

        .navbar {

            background:
                #198754;

            color:
                white;

            padding:
                15px 30px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            position:
                sticky;

            top:
                0;

            z-index:
                1000;

            box-shadow:
                0 3px 12px
                rgba(0, 0, 0, 0.12);
        }


        .brand {

            color:
                white;

            font-size:
                24px;

            font-weight:
                bold;
        }


        .nav-links {

            display:
                flex;

            align-items:
                center;

            gap:
                8px;

            list-style:
                none;
        }


        .nav-links a {

            color:
                white;

            padding:
                9px 13px;

            border-radius:
                7px;

            font-size:
                14px;

            font-weight:
                600;

            transition:
                0.2s;
        }


        .nav-links a:hover {

            background:
                rgba(255,255,255,0.15);
        }


        .nav-links .active {

            background:
                rgba(255,255,255,0.18);
        }


        .login-btn {

            background:
                white !important;

            color:
                #198754 !important;

            margin-left:
                5px;
        }


        .logout-btn {

            background:
                #146c43;

            color:
                white;

            padding:
                9px 13px;

            border-radius:
                7px;

            font-size:
                14px;

            font-weight:
                600;
        }


        /* ==================================================
           MOBILE MENU
        ================================================== */

        .menu-btn {

            display:
                none;

            background:
                transparent;

            border:
                1px solid
                rgba(255,255,255,0.5);

            color:
                white;

            font-size:
                23px;

            padding:
                4px 9px;

            border-radius:
                6px;

            cursor:
                pointer;
        }


        /* ==================================================
           HERO
        ================================================== */

        .hero {

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #20a464
                );

            color:
                white;

            padding:
                80px 20px;

            text-align:
                center;
        }


        .hero-content {

            max-width:
                900px;

            margin:
                auto;
        }


        .hero-badge {

            display:
                inline-block;

            background:
                rgba(255,255,255,0.15);

            border:
                1px solid
                rgba(255,255,255,0.3);

            padding:
                7px 14px;

            border-radius:
                20px;

            font-size:
                13px;

            font-weight:
                bold;

            margin-bottom:
                15px;
        }


        .hero h1 {

            font-size:
                44px;

            margin-bottom:
                15px;
        }


        .hero p {

            max-width:
                700px;

            margin:
                auto;

            font-size:
                17px;

            color:
                rgba(255,255,255,0.92);
        }


        /* ==================================================
           CONTAINER
        ================================================== */

        .container {

            max-width:
                1150px;

            margin:
                auto;

            padding:
                0 20px;
        }


        /* ==================================================
           SECTION
        ================================================== */

        .section {

            padding:
                65px 0;
        }


        .section-title {

            text-align:
                center;

            margin-bottom:
                40px;
        }


        .section-title h2 {

            color:
                #198754;

            font-size:
                30px;

            margin-bottom:
                8px;
        }


        .section-title p {

            color:
                #666;

            max-width:
                700px;

            margin:
                auto;
        }


        /* ==================================================
           ABOUT INTRO
        ================================================== */

        .about-grid {

            display:
                grid;

            grid-template-columns:
                1.1fr 0.9fr;

            gap:
                35px;

            align-items:
                center;
        }


        .about-content {

            background:
                white;

            padding:
                35px;

            border-radius:
                14px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.07);
        }


        .about-content h2 {

            color:
                #198754;

            margin-bottom:
                15px;

            font-size:
                28px;
        }


        .about-content p {

            color:
                #555;

            margin-bottom:
                14px;
        }


        .about-content p:last-child {

            margin-bottom:
                0;
        }


        /* ==================================================
           INFO CARD
        ================================================== */

        .info-card {

            background:
                linear-gradient(
                    135deg,
                    #e8f8ef,
                    #ffffff
                );

            border:
                1px solid
                #d6efdf;

            padding:
                35px;

            border-radius:
                14px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);
        }


        .info-icon {

            font-size:
                55px;

            margin-bottom:
                15px;
        }


        .info-card h3 {

            color:
                #198754;

            margin-bottom:
                12px;

            font-size:
                24px;
        }


        .info-card p {

            color:
                #555;
        }


        /* ==================================================
           FEATURES
        ================================================== */

        .features {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                22px;
        }


        .feature-card {

            background:
                white;

            padding:
                30px 25px;

            border-radius:
                13px;

            text-align:
                center;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.07);

            transition:
                0.25s;
        }


        .feature-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 10px 28px
                rgba(0,0,0,0.11);
        }


        .feature-icon {

            width:
                65px;

            height:
                65px;

            margin:
                0 auto 17px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            background:
                #e8f8ef;

            border-radius:
                50%;

            font-size:
                30px;
        }


        .feature-card h3 {

            color:
                #198754;

            margin-bottom:
                9px;

            font-size:
                20px;
        }


        .feature-card p {

            color:
                #666;

            font-size:
                14px;
        }


        /* ==================================================
           CATEGORIES
        ================================================== */

        .categories {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                22px;
        }


        .category-card {

            padding:
                30px;

            border-radius:
                14px;

            color:
                white;

            min-height:
                180px;

            display:
                flex;

            flex-direction:
                column;

            justify-content:
                center;

            box-shadow:
                0 7px 20px
                rgba(0,0,0,0.10);

            transition:
                0.25s;
        }


        .category-card:hover {

            transform:
                translateY(-5px);
        }


        .vegetables {

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #2caf70
                );
        }


        .fruits {

            background:
                linear-gradient(
                    135deg,
                    #e85d04,
                    #f48c06
                );
        }


        .grocery {

            background:
                linear-gradient(
                    135deg,
                    #6f42c1,
                    #8e5bd4
                );
        }


        .category-card .category-icon {

            font-size:
                38px;

            margin-bottom:
                12px;
        }


        .category-card h3 {

            font-size:
                22px;

            margin-bottom:
                6px;
        }


        .category-card p {

            font-size:
                14px;

            color:
                rgba(255,255,255,0.9);
        }


        /* ==================================================
           HOW IT WORKS
        ================================================== */

        .steps {

            display:
                grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap:
                20px;
        }


        .step {

            text-align:
                center;

            position:
                relative;
        }


        .step-number {

            width:
                55px;

            height:
                55px;

            border-radius:
                50%;

            background:
                #198754;

            color:
                white;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                20px;

            font-weight:
                bold;

            margin:
                0 auto 15px;
        }


        .step h3 {

            color:
                #198754;

            margin-bottom:
                7px;
        }


        .step p {

            color:
                #666;

            font-size:
                14px;
        }


        /* ==================================================
           CTA
        ================================================== */

        .cta {

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #146c43
                );

            color:
                white;

            border-radius:
                16px;

            padding:
                50px 30px;

            text-align:
                center;

            margin-bottom:
                60px;
        }


        .cta h2 {

            font-size:
                30px;

            margin-bottom:
                10px;
        }


        .cta p {

            max-width:
                650px;

            margin:
                0 auto 25px;

            color:
                rgba(255,255,255,0.9);
        }


        .cta-buttons {

            display:
                flex;

            justify-content:
                center;

            gap:
                12px;

            flex-wrap:
                wrap;
        }


        .btn {

            display:
                inline-block;

            padding:
                11px 20px;

            border-radius:
                7px;

            font-weight:
                bold;

            transition:
                0.2s;
        }


        .btn-primary {

            background:
                white;

            color:
                #198754;
        }


        .btn-primary:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.15);
        }


        .btn-outline {

            border:
                1px solid
                rgba(255,255,255,0.7);

            color:
                white;
        }


        .btn-outline:hover {

            background:
                rgba(255,255,255,0.12);
        }


        /* ==================================================
           FOOTER
        ================================================== */

        footer {

            background:
                #1f2933;

            color:
                #d5d8dc;

            padding:
                35px 20px;

            text-align:
                center;
        }


        .footer-brand {

            color:
                white;

            font-size:
                22px;

            font-weight:
                bold;

            margin-bottom:
                8px;
        }


        footer p {

            font-size:
                14px;

            color:
                #aeb4ba;

            margin-bottom:
                5px;
        }


        .footer-links {

            margin-top:
                14px;

            display:
                flex;

            justify-content:
                center;

            gap:
                18px;

            flex-wrap:
                wrap;
        }


        .footer-links a {

            color:
                #d5d8dc;

            font-size:
                14px;
        }


        .footer-links a:hover {

            color:
                #5ee49a;
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 900px) {

            .about-grid {

                grid-template-columns:
                    1fr;
            }


            .features {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .categories {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .steps {

                grid-template-columns:
                    repeat(2, 1fr);

                row-gap:
                    35px;
            }


            .hero h1 {

                font-size:
                    36px;
            }

        }


        @media (max-width: 700px) {

            .navbar {

                padding:
                    14px 18px;
            }


            .menu-btn {

                display:
                    block;
            }


            .nav-links {

                display:
                    none;

                position:
                    absolute;

                top:
                    62px;

                left:
                    0;

                right:
                    0;

                background:
                    #198754;

                padding:
                    12px 18px;

                flex-direction:
                    column;

                align-items:
                    stretch;

                box-shadow:
                    0 5px 15px
                    rgba(0,0,0,0.12);
            }


            .nav-links.show {

                display:
                    flex;
            }


            .nav-links a {

                display:
                    block;

                text-align:
                    center;
            }


            .hero {

                padding:
                    60px 20px;
            }


            .hero h1 {

                font-size:
                    31px;
            }


            .hero p {

                font-size:
                    15px;
            }


            .features,
            .categories,
            .steps {

                grid-template-columns:
                    1fr;
            }


            .section {

                padding:
                    50px 0;
            }


            .about-content,
            .info-card {

                padding:
                    25px;
            }

        }


        @media (max-width: 450px) {

            .brand {

                font-size:
                    20px;
            }


            .hero h1 {

                font-size:
                    27px;
            }


            .section-title h2 {

                font-size:
                    25px;
            }


            .cta {

                padding:
                    40px 20px;
            }


            .cta h2 {

                font-size:
                    25px;
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
        href="index.php"
        class="brand"
    >
        🛒 Daily Bazaar
    </a>


    <button
        type="button"
        class="menu-btn"
        id="menuBtn"
        aria-label="Open navigation menu"
    >
        ☰
    </button>


    <div
        class="nav-links"
        id="navLinks"
    >

        <a href="index.php">
            Home
        </a>

        <a
            href="about.php"
            class="active"
        >
            About
        </a>

        <a href="products/index.php">
            Products
        </a>

        <a href="contact.php">
            Contact
        </a>


        <?php if ($loggedIn): ?>

            <a
                href="products/index.php"
            >
                👤
                <?= htmlspecialchars($userName) ?>
            </a>

            <a
                href="auth/logout.php"
                class="logout-btn"
            >
                Logout
            </a>

        <?php else: ?>

            <a
                href="auth/login.php"
                class="login-btn"
            >
                Login
            </a>

        <?php endif; ?>

    </div>

</nav>



<!-- ==================================================
     HERO
================================================== -->

<section class="hero">

    <div class="hero-content">

        <div class="hero-badge">
            🥕 Your Daily Market Price Guide
        </div>


        <h1>
            About Daily Bazaar
        </h1>


        <p>
            Making everyday market price information
            simple, clear and accessible for everyone.
        </p>

    </div>

</section>



<!-- ==================================================
     ABOUT
================================================== -->

<section class="section">

    <div class="container">

        <div class="about-grid">


            <div class="about-content">

                <h2>
                    What is Daily Bazaar?
                </h2>


                <p>
                    Daily Bazaar is a simple price information
                    platform designed to help users check
                    the latest prices of everyday vegetables,
                    fruits and grocery products.
                </p>


                <p>
                    Instead of searching for prices in different
                    places, users can find product information
                    in one convenient location.
                </p>


                <p>
                    Our goal is to make daily market information
                    easier to understand and easier to access.
                </p>

            </div>


            <div class="info-card">

                <div class="info-icon">
                    🏪
                </div>


                <h3>
                    Simple. Useful. Accessible.
                </h3>


                <p>
                    Daily Bazaar brings important daily
                    product and price information together
                    in a clean and easy-to-use interface.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     FEATURES
================================================== -->

<section
    class="section"
    style="background:#ffffff;"
>

    <div class="container">


        <div class="section-title">

            <h2>
                Why Daily Bazaar?
            </h2>

            <p>
                Built with simplicity and usefulness
                in mind for everyday users.
            </p>

        </div>


        <div class="features">


            <div class="feature-card">

                <div class="feature-icon">
                    💰
                </div>

                <h3>
                    Daily Prices
                </h3>

                <p>
                    Check current product prices
                    available on the platform.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🔎
                </div>

                <h3>
                    Easy to Find
                </h3>

                <p>
                    Quickly search and browse
                    vegetables, fruits and groceries.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    📱
                </div>

                <h3>
                    Mobile Friendly
                </h3>

                <p>
                    Access Daily Bazaar comfortably
                    from phones, tablets and computers.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    📊
                </div>

                <h3>
                    Price History
                </h3>

                <p>
                    Track previous price changes
                    when historical information is available.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🛒
                </div>

                <h3>
                    Multiple Categories
                </h3>

                <p>
                    Browse products across vegetables,
                    fruits and grocery categories.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🔐
                </div>

                <h3>
                    Secure Access
                </h3>

                <p>
                    Registered users and administrators
                    get appropriate access to system features.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     CATEGORIES
================================================== -->

<section class="section">

    <div class="container">


        <div class="section-title">

            <h2>
                What You Can Find
            </h2>

            <p>
                Explore different types of everyday
                market products.
            </p>

        </div>


        <div class="categories">


            <div class="category-card vegetables">

                <div class="category-icon">
                    🥦
                </div>

                <h3>
                    Vegetables
                </h3>

                <p>
                    Potatoes, onions, tomatoes,
                    carrots, cabbage and more.
                </p>

            </div>


            <div class="category-card fruits">

                <div class="category-icon">
                    🍎
                </div>

                <h3>
                    Fruits
                </h3>

                <p>
                    Apples, bananas, oranges,
                    mangoes and other fruits.
                </p>

            </div>


            <div class="category-card grocery">

                <div class="category-icon">
                    🛍️
                </div>

                <h3>
                    Grocery
                </h3>

                <p>
                    Everyday grocery products
                    for your regular needs.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     HOW IT WORKS
================================================== -->

<section
    class="section"
    style="background:#ffffff;"
>

    <div class="container">


        <div class="section-title">

            <h2>
                How Daily Bazaar Works
            </h2>

            <p>
                A simple process for finding
                daily product information.
            </p>

        </div>


        <div class="steps">


            <div class="step">

                <div class="step-number">
                    1
                </div>

                <h3>
                    Browse
                </h3>

                <p>
                    Open the products section
                    and explore available items.
                </p>

            </div>


            <div class="step">

                <div class="step-number">
                    2
                </div>

                <h3>
                    Search
                </h3>

                <p>
                    Find a particular product
                    or browse by category.
                </p>

            </div>


            <div class="step">

                <div class="step-number">
                    3
                </div>

                <h3>
                    Check Price
                </h3>

                <p>
                    View the current price
                    and unit of the product.
                </p>

            </div>


            <div class="step">

                <div class="step-number">
                    4
                </div>

                <h3>
                    Stay Updated
                </h3>

                <p>
                    Check back regularly for
                    the latest available prices.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     CALL TO ACTION
================================================== -->

<section class="section">

    <div class="container">

        <div class="cta">

            <h2>
                Ready to Check Today's Prices?
            </h2>


            <p>
                Explore Daily Bazaar and find
                the products and prices you need.
            </p>


            <div class="cta-buttons">

                <a
                    href="products/index.php"
                    class="btn btn-primary"
                >
                    🛒 View Products
                </a>


                <a
                    href="contact.php"
                    class="btn btn-outline"
                >
                    Contact Us
                </a>

            </div>

        </div>

    </div>

</section>



<!-- ==================================================
     FOOTER
================================================== -->

<footer>

    <div class="footer-brand">
        🛒 Daily Bazaar
    </div>


    <p>
        Daily market prices made simple.
    </p>


    <p>
        ©
        <span id="currentYear">
            <?= date("Y") ?>
        </span>
        Daily Bazaar. All rights reserved.
    </p>


    <div class="footer-links">

        <a href="index.php">
            Home
        </a>

        <a href="about.php">
            About
        </a>

        <a href="products/index.php">
            Products
        </a>

        <a href="contact.php">
            Contact
        </a>

        <?php if (!$loggedIn): ?>

            <a href="auth/login.php">
                Login
            </a>

        <?php endif; ?>

    </div>

</footer>



<!-- ==================================================
     JAVASCRIPT
================================================== -->

<script src="js/script.js"></script>


<script>

    document.addEventListener(
        "DOMContentLoaded",
        function () {

            const menuBtn =
                document.getElementById(
                    "menuBtn"
                );

            const navLinks =
                document.getElementById(
                    "navLinks"
                );


            if (
                menuBtn &&
                navLinks
            ) {

                menuBtn.addEventListener(
                    "click",
                    function () {

                        navLinks.classList.toggle(
                            "show"
                        );

                    }
                );


                navLinks
                    .querySelectorAll("a")
                    .forEach(
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

        }
    );

</script>


</body>

</html>