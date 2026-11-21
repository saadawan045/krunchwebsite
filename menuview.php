<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "krunchwebsite";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Fetch categories from the categories table
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Fetch products and join with categories
try {
    $stmt = $pdo->query("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY c.category_name, p.name");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching menu: " . $e->getMessage());
}

// Organize products by categories
$categorizedProducts = [];
foreach ($products as $product) {
    $categorizedProducts[$product['category_name']][] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu View</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bangers', cursive;
            background-color: #000000;
            color: #ffcc00;
            margin: 0;
            padding: 0;
        }
        /* Adjust container for navbar space */
        .container {
            max-width: 1200px;
            margin: 100px auto; /* Adjusted for navbar space */
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
            color: #ffcc00;
        }
        .menu-category {
            margin-bottom: 30px;
        }
        .menu-category h2 {
            border-bottom: 2px solid #ffcc00;
            padding-bottom: 5px;
        }
        .menu-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border: 1px solid #ffcc00;
            border-radius: 5px;
            padding: 10px;
            background-color: #1a1a1a;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
        }
        .menu-item img {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            margin-right: 20px;
            object-fit: cover;
        }
        .menu-item-details {
            flex-grow: 1;
        }
        .menu-item-details h3 {
            margin: 0;
            color: #ffcc00;
        }
        .menu-item-details p {
            margin: 5px 0;
            color: #ccc;
        }
        .menu-item-details .price {
            font-weight: bold;
            color: #ffcc00;
        }
    </style>
</head>
<body>

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <div class="container">
        <h1>Browse Our Menu</h1>
        <?php if (!empty($categorizedProducts)): ?>
            <?php foreach ($categorizedProducts as $categoryName => $productsInCategory): ?>
                <div class="menu-category">
                    <h2><?= htmlspecialchars($categoryName) ?></h2>
                    <?php foreach ($productsInCategory as $product): ?>
                        <div class="menu-item">
                            <img src="uploads/<?php echo basename($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <div class="menu-item-details">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p><?= htmlspecialchars($product['description']) ?></p>
                                <p class="price">Price: Rs.<?= number_format($product['price'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No menu items available at the moment.</p>
        <?php endif; ?>
    </div>

</body>
</html>
