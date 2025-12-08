<?php
session_start();

// Require the centralized configuration file
require 'db_config.php'; 

// --- AZURE FUNCTION CONFIGURATION ---
$azure_function_url = "https://sarahm-funcapp-a4ffeyebfqc3atay.ukwest-01.azurewebsites.net/validatepayment";
// ---

// 1. Security and Initial Validation Checks
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: shop.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'] ?? [];
$total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);
// CRITICAL CHANGE: Extract the token from the POST data submitted by cart.php
$payment_token = filter_input(INPUT_POST, 'payment_token', FILTER_SANITIZE_STRING);


if (empty($cart_items) || $total_amount === false || $total_amount <= 0) {
    header("Location: cart.php?error=" . urlencode("Cart is empty or total is invalid."));
    exit();
}
// Add check for the required payment token
if (empty($payment_token)) {
    // This catches an explicit failure case from the client (e.g., if JS cleared the token)
    header("Location: cart.php?error=" . urlencode("Payment token is missing. Please re-enter payment details."));
    exit();
}


// 2. --- PREPARE DATA FOR AZURE FUNCTION ---
$payment_data = [
    'amount' => $total_amount, 
    'currency' => 'GBP', // Use your intended currency code
    'token' => $payment_token       // USE THE SUBMITTED TOKEN HERE
];


// 3. --- EXTERNAL VALIDATION VIA AZURE FUNCTION ---

$ch = curl_init($azure_function_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($payment_data))
]);

$response_json = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);


// 4. --- Handle Validation Response ---

if ($curl_error) {
    // Curl failed (network error)
    $error_message = "Payment service failed to respond. Please try again. (Code: 101)";
    header("Location: cart.php?error=" . urlencode($error_message));
    exit();
}

if ($http_status !== 200) {
    // The Azure Function returned a validation failure (e.g., HTTP 400 Bad Request)
    $response_data = json_decode($response_json, true);
    $error_detail = $response_data['message'] ?? "Unknown Validation Error (Status: {$http_status}).";
    $error_message = "Payment Validation Failed: " . $error_detail;
    
    // Redirect back to the cart with the specific error message from the Azure Function
    header("Location: cart.php?error=" . urlencode($error_message));
    exit();
}


// 5. --- START SECURE ORDER PROCESS (Validation Passed) ---

// Initialize $order_id and error tracking
$order_id = null; 
$success = true;

// Start Database Transaction
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

    // ----------------------------------------------------------------------
    // NOTE: This is where the code would usually call the REAL payment gateway 
    // to capture the funds using the validated $payment_token.
    // ----------------------------------------------------------------------

    // --- C. COMMIT TRANSACTION ---
    $conn->commit();
    
    // 6. CLEAR CART SESSION
    unset($_SESSION['cart']);

} catch (Exception $e) {
    // 7. ROLLBACK TRANSACTION on error
    $conn->rollback();
    
    error_log("Order Process Failed (Transaction Rolled Back): " . $e->getMessage());
    $success = false;
}

$conn->close();

// 8. Final Redirect based on success
if ($success && $order_id) { 
    header("Location: order_success.php?order_id=" . $order_id);
} else {
    header("Location: cart.php?error=" . urlencode("We could not finalize your order due to an internal error."));
}

exit();
