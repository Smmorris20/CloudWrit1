<?php
session_start();
require 'db_config.php'; 

// Security check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

$order_details = null;
$order_items = [];

// 1. Fetch main order details
if ($order_id) {
    $stmt_order = $conn->prepare("SELECT UserID, TotalAmount, OrderDate FROM Orders WHERE OrderID = ? AND UserID = ?");
    if ($stmt_order) {
        $stmt_order->bind_param("ii", $order_id, $user_id);
        $stmt_order->execute();
        $result = $stmt_order->get_result();
        $order_details = $result->fetch_assoc();
        $stmt_order->close();
    }
}

// 2. Fetch order items
if ($order_details) {
    $stmt_items = $conn->prepare(
        "SELECT oi.Quantity, oi.PriceAtPurchase, p.ProductName 
         FROM Order_Items oi 
         JOIN Products p ON oi.ProductID = p.ProductID 
         WHERE oi.OrderID = ?"
    );
    if ($stmt_items) {
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $order_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_items->close();
    }
}

$conn->close();

// If the order was not found (or didn't belong to the user), redirect to shop
if (!$order_details) {
    header("Location: shop.php?error=" . urlencode("Order not found or access denied."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed!</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 800px; margin: 50px auto; padding: 20px; text-align: center; }
        .success-box { border: 2px solid #28a745; padding: 30px; border-radius: 8px; background-color: #e6ffed; }
        .success-box h1 { color: #28a745; margin-top: 0; }
        .order-summary-list { text-align: left; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px; }
        .order-summary-list div { margin-bottom: 8px; }
        .order-summary-list strong { display: inline-block; width: 150px; }
        .item-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .item-table th, .item-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    </style>
</head>
<body>
<div class="container">
    <div class="success-box">
        <h1>✅ Order Placed Successfully!</h1>
        <p>Thank you for your purchase, **<?php echo htmlspecialchars($_SESSION['name']); ?>**.</p>
        <p>Your order details are below. A confirmation email has been sent to your address.</p>

        <div class="order-summary-list">
            <div><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_details['OrderID']); ?></div>
            <div><strong>Order Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($order_details['OrderDate'])); ?></div>
            <div><strong>Total Paid:</strong> **$<?php echo number_format($order_details['TotalAmount'], 2); ?>**</div>
        </div>

        <h3>Items Ordered:</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Price/Unit</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                    <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                    <td>$<?php echo number_format($item['PriceAtPurchase'], 2); ?></td>
                    <td>$<?php echo number_format($item['PriceAtPurchase'] * $item['Quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top: 30px;"><a href="shop.php">Return to Shop</a></p>
    </div>
</div>
</body>
</html>
