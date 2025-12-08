<?php
session_start();

// Require the centralized configuration file
require 'db_config.php'; 

// --- AZURE FUNCTION CONFIGURATION ---
$azure_function_url = "https://sarahm-funcapp-a4ffeyebfqc3atay.ukwest-01.azurewebsites.net/validatepayment";
// Note: In a real system, you would abstract this URL into db_config.php or environment variables.
// ---

// 1. Security and Initial Validation Checks
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

// 2. --- PAYMENT DATA MOCKUP (Replace with actual form data in production) ---
// For testing, we mock the payment token and currency.
// In a real e-commerce flow, this data would come from a payment gateway 
// (e.g., a token received client-side after payment form submission).
$payment_data = [
    'amount' => $total_amount, 
    'currency' => 'GBP', // Change to your intended currency
    'token' => 'tok_' . bin2hex(random_bytes(16)) // Simulate a unique, valid token
];


// 3. --- EXTERNAL VALIDATION VIA AZURE FUNCTION ---

$ch = curl_init($azure_function_url);

// Configure cURL options for POST request
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
    $error_detail = $response_data['message'] ?? "Unknown Validation Error.";
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
    // Note: You might set the Status to 'Payment_Authorized' instead of 'Processing' 
    // here, anticipating a second step to confirm/capture payment.
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
    // --- C. (New Step): CALL REAL PAYMENT GATEWAY API HERE ---
    // At this point, the data is validated. The next step would be to call 
    // Stripe/PayPal/etc. using $payment_data['token'] to process the money.
    // If the payment gateway succeeds, you proceed; otherwise, you rollback.
    // ----------------------------------------------------------------------

    // --- D. COMMIT TRANSACTION ---
    $conn->commit();
    
    // 6. CLEAR CART SESSION
    unset($_SESSION['cart']);

} catch (Exception $e) {
    // 7. ROLLBACK TRANSACTION on error
    $conn->rollback();
    
    // Log the detailed error internally
    error_log("Order Process Failed (Transaction Rolled Back): " . $e->getMessage());
    $success = false;
}

$conn->close();

// 8. Final Redirect based on success
if ($success && $order_id) { 
    // Redirect to a success page showing the new order ID
    header("Location: order_success.php?order_id=" . $order_id);
} else {
    // Redirect back to cart with the generic error message
    header("Location: cart.php?error=" . urlencode("We could not finalize your order due to an internal error. Please contact support."));
}

exit();
