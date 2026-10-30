<?php include("includes/header.php"); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="index.php">Daily Bazaar</a>
        <button
            class="navbar-toggler"
            data-bs-toggle="collapse"
            data-bs-target="#menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products/index.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning ms-3" href="auth/login.php">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="text-center">
        <h1>Daily Bazaar Price Update System</h1>
        <p class="lead">
            Check Daily Vegetable, Fruit and Grocery Prices Easily.
        </p>
        <a href="products/index.php" class="btn btn-success">
            View Today's Prices
        </a>
    </div>
</div>
<?php include("includes/footer.php"); ?>