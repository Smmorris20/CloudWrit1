<?php
session_start();
// Development error display—turn these OFF in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Only process form submissions (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// -----------------------------------------------------------------
// 1. CENTRALIZED DATABASE CONNECTION
// This replaces the entire duplicated connection block.
// The db_config.php file must define the $conn variable and handle connection failure.
require 'db_config.php';
// -----------------------------------------------------------------

// Get POST data
// Sanitize input by trimming whitespace
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';

// --- Input Validation ---
if (!$name || !$email || !$password_input) {
    header("Location: register.php?error=" . urlencode("Please fill all fields."));
    $conn->close(); // Close connection before exit
    exit();
}

// --- Check for Existing User ---
$check_stmt = $conn->prepare("SELECT email FROM shopusers WHERE email = ?");
if (!$check_stmt) {
    error_log("Prepare failed (Email Check): " . $conn->error);
    header("Location: register.php?error=" . urlencode("A server error occurred."));
    $conn->close(); 
    exit();
}

$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->close();
    $conn->close(); 
    header("Location: register.php?error=" . urlencode("That email is already registered."));
    exit();
}
$check_stmt->close();

// --- Insert User ---
// Hash password securely (Bcrypt is default and robust)
$hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed (User Insert): " . $conn->error);
    $conn->close();
    header("Location: register.php?error=" . urlencode("A database setup error occurred."));
    exit();
}

$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    // SUCCESS: Registration complete
    $stmt->close();
    $conn->close(); 
    header("Location: success.php");
    exit();
} else {
    // FAILURE: Database execution failed
    error_log("Execution failed: " . $stmt->error);
    $stmt->close();
    $conn->close();
    header("Location: register.php?error=" . urlencode("Database error: Registration failed."));
    exit();
}

// Any remaining cleanup lines are now unreachable due to explicit exit() calls above.
?>
