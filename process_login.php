<?php
session_start();
// Development error display—turn these OFF in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Only process form submissions (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// -----------------------------------------------------------------
// 1. CENTRALIZED DATABASE CONNECTION
// Replace the duplicated connection block with a single include.
// This file must define the $conn variable and handle connection failure.
require 'db_config.php';
// -----------------------------------------------------------------

// Get and sanitize POST data
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';

// 2. Validate Input
if (!$email || !$password_input) {
    header("Location: login.php?error=" . urlencode("Please enter email and password."));
    // Always close connection on script exit paths
    $conn->close(); 
    exit();
}

// 3. Fetch user by email
// Use prepared statements for security
$stmt = $conn->prepare("SELECT id, name, password FROM shopusers WHERE email=? LIMIT 1");
if (!$stmt) {
    error_log("Login Prepare Failed: " . $conn->error);
    header("Location: login.php?error=" . urlencode("A server error occurred."));
    $conn->close(); 
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// Check if exactly one user was found
if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    // 4. Verify Password (Securely)
    if (password_verify($password_input, $hashed_password)) {
        // SUCCESS: Set session variables
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $name; // Changed to 'name' for consistency
        
        // Cleanup and Redirect to the Shop Page
        $stmt->close();
        $conn->close();
        header("Location: shop.php"); // <--- Changed redirect to shop.php
        exit();
    }
}

// 5. Failure Handling (Default to generic message for security)
// If password_verify failed OR user was not found, execute this block.
// Using a generic message prevents timing attacks that reveal if the user exists.
header("Location: login.php?error=" . urlencode("Invalid email or password."));

$stmt->close();
$conn->close();
exit();
?>
