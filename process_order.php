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
$payment_token = filter_input(INPUT_POST, 'payment_token', FILTER_SANITIZE_STRING);

if (empty($cart_items) || $total_amount === false || $total_amount <= 0) {
    header("Location: cart.php?error=" . urlencode("Cart is empty or total is invalid."));
    exit();
}

if (empty($payment_token)) {
    header("Location: cart.php?error=" . urlencode("Payment token is missing. Please re-enter payment details."));
    exit();
}

// 2. Prepare data for Azure Function
$payment_data = [
    'amount' => $total_amount, 
    'currency' => 'GBP', 
    'token' => $payment_token
];

// 3. Call Azure Function for payment validation
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

if ($curl_error) {
    $error_message = "Payment service failed to respond. Please try again. (Code: 101)";
    header("Location: cart.php?error=" . urlencode($error_message));
    exit();
}

if ($http_status !== 200) {
    $response_data = json_decode($response_json, true);
    $error_detail = $response_data['message'] ?? "Unknown Validation Error (Status: {$http_status}).";
    $error_message = "Payment Validation Failed: " . $error_detail;
    header("Location: cart.php?error=" . urlencode($error_message));
    exit();
}

// 4. Start secure order process (transaction)
$success = true;
$order_id = null;
$conn->begin_transaction();

try {
    // --- A. Check stock and deduct quantities ---
    foreach ($cart_items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];

        // Check current stock
        $stmt_stock = $conn->prepare("SELECT stock FROM Products WHERE ProductID = ? FOR UPDATE");
        $stmt_stock->bind_param("i", $product_id);
        $stmt_stock->execute();
        $stmt_stock->bind_result($current_stock);
        $stmt_stock->fetch();
        $stmt_stock->close();

        if ($quantity > $current_stock) {
            throw new Exception("Not enough stock for product ID: $product_id");
        }

        // Deduct stock
        $stmt_update = $conn->prepare("UPDATE Products SET stock = stock - ? WHERE ProductID = ?");
        $stmt_update->bind_param("ii", $quantity, $product_id);
        if (!$stmt_update->execute()) {
            throw new Exception("Failed to update stock for product ID: $product_id");
        }
        $stmt_update->close();
    }

    // --- B. Insert into Orders table ---
    $stmt_order = $conn->prepare("INSERT INTO Orders (UserID, TotalAmount, Status) VALUES (?, ?, 'Processing')");
    if (!$stmt_order) throw new Exception("Order Prepare Failed: " . $conn->error);
    $stmt_order->bind_param("id", $user_id, $total_amount);
    if (!$stmt_order->execute()) throw new Exception("Order Execution Failed: " . $stmt_order->error);
    $order_id = $conn->insert_id;
    $stmt_order->close();

    // --- C. Insert into Order_Items table ---
    $stmt_item = $conn->prepare("INSERT INTO Order_Items (OrderID, ProductID, Quantity, PriceAtPurchase) VALUES (?, ?, ?, ?)");
    if (!$stmt_item) throw new Exception("Item Prepare Failed: " . $conn->error);

    foreach ($cart_items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        if (!$stmt_item->execute()) throw new Exception("Item Execution Failed for ProductID: $product_id - " . $stmt_item->error);
    }
    $stmt_item->close();

    // --- D. COMMIT TRANSACTION ---
    $conn->commit();
    unset($_SESSION['cart']);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Order process failed (transaction rolled back): " . $e->getMessage());
    $success = false;
}

$conn->close();

// 5. Final redirect
if ($success && $order_id) {
    header("Location: order_success.php?order_id=" . $order_id);
} else {
    header("Location: cart.php?error=" . urlencode("We could not finalize your order due to an internal error."));
}
exit();
?>
