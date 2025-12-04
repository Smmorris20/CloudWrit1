<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Azure MySQL connection details
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01@sarahmdb"; // full username
$password = "Cardiff01";       // actual password
$ssl_ca = __DIR__ . "/MysqlflexGlobalRootCA.crt.pem"; // path to CA file

// Initialize MySQLi
$mysqli = mysqli_init();
if (!$mysqli) {
    die("MySQLi initialization failed");
}

// Enable SSL
mysqli_ssl_set($mysqli, NULL, NULL, $ssl_ca, NULL, NULL);

// Create connection
$conn = mysqli_real_connect(
    $mysqli,
    $host,
    $username,
    $password,
    $dbname,
    3306,
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (!$name || !$email || !$password_input) {
        die("Please fill in all required fields.");
    }

    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

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

$mysqli->close();
?>
