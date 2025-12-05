<?php
session_start();
// --- TEMPORARY DEBUGGING START: KEEP THESE LINES! ---
// These force PHP to display errors.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
// --- END TEMPORARY DEBUGGING ---

// Require the centralized configuration file
require 'db_config.php'; 

// 1. Security and Validation Checks
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: shop.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'] ?? [];
// Validate the total amount received from the form
$total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);

if (empty($cart_items) || $total_amount === false || $total_amount <= 0) {
    header("Location: cart.php?error=" . urlencode("Cart is empty or total is invalid."));
    exit();
}

// Initialize $order_id for use outside the try block
$order_id = null; 

// 2. Start Database Transaction
$conn->begin_transaction();
$success = true;
$error_message = ""; 

try {
    // --- A. INSERT INTO ORDERS TABLE ---
    $stmt_order = $conn->prepare("INSERT INTO Orders (UserID, TotalAmount, Status) VALUES (?, ?, 'Processing')");
    if (!$stmt_order) {
        throw new Exception("Order Prepare Failed: " . $conn->error);
    }
    
    // 'i' for integer (UserID), 'd' for decimal/double (TotalAmount)
    $stmt_order->bind_param("id", $user_id, $total_amount); 
    if (!$stmt_order->execute()) {
        throw new Exception("Order Execution Failed: " . $stmt_order->error);
    }
    
    $order_id = $conn->insert_id; // Get the ID of the newly created order
    $stmt_order->close();

    // --- B. INSERT INTO ORDER_ITEMS TABLE ---
    $stmt_item = $conn->prepare("INSERT INTO Order_Items (OrderID, ProductID, Quantity, PriceAtPurchase) VALUES (?, ?, ?, ?)");
    if (!$stmt_item) {
        throw new Exception("Item Prepare Failed: " . $conn->error);
    }
    
    foreach ($cart_items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        // 'iii' for three integers (OrderID, ProductID, Quantity), 'd' for decimal (Price)
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price); 
        
        if (!$stmt_item->execute()) {
            throw new Exception("Item Execution Failed for ProductID: $product_id - " . $stmt_item->error);
        }
    }
    $stmt_item->close();

    // --- D. COMMIT TRANSACTION ---
    $conn->commit();
    
    // 3. CLEAR CART SESSION
    unset($_SESSION['cart']);

} catch (Exception $e) {
    // 4. ROLLBACK TRANSACTION on error
    $conn->rollback();
    $error_message = $e->getMessage();
    $success = false;
}

$conn->close();

// ----------------------------------------------------------------------------------
// CRITICAL DEBUGGING LINE: Displays the SQL error instead of redirecting on failure
if (!$success) {
    die("TRANSACTION FAILED: " . htmlspecialchars($error_message));
}
// ----------------------------------------------------------------------------------


// 5. Final Redirect based on success
if ($success && $order_id) { 
    // Success redirect
    header("Location: order_success.php?order_id=" . $order_id);
} else {
    // If the script runs here (which it shouldn't, due to the die() above), use the generic failure.
    header("Location: cart.php?error=" . urlencode("A critical error occurred. Check logs."));
}

exit();
?>
