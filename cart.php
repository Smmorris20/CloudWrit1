<?php
session_start();
// Require the centralized configuration file
require 'db_config.php'; 

// Security check: If the user is not logged in, redirect them.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$cart_items = $_SESSION['cart'] ?? [];
$subtotal = 0;
// Define fixed shipping cost
$shipping = 5.00; 

// Calculate Subtotal based on items in the session cart
foreach ($cart_items as $item) {
    // Price from DB (secure) * Quantity from session
    $subtotal += $item['price'] * $item['quantity'];
}

// Calculate Final Total
$total = $subtotal + $shipping; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Base styles */
        body { font-family: Arial, sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .cart-table th, .cart-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .cart-table th { background-color: #f2f2f2; }
        .totals-summary { float: right; width: 300px; margin-top: 20px; border: 1px solid #ccc; padding: 15px; border-radius: 4px; }
        .totals-summary div { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .totals-summary .total-amount { font-weight: bold; font-size: 1.5em; color: #007BFF; border-top: 1px solid #ccc; padding-top: 10px; margin-top: 10px; }
        .checkout-btn { display: block; width: 100%; padding: 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 1.2em; cursor: pointer; margin-top: 20px; }
        .error-message { color: red; background-color: #ffe0e0; padding: 10px; border: 1px solid red; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Your Shopping Cart</h1>
    <p><a href="shop.php">← Continue Shopping</a></p>

    <?php 
    // Display any errors (e.g., if redirected from process_order.php)
    if (isset($_GET['error'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is currently empty.</p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $product_id => $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals-summary">
            <div><span>Subtotal:</span> <span>$<?php echo number_format($subtotal, 2); ?></span></div>
            <div><span>Shipping:</span> <span>$<?php echo number_format($shipping, 2); ?></span></div>
            <div class="total-amount"><span>Total:</span> <span>$<?php echo number_format($total, 2); ?></span></div>

            <form method="POST" action="process_order.php">
                <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                <button type="submit" class="checkout-btn">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
