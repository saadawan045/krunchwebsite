<?php
// Start the session
session_start();

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=krunchwebsite;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Check if the customer is logged in and has a customer_id in the session
if (!isset($_SESSION['user_id'])) {
    echo "User is not logged in.";
    header("Location: login.php");
    exit;
}

// Ensure the cart is initialized
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $cartEmpty = true;
} else {
    $cartEmpty = false;
}

// Get the current order ID (create one if it doesn't exist)
if (!isset($_SESSION['order_id'])) {
    // Create a new order in the 'yes' table
    try {
        $stmt = $pdo->prepare("INSERT INTO yes (customer_id) VALUES (:customer_id)");
        $stmt->execute(['customer_id' => $_SESSION['user_id']]);
        $_SESSION['order_id'] = $pdo->lastInsertId();
    } catch (PDOException $e) {
        echo "Error creating order: " . htmlspecialchars($e->getMessage());
        exit;
    }
}
$orderId = $_SESSION['order_id'];

// Sync cart with order_items table
if (!$cartEmpty) {
    // Clear existing items for this order in the order_items table
    try {
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);

        // Insert the current cart items into the order_items table
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            // Fetch product details (including price) from the products table
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = :product_id");
            $stmt->execute(['product_id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                // Calculate subtotal for this product
                $subtotal = $quantity * $product['price'];
                
                // Insert the item into the order_items table with price included
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
                $stmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product['price']  // Ensure the price is inserted
                ]);
            }
        }
    } catch (PDOException $e) {
        echo "Error syncing cart with order_items: " . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Fetch cart details from the database
$cartItems = [];
$totalPrice = 0;

if (!$cartEmpty) {
    $productIds = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($productIds)");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $productId = $product['id'];
            $quantity = $_SESSION['cart'][$productId];
            $subtotal = $quantity * $product['price'];
            $totalPrice += $subtotal;

            $cartItems[] = [
                'id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'image' => $product['image'],
            ];
        }
    } catch (PDOException $e) {
        echo "Error fetching cart details: " . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Handle item removal (single item)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_one'])) {
    $productId = intval($_POST['remove_one']);
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]--;
        if ($_SESSION['cart'][$productId] <= 0) {
            unset($_SESSION['cart'][$productId]);
        }
    }
    header("Location: cart.php");
    exit;
}

// Handle item removal (all items)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_all'])) {
    $productId = intval($_POST['remove_all']);
    unset($_SESSION['cart'][$productId]);
    header("Location: cart.php");
    exit;
}

// Redirect to checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_to_checkout'])) {
    header("Location: checkout.php?order_id=" . $_SESSION['order_id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        
        body {
            font-family: 'Bangers', cursive;
            background-color: #1a1a1a;
            color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        h1 {
            color: #f0c808;
            margin-bottom: 20px;
        }

        .cart-container {
            max-width: 800px;
            width: 100%;
            border: 2px solid #f0c808;
            border-radius: 10px;
            background-color: #2e2e2e;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0c808;
            padding-bottom: 15px;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h3 {
            margin: 0 0 10px;
            color: #f0c808;
        }

        .cart-item-details p {
            margin: 5px 0;
        }

        .cart-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .cart-actions form {
            margin-bottom: 10px;
        }

        .total {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: right;
            color: #f0c808;
        }

        button {
            padding: 10px 20px;
            background-color: #f0c808;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #d4a500;
            color: white;
        }

        .checkout-button {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Your Shopping Cart</h1>

    <div class="cart-container">
        <?php if ($cartEmpty): ?>
            <p>Your cart is empty. <a href="menu.php" style="color: #f0c808;">Go back to the menu</a>.</p>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                <img src="uploads/<?php echo basename($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Subtotal: $<?php echo number_format($item['subtotal'], 2); ?></p>
                    </div>
                    <div class="cart-actions">
                        <form method="POST">
                            <button type="submit" name="remove_one" value="<?php echo $item['id']; ?>">Remove One</button>
                        </form>
                        <form method="POST">
                            <button type="submit" name="remove_all" value="<?php echo $item['id']; ?>">Remove All</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="total">
                <p>Total: $<?php echo number_format($totalPrice, 2); ?></p>
            </div>
            <form method="POST" class="checkout-button">
                <button type="submit" name="proceed_to_checkout">Proceed to Checkout</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
