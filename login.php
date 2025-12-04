<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - MyShop</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    margin: 0;
    padding-top: 120px;
}
.header {
    background-color: #007BFF;
    color: white;
    padding: 20px 0;
    text-align: center;
    position: fixed;
    top: 0;
    width: 100%;
}
.header h1 { margin:0; font-size:2.5em; font-weight:bold; }
.header p { margin:5px 0 0 0; font-size:1.2em; }
.login-form {
    background:white;
    padding:30px;
    border-radius:8px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    width:100%;
    max-width:400px;
}
.login-form h2 { text-align:center; margin-bottom:20px; }
.form-group { margin-bottom:15px; }
label { display:block; margin-bottom:5px; font-weight:bold; }
input[type="email"], input[type="password"] {
    width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;
}
.btn {
    background-color: #28a745; color:white; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; width:100%; font-size:16px;
}
.btn:hover { background-color:#218838; }
.error {
    color:red; padding:10px; margin:10px 0; background-color:#f8d7da; border:1px solid #f5c6cb; border-radius:4px; text-align:center;
}
.back-link { text-align:center; margin-top:15px; }
.back-link a { color:#007BFF; text-decoration:none; }
.back-link a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="header">
    <h1>MyShop</h1>
    <p>Sign In to Your Account</p>
</div>

<div class="login-form">
    <h2>Login</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form method="post" action="process_login.php">
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="form-group">
            <input type="submit" class="btn" value="Sign In">
        </div>
    </form>

    <div class="back-link">
        <a href="index.php">Back to Home</a>
    </div>
</div>
</body>
</html>
