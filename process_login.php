<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Azure MySQL connection
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

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (!$email || !$password_input) {
        header("Location: login.php?error=" . urlencode("Please enter email and password"));
        exit();
    }

    // Fetch user
    $stmt = $mysqli->prepare("SELECT id, name, password FROM shopusers WHERE email=? LIMIT 1");
    if (!$stmt) die("Prepare failed: " . $mysqli->error);

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password_input, $hashed_password)) {
            // Login successful
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
} else {
    die("Invalid request method.");
}

$mysqli->close();
?>
