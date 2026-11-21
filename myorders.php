<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to verify if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

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

$customer_id = $_SESSION['user_id']; // Logged-in user's ID

// Delete orders with unspecified payment methods
try {
    $stmt = $pdo->prepare("DELETE FROM yes WHERE payment_method IS NULL OR payment_method = ''");
    $stmt->execute();
} catch (PDOException $e) {
    die("Error deleting orders with unspecified payment methods: " . $e->getMessage());
}

// Fetch the user's previous orders
try {
    $stmt = $pdo->prepare("SELECT * FROM yes WHERE customer_id = :customer_id ORDER BY order_date DESC");
    $stmt->execute(['customer_id' => $customer_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}

// Handle the creation of a new order
if (isset($_POST['place_new_order'])) {
    try {
        // Clear session order data
        unset($_SESSION['order_id']);
        unset($_SESSION['cart']);

        // Insert a new order
        $stmt = $pdo->prepare("INSERT INTO yes (customer_id, order_date, order_status) VALUES (:customer_id, NOW(), 'pending')");
        $stmt->execute(['customer_id' => $customer_id]);
        $newOrderId = $pdo->lastInsertId();

        // Store the new order_id in the session
        $_SESSION['order_id'] = $newOrderId;

        // Redirect to the menu page with the new order_id in the URL
        header("Location: menu.php?order_id=" . $newOrderId);
        exit();
    } catch (PDOException $e) {
        die("Error creating new order: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bangers', cursive;
            background-color: #000000;
            color: #ffcc00;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            font-family: 'Bangers', cursive;
            margin: 100px auto;
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
        }
        h1 {
            text-align: center;
            color: #ffcc00;
        }
        .order {
            border-bottom: 1px solid #444;
            padding: 10px 0;
        }
        .order:last-child {
            border-bottom: none;
        }
        .order-details strong {
            color: #ffcc00;
        }
        .order-items {
            margin: 10px 0;
            color: #ffcc00;
        }
        .btn {
            display: inline-block;
            background-color: #444;
            color: #ffcc00;
            text-decoration: none;
            padding: 8px 12px;
            border: 1px solid #ffcc00;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Bangers', cursive;
        }
        .btn:hover {
            background-color: #ffcc00;
            color: #000000;
        }
        .new-order-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #444;
            color: #ffcc00;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #ffcc00;
            font-size: 16px;
            margin-bottom: 20px;
            cursor: pointer;
            font-family: 'Bangers', cursive;
        }
        .new-order-btn:hover {
            background-color: #ffcc00;
            color: #000000;
        }
        p {
            text-align: center;
            color: #ffcc00;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <form method="POST">
            <button type="submit" name="place_new_order" class="new-order-btn">Place a New Order</button>
        </form>

        <h1>My Orders</h1>
        <?php if ($orders): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order">
                    <div class="order-details">
                        <strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?><br>
                        <strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?><br>
                    </div>
                    <div class="order-items">
                        <strong>Items:</strong><br>
                        <?php
                        try {
                            $stmt = $pdo->prepare("SELECT p.name, oi.quantity 
                                                   FROM order_items oi
                                                   JOIN products p ON oi.product_id = p.id
                                                   WHERE oi.order_id = :order_id");
                            $stmt->execute(['order_id' => $order['id']]);
                            $items = $stmt->fetchAll();
                        } catch (PDOException $e) {
                            die("Error fetching order items: " . $e->getMessage());
                        }

                        if ($items) {
                            foreach ($items as $item) {
                                echo htmlspecialchars($item['name']) . " x" . htmlspecialchars($item['quantity']) . "<br>";
                            }
                        } else {
                            echo "No items found for this order.";
                        }
                        ?>
                    </div>
                    <form method="get" action="orderstatus.php">
                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                        <button type="submit" class="btn">View Status</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No previous orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
