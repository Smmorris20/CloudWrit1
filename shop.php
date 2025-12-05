<?php
session_start();

// 1. ACCESS CONTROL: If the user is not logged in, redirect them.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. DATABASE CONNECTION
require 'db_config.php'; // This establishes $conn

// 3. INITIALIZE CART: Ensure the cart session variable exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 4. FETCH PRODUCTS
$products = [];
// Select only products that are in stock
$stmt = $conn->prepare("SELECT ProductID, ProductName, Price FROM Products WHERE StockQuantity > 0 ORDER BY ProductName ASC");

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
} else {
    $products_error = "Could not load products at this time.";
    error_log("Product fetch error: " . $conn->error);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CloudWrit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic shop styling for layout */
        .container { max-width: 1000px; margin: 20px auto; padding: 0 20px; }
        .product-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .product-card { border: 1px solid #ccc; padding: 15px; border-radius: 8px; box-shadow: 2px 2px 5px rgba(0,0,0,0.1); }
        .price { font-weight: bold; font-size: 1.3em; color: #28a745; margin-top: 5px; display: block; }
        .add-to-cart { background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Shop, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</h1>
        <p>Your Cart currently has **<?php echo count($_SESSION['cart']); ?>** unique items. <a href="logout.php">Logout</a></p>
        <hr>

        <h2>Available Products</h2>

        <?php if (isset($products_error)): ?>
            <p style="color: red;"><?php echo $products_error; ?></p>
        <?php else: ?>
            <div class="product-list">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <h3><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                        <span class="price">$<?php echo number_format($product['Price'], 2); ?></span>
                        
                        <form method="POST" action="cart_handler.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['ProductName']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo $product['Price']; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="add-to-cart">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
