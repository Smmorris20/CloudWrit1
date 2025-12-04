<?php
// Enable error reporting temporarily for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MySQL database connection
$host = "sarahmdb.mysql.database.azure.com"; // Azure MySQL endpoint
$dbname = "mydatabase";
$username = "cmet01@sarahmdb"; // full username
$password = "Cardiff01"; // replace with actual password

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    // Basic validation
    if (!$name || !$email || !$password_input) {
        die("Please fill in all required fields.");
    }

    // Hash the password
    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $email, $hashed_password);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to success page
        header("Location: success.php");
        exit();
    } else {
        die("Database error: " . $stmt->error);
    }

    $stmt->close();
} else {
    // Block direct GET access
    die("Invalid request method.");
}

// Close connection
$conn->close();
?>
