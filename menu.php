<?php
// Start the session
session_start();

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=krunchwebsite;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Initialize cart in session if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if the customer is logged in and has a customer_id in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$customerId = $_SESSION['user_id'];

// Fetch categories from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . htmlspecialchars($e->getMessage()));
}

// Fetch products with category information from the database
try {
    $stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . htmlspecialchars($e->getMessage()));
}

// Handle Add to Cart functionality via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);

    if ($productId > 0) {
        // Create a new order ID if it doesn't exist
        if (!isset($_SESSION['order_id'])) {
            $stmt = $pdo->prepare("INSERT INTO yes (customer_id, order_status) VALUES (?, 'pending')");
            $stmt->execute([$customerId]);
            $_SESSION['order_id'] = $pdo->lastInsertId();
        }

        // Add the product to the session cart
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 0;
        }
        $_SESSION['cart'][$productId]++;

        // Respond with the updated cart count
        echo json_encode(['cartCount' => array_sum($_SESSION['cart'])]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Menu</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

        body {
            font-family: 'Bangers', cursive;
            background-color: #1a1a1a;
            margin: 0;
            padding: 0;
            color: #f5f5f5;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            height: 100%;
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1 {
            text-align: center;
            color: #f0c808;
            font-size: 2.5rem;
            margin-top: 30px;
            flex: 1;
        }

        .menu-container {
            width: 100%;
            max-width: 1200px;
            padding: 20px;
            box-sizing: border-box;
        }

        .category {
            margin-bottom: 40px;
        }

        .category-header {
            font-size: 1.6rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #f0c808;
            border-bottom: 2px solid #f0c808;
            padding-bottom: 5px;
        }

        .menu-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .menu-item {
            width: 100%;
            max-width: 320px;
            border: 2px solid #f0c808;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background-color: #2e2e2e;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .menu-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .menu-item h3 {
            margin-top: 15px;
            font-size: 1.3rem;
            font-weight: bold;
            color: #f0c808;
        }

        .menu-item p {
            font-size: 14px;
            color: #ddd;
        }

        .menu-item .price {
            font-weight: bold;
            color: #f0c808;
            font-size: 1.2rem;
        }

        .cart-icon {
            position: fixed;
            top: 15px;
            right: 15px;
            width: 60px;
            height: auto;
            cursor: pointer;
            z-index: 1000;
        }

        .cart-icon img {
            width: 100%;
            height: auto;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 1rem;
            font-weight: bold;
        }

        button {
            padding: 12px 25px;
            background-color: #f0c808;
            color: black;
            border: 2px solid #f0c808;
            cursor: pointer;
            margin-top: 15px;
            font-size: 1.1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        button:hover {
            background-color: #d4a500;
            color: white;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const addToCartForms = document.querySelectorAll("form");

            addToCartForms.forEach((form) => {
                form.addEventListener("submit", function (event) {
                    event.preventDefault();

                    const formData = new FormData(this);

                    fetch("menu.php", {
                        method: "POST",
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.cartCount !== undefined) {
                                const cartCountElement = document.querySelector(".cart-count");
                                cartCountElement.textContent = data.cartCount;
                            }
                        })
                        .catch((error) => {
                            console.error("Error adding to cart:", error);
                        });
                });
            });
        });
    </script>
</head>
<body>
    <h1>Choose Wisely!</h1>

    <div class="menu-container">
        <?php foreach ($categories as $category): ?>
            <div class="category">
                <div class="category-header"><?php echo htmlspecialchars($category['category_name']); ?></div>
                <div class="menu-items">
                    <?php
                    foreach ($products as $product):
                        if ($product['category_name'] == $category['category_name']):
                    ?>
                        <div class="menu-item">
                            <img src="uploads/<?php echo basename($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <div class="item-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="item-description"><?php echo htmlspecialchars($product['description']); ?></div>
                            <div class="item-price">Rs. <?php echo htmlspecialchars($product['price']); ?></div>
                            <form method="POST" action="menu.php">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit">Add to Cart</button>
                            </form>
                        </div>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cart Icon -->
    <a href="cart.php" class="cart-icon">
        <img src="cart.png" alt="Cart Icon">
        <div class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></div>
    </a>
</body>
</html>
