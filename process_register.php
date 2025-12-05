<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Only process form submissions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// Azure MySQL connection
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01"; // full username
$password = "Cardiff01";       // your real password
$ssl_ca = __DIR__ . "/ssl/DigiCertGlobalRootG2.crt.pem"; // path to SSL cert

// Connect using MySQLi with SSL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);
if (!$conn->real_connect($host, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';

// Validate
if (!$name || !$email || !$password_input) {
    header("Location: register.php?error=" . urlencode("Please fill all fields"));
    exit();
}

// Hash password
$hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

// Insert user safely
$stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    header("Location: success.php");
    exit();
} else {
    header("Location: register.php?error=" . urlencode("Database error: " . $stmt->error));
    exit();
}

$stmt->close();
$conn->close();
?>
