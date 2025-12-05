<?php
session_start();

// Security check: Only allow access via POST for the 'add' action
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit();
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$product_name = $_POST['product_name'] ?? 'Unknown Product';
$product_price = filter_input(INPUT_POST, 'product_price', FILTER_VALIDATE_FLOAT);
$action = $_POST['action'] ?? '';

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($product_id && $product_price !== false && $action === 'add') {
    
    // Check if the item is already in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        // Increment quantity if it exists
        $_SESSION['cart'][$product_id]['quantity']++;
    } else {
        // Add new item to the cart
        $_SESSION['cart'][$product_id] = [
            'id'       => $product_id,
            'name'     => $product_name,
            'price'    => $product_price,
            'quantity' => 1
        ];
    }
}

// Redirect the user back to the shop page after adding the item
header("Location: shop.php");
exit();
?>
