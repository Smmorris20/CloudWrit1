<?php
session_start();

// 1. ACCESS CONTROL: If the user is not logged in, redirect them.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. DATABASE CONNECTION
// Requires the central config file to get the $conn object.
require 'db_config.php'; 

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
    // Handle error if query fails
    error_log("Product fetch error: " . $conn->error);
    $products_error = "Could not load products at this time.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop - Products</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* BASE STYLES COPIED FROM YOUR TEMPLATE */
        body {
            font-family: Arial, Helvetica, sans-serif; 
        }
        .header {
            background-color: #007BFF;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 3em;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 1.2em;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* SPECIFIC SHOP STYLES */
        .product-list { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 25px; 
            margin-top: 30px;
        }
        .product-card { 
            border: 1px solid #ddd; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
            text-align: center;
        }
        .product-card h3 { 
            color: #007BFF; 
            margin-top: 0;
        }
        .price { 
            font-weight: bold; 
            font-size: 1.5em; 
            color: #28a745; 
            margin: 10px 0 20px 0; 
            display: block; 
        }
        .add-to-cart { 
            /* Styled to match the look of your .signup-link button */
            display: block;
            width: 100%;
            background-color: #0056b3; 
            color: white; 
            padding: 12px 30px; 
            border: none;
            border-radius: 4px; 
            font-size: 18px; 
            cursor: pointer; 
            text-transform: uppercase;
        }
        .add-to-cart:hover {
            background-color: #003d80;
        }
        .cart-info {
            text-align: right;
            font-size: 1.1em;
            margin-top: 10px;
            margin-bottom: 20px; /* Added spacing */
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <h1>ShopSphere - Products</h1>
        <p>
            Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>! | 
            <a href="logout.php" style="color: white; text-decoration: underline;">Logout</a>
        </p>
    </div>
</header>

<div class="container">
    <div class="cart-info">
        You have **<?php echo count($_SESSION['cart']); ?>** unique items in your cart. 
        
        <?php if (count($_SESSION['cart']) > 0): ?>
            <a href="cart.php" style="font-weight: bold; margin-left: 15px; padding: 5px 10px; background-color: #ffc107; color: #333; border-radius: 4px; text-decoration: none;">
                View Cart →
            </a>
        <?php endif; ?>
    </div>

    <h2>Product Catalog</h2>

    <?php if (isset($products_error)): ?>
        <p style="color: red; text-align: center;"><?php echo $products_error; ?></p>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                    <span class="price">$<?php echo number_format($product['Price'], 2); ?></span>
                    
                    <form method="POST" action="cart_handler.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
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
