<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - MyShop</title>
    <style>
        /* Body */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            text-align: center;
            padding-top: 120px; /* Space for fixed header */
        }

        /* Header */
        .header {
            background-color: #007BFF; /* Blue header */
            color: white;
            padding: 20px 0;
            text-align: center;
            position: fixed;
            top: 0;
            width: 100%;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 1.2em;
        }

        /* Success message container */
        .success-message {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 60px auto 0 auto;
        }

        /* Success icon (optional for future icons) */
        .success-icon {
            font-size: 48px;
            color: #28a745; /* Green for success */
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MyShop</h1>
        <p>Registration Successful</p>
    </div>

    <div class="success-message">
        <div class="success-icon">✔</div>
        <h2>Registration Successful!</h2>
        <p>Your account has been created successfully. Welcome to MyShop!</p>

        <div style="margin-top: 30px;">
            <a href="index.php" class="btn">Go Back to Home</a>
            <a href="register.php" class="btn btn-success">Register Another User</a>
        </div>
    </div>
</body>
</html>
