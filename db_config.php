<?php
// db_config.php

// -----------------------------------------------------
// Azure MySQL Connection Configuration and Execution
// -----------------------------------------------------

// Connection Parameters
$host = "sarahmdb.mysql.database.azure.com";
$dbname = "mydatabase";
$username = "cmet01";
$password = "Cardiff01"; // YOUR REAL PASSWORD
$ssl_ca = __DIR__ . "/ssl/DigiCertGlobalRootG2.crt.pem"; // path to SSL cert

// Connect using MySQLi with SSL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);

if (!$conn->real_connect($host, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    // Log error to server log but show a generic message to the user
    error_log("DB Connection Failed: " . mysqli_connect_error());
    die("Database connection failed due to a server error. Please try again later.");
}
// The variable $conn is now available for use in any file that includes this one.
?>
