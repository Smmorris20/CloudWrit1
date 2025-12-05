<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyShop - Welcome</title>
<link rel="stylesheet" href="styles.css">
<style>
    .header {
        background-color: #007BFF;
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-bottom: 30px;
    }
    .header h1 {
        margin: 0;
        font-size: 3em;
        font-weight: bold;
    }
    .header p {
        margin: 5px 0 0 0;
        font-size: 1.2em;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .welcome-text {
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.2em;
        color: #555;
    }
    /* --- FIX: Updated the selector list to include .shop-link --- */
    .shop-link, .signup-link, .login-link {
        display: inline-block;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 18px;
        margin-top: 20px;
        margin-right: 10px;
    }
    /* --- NEW SHOP LINK STYLING --- */
    .shop-link {
        background-color: #ffc107; /* Bright color to stand out */
        color: #333;
    }
    .shop-link:hover {
        background-color: #e0a800;
    }
    /* --- Existing Link Styles --- */
    .signup-link {
        background-color: #0056b3;
        color: white;
    }
    .signup-link:hover {
        background-color: #003d80;
    }
    .login-link {
        background-color: #28a745;
        color: white;
        margin-left: 0;
    }
    .login-link:hover {
        background-color: #218838;
    }
</style>
</head>
<body>
<div class="header">
    <div class="container">
        <h1>ShopSphere</h1>
        <p>Your one-stop destination for amazing products by Sarah Morris ST20285209</p>
    </div>
</div>

<div class="container">
    <div class="welcome-text">
        <h2>Welcome to MyShop</h2>
        <p>Join our community today to access exclusive deals and features!</p>
        
                <a href="shop.php" class="shop-link">Browse Products</a>
        
        <a href="register.php" class="signup-link">Create Your Account</a>
        <a href="login.php" class="login-link">Sign In</a>
    </div>
</div>
</body>
</html>
