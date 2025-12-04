<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Only process form submissions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// Azure MySQL connection
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01@sarahmdb"; // full username
$password = "Cardiff01";       // your real password
$ssl_ca = __DIR__ . "/MysqlflexGlobalRootCA.crt.pem"; // path to SSL cert

// Connect using MySQLi with SSL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);
if (!$conn->real_connect($host, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get POST data
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';

// Validate
if (!$email || !$password_input) {
    header("Location: login.php?error=" . urlencode("Please enter email and password"));
    exit();
}

// Fetch user
$stmt = $conn->prepare("SELECT id, name, email, password FROM shopusers WHERE email=? LIMIT 1");
if (!$stmt) die("Prepare failed: " . $conn->error);

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name, $db_email, $hashed_password);
    $stmt->fetch();

    if (password_verify($password_input, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=" . urlencode("Invalid password"));
        exit();
    }
} else {
    header("Location: login.php?error=" . urlencode("User not found"));
    exit();
}

$stmt->close();
$conn->close();
?>
