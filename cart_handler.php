<?php
session_start();

// Security check: Only allow access via POST for the 'add' action
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit();
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if we have a valid product ID and action
if ($product_id && $action === 'add') {
    
    // Check if item already exists in cart (no DB query needed yet)
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity']++;
    } else {
        // --- 1. Fetch Price and Name from Database (SECURITY STEP) ---
        // Require the centralized connection file
        require 'db_config.php'; 
        
        $stmt = $conn->prepare("SELECT ProductName, Price FROM Products WHERE ProductID = ? AND StockQuantity > 0 LIMIT 1");
        
        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($product = $result->fetch_assoc()) {
                // Add new item to cart using SECURE, database-validated data
                $_SESSION['cart'][$product_id] = [
                    'id'       => $product_id,
                    'name'     => $product['ProductName'], // <-- Secured from DB
                    'price'    => $product['Price'],      // <-- Secured from DB
                    'quantity' => 1
                ];
            }
            $stmt->close();
        } else {
            error_log("Cart Handler DB Prepare Failed: " . $conn->error);
        }
        $conn->close();
    }
}

// Redirect the user back to the shop page after adding the item
header("Location: shop.php");
exit();
