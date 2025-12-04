<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MySQL connection
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01@sarahmdb";
$password = "YourPasswordHere";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if ($name && $email && $password_input) {
        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            die("Database error: " . $stmt->error);
        }

        $stmt->close();
    } else {
        die("Please fill all fields");
    }
} else {
    die("Invalid request method");
}

$conn->close();
?>
