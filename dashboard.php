<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dashboard-container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            width: 320px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        a.logout-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #2575fc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        a.logout-button:hover {
            background-color: #6a11cb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
</body>
</html>
