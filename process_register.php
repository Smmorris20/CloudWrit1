<!DOCTYPE html>
<?php
// MySQL database connection
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01@sarahmdb"; // note the full username for Azure MySQL
$password = "Cardiff01"; // replace with your real password

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header("Location: register.php?error=" . urlencode("Database connection failed: " . $conn->connect_error));
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password_input)) {

        // Hash password
        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            // Success, redirect
            header("Location: success.php");
            exit();
        } else {
            header("Location: register.php?error=" . urlencode("Database error: " . $stmt->error));
            exit();
        }

        $stmt->close();
    } else {
        header("Location: register.php?error=" . urlencode("Please fill all fields"));
        exit();
    }
} else {
    // If accessed directly
    header("Location: register.php");
    exit();
}

$conn->close();
?>
