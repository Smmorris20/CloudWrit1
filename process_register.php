<?php
session_start();
// Development settings - remove these lines in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// Azure MySQL connection configuration
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01"; 
$password = "Cardiff01"; // <-- Verify this!
$ssl_ca = __DIR__ . "/ssl/DigiCertGlobalRootG2.crt.pem"; 

// --- Database Connection ---
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);

if (!$conn->real_connect($host, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    error_log("DB Connect Error: " . mysqli_connect_error());
    header("Location: register.php?error=" . urlencode("A server error occurred. Please try again."));
    exit();
}

// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';

// --- Input Validation ---
if (!$name || !$email || !$password_input) {
    header("Location: register.php?error=" . urlencode("Please fill all fields"));
    exit();
}

// --- Check for Existing User ---
$check_stmt = $conn->prepare("SELECT email FROM shopusers WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->close();
    $conn->close(); // Close connection before exit
    header("Location: register.php?error=" . urlencode("That email is already registered."));
    exit();
}
$check_stmt->close();

// --- Insert User ---
$hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    $conn->close(); // Close connection before exit
    header("Location: register.php?error=" . urlencode("A database setup error occurred."));
    exit();
}

$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close(); // Close connection
    header("Location: success.php");
    exit();
} else {
    error_log("Execution failed: " . $stmt->error);
    $stmt->close();
    $conn->close(); // Close connection before exit
    header("Location: register.php?error=" . urlencode("Database error: Registration failed."));
    exit();
}

$stmt->close(); // Should be unreachable if logic is correct, but safe to keep.
$conn->close(); // Should be unreachable.
?>
