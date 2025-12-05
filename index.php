<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyShop - Welcome</title>

<style>
    body {
        margin: 0;
        font-family: "Times New Roman", serif;
        background-color: white;
    }

    /* ----- HEADER (MATCHES SCREENSHOT) ----- */
    .header {
        background-color: #007bff; /* bold bright blue */
        color: white;
        text-align: center;
        padding: 50px 0;
        width: 100%;
    }
    .header h1 {
        margin: 0;
        font-size: 48px;
        font-weight: bold;
    }
    .header p {
        margin-top: 10px;
        font-size: 20px;
    }

    /* ----- PAGE CONTENT ----- */
    .content {
        text-align: center;
        margin-top: 40px;
        font-family: Georgia, serif; /* matches screenshot heading style */
    }

    .content h2 {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .content p {
        font-size: 20px;
        margin-bottom: 40px;
        color: #444;
    }

    /* ----- BUTTONS (EXACT MATCH) ----- */
    .btn {
        display: inline-block;
        padding: 12px 40px;
        font-size: 20px;
        border-radius: 4px;
        text-decoration: none;
        font-family: Arial, sans-serif;
        margin: 0 10px;
    }

    /* Blue button */
    .signup-link {
        background-color: #0069d9;
        color: white;
    }
    .signup-link:hover {
        background-color: #0056b3;
    }

    /* Green button */
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

<div class="content">
    <h2>Welcome to MyShop</h2>
    <p>Join our community today to access exclusive deals and features!</p>

    <a href="register.php" class="btn signup-link">Create Your Account</a>
    <a href="login.php" class="btn login-link">Sign In</a>
</div>

</body>
</html>
