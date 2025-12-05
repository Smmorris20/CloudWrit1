<?php
session_start();

// Require the centralized configuration file
require 'db_config.php'; 

// 1. Security and Validation Checks
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: shop.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'] ?? [];
$total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);

if (empty($cart_items) || $total_amount === false || $total_amount <= 0) {
    header("Location: cart.php?error=" . urlencode("Cart is empty or total is invalid."));
    exit();
}

// Initialize $order_id and error tracking
$order_id = null; 
$success = true;

// 2. Start Database Transaction
$conn->begin_transaction();

try {
    // --- A. INSERT INTO ORDERS TABLE ---
    $stmt_order = $conn->prepare("INSERT INTO Orders (UserID, TotalAmount, Status) VALUES (?, ?, 'Processing')");
    if (!$stmt_order) {
        throw new Exception("Order Prepare Failed: " . $conn->error);
    }
    
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
    
    // Log the detailed error internally
    error_log("Order Process Failed (Transaction Rolled Back): " . $e->getMessage());
    $success = false;
}

$conn->close();

// 5. Final Redirect based on success
if ($success && $order_id) { 
    // Redirect to a success page showing the new order ID
    header("Location: order_success.php?order_id=" . $order_id);
} else {
    // Redirect back to cart with the generic error message
    header("Location: cart.php?error=" . urlencode("We could not finalize your order. Please try again."));
}

exit();
?>
