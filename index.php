<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyShop - Welcome</title>
<link rel="stylesheet" href="styles.css">
<style>
    /* Header styling */
    .header {
        background-color: #007BFF; /* Blue background */
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-bottom: 30px;
    }

    /* Bold, stand-out title */
    .header h1 {
        margin: 0;
        font-size: 3em;
        font-weight: bold;
    }

    .header p {
        margin: 5px 0 0 0;
        font-size: 1.2em;
    }

    /* Container */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Welcome text */
    .welcome-text {
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.2em;
        color: #555;
    }

    /* Signup link */
    .signup-link {
        display: inline-block;
        background-color: #0056b3;
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 18px;
        margin-top: 20px;
    }

    .signup-link:hover {
        background-color: #003d80;
    }

    /* Login link */
    .login-link {
        display: inline-block;
        background-color: #28a745; /* Green for login */
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 18px;
        margin-top: 20px;
        margin-left: 10px; /* spacing from signup button */
    }

    .login-link:hover {
        background-color: #218838;
    }
</style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>MyShop</h1>
            <p>Your one-stop destination for amazing products</p>
        </div>
    </div>

    <div class="container">
        <div class="welcome-text">
            <h2>Welcome to MyShop</h2>
            <p>Join our community today to access exclusive deals and features!</p>

            <!-- Signup and Login links together -->
            <a href="register.php" class="signup-link">Create Your Account</a>
            <a href="login.php" class="login-link">Sign In</a>
        </div>
    </div>
</body>
</html>
