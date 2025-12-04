<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Azure MySQL connection
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01@sarahmdb"; // full username
$password = "Cardiff01"; // replace with real password

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (!$name || !$email || !$password_input) {
        die("Please fill in all required fields.");
    }

    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: success.php");
        exit();
    } else {
        die("Database error: " . $stmt->error);
    }

    $stmt->close();
} else {
    die("Invalid request method.");
}

$conn->close();
?>
