<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyShop - Welcome</title>

<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
    }

    /* HEADER */
    .header {
        background-color: #007bff; /* Bold blue */
        color: white;
        padding: 40px 0;
        text-align: center;
    }
    .header h1 {
        margin: 0;
        font-size: 3em;
        font-weight: bold;
    }
    .header p {
        margin-top: 8px;
        font-size: 1.2em;
    }

    /* LAYOUT */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        text-align: center;
    }
    .welcome-text h2 {
        font-size: 2em;
        margin-bottom: 10px;
    }
    .welcome-text p {
        color: #555;
        font-size: 1.2em;
    }

    /* BUTTONS (bold blue style restored) */
    .btn {
        display: inline-block;
        padding: 12px 30px;
        border-radius: 6px;
        font-size: 18px;
        font-weight: bold;
        text-decoration: none;
        margin: 15px 10px 0 10px;
        transition: 0.25s ease-in-out;
    }

    /* Browse Products (optional) */
    .shop-link {
        background-color: #0056b3;
        color: white;
    }
    .shop-link:hover {
        background-color: #003d80;
    }

    /* Create account – strong blue button */
    .signup-link {
        background-color: #0056b3;
        color: white;
    }
    .signup-link:hover {
        background-color: #003d80;
    }

    /* Sign in – green */
    .login-link {
        background-color: #28a745;
        color: white;
    }
    .login-link:hover {
        background-color: #1e7e34;
    }

</style>
</head>

<body>

<div class="header">
    <h1>ShopSphere</h1>
    <p>Your one-stop destination for amazing products by Sarah Morris ST20285209</p>
</div>

<div class="container welcome-text">
    <h2>Welcome to MyShop</h2>
    <p>Join our community today to access exclusive deals and features!</p>

    <a href="shop.php" class="btn shop-link">Browse Products</a>
    <a href="register.php" class="btn signup-link">Create Your Account</a>
    <a href="login.php" class="btn login-link">Sign In</a>
</div>

</body>
</html>
