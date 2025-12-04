<?php
session_start(); // Start session to store user info

// MySQL database connection
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "myadmin@sarahmdb"; 
$password = "YourPasswordHere"; 

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    header("Location: login.php?error=" . urlencode("Database connection failed: " . $conn->connect_error));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];

    if (!empty($email) && !empty($password_input)) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, password FROM shopusers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows === 1){
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();

            if(password_verify($password_input, $hashed_password)){
                // Login success
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                header("Location: index.php"); // redirect to home page
                exit();
            } else {
                header("Location: login.php?error=" . urlencode("Invalid password"));
                exit();
            }
        } else {
            header("Location: login.php?error=" . urlencode("Email not found"));
            exit();
        }

        $stmt->close();
    } else {
        header("Location: login.php?error=" . urlencode("Please fill all fields"));
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

$conn->close();
?>
