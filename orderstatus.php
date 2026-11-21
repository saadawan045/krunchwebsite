<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=krunchwebsite", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if order ID is passed via GET
if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']); // Sanitize input

    // Fetch order details along with customer name
    try {
        $stmt = $pdo->prepare("SELECT y.*, u.first_name, u.last_name FROM yes y
                               JOIN users u ON y.customer_id = u.id
                               WHERE y.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            die("<p>Order not found. Please check the order ID.</p>");
        }
    } catch (PDOException $e) {
        die("Error fetching data: " . $e->getMessage());
    }

    // Fetch order items
    try {
        $stmt = $pdo->prepare("SELECT oi.*, p.name, p.price FROM order_items oi
                               JOIN products p ON oi.product_id = p.id
                               WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching order items: " . $e->getMessage());
    }
} else {
    die("<p>No order ID provided. Please include an order ID in the URL.</p>");
}

// Map statuses to animations and messages
$animations = [
    'pending' => ['class' => 'status-pending', 'message' => 'Your order is pending.'],
    'processing' => ['class' => 'status-processing', 'message' => 'We’re preparing your order!'],
    'completed' => ['class' => 'status-completed', 'message' => 'Your order is complete. Enjoy!'],
    'cancelled' => ['class' => 'status-cancelled', 'message' => 'Your order has been cancelled.'],
];

// Determine the appropriate animation and message
$orderStatus = strtolower($order['order_status']); // Ensure case-insensitivity
$animation = $animations[$orderStatus] ?? ['class' => 'status-unknown', 'message' => 'Order status unknown.'];

// Calculate total amount
$totalAmount = 0;
foreach ($orderItems as $item) {
    $totalAmount += $item['quantity'] * $item['price'];
}

// Capitalize "JazzCash" for display
$paymentMethod = ucfirst(strtolower($order['payment_method'])); // Capitalize the first letters of JazzCash
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

        /* General styles */
        body {
            font-family: 'Bangers', cursive;
            background-color: #000000; /* Black background */
            color: #ffcc00; /* Gold text */
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #333; /* Dark background for the container */
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 2px solid #ffcc00; /* Gold border */
        }

        h1 {
            font-family: 'Bangers', cursive;
            font-size: 2.2rem;
            margin-bottom: 10px;
            color: #ffcc00; /* Gold for header */
        }

        .status-box {
            margin: 20px 0;
            padding: 20px;
            border-radius: 12px;
            color: #ffcc00; /* Gold text in status box */
            font-size: 1.2rem;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }

        /* Status Animations */
        @keyframes gradient-slide {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes status-pop {
            0% {
                transform: scale(0.9);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .status-box {
            animation: status-pop 0.5s ease-out;
        }

        .status-pending {
            background: linear-gradient(135deg, #ff9800, #ff5722);
            background-size: 300% 300%;
            animation: gradient-slide 5s ease infinite, status-pop 0.5s ease-out;
        }

        .status-processing {
            background: linear-gradient(135deg, #00bcd4, #3f51b5);
            background-size: 300% 300%;
            animation: gradient-slide 5s ease infinite, status-pop 0.5s ease-out;
        }

        .status-completed {
            background: linear-gradient(135deg, #4caf50, #8bc34a);
            background-size: 300% 300%;
            animation: gradient-slide 5s ease infinite, status-pop 0.5s ease-out;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #f44336, #e91e63);
            background-size: 300% 300%;
            animation: gradient-slide 5s ease infinite, status-pop 0.5s ease-out;
        }

        .status-unknown {
            background: linear-gradient(135deg, #9e9e9e, #607d8b);
            background-size: 300% 300%;
            animation: gradient-slide 5s ease infinite, status-pop 0.5s ease-out;
        }

        .details {
            margin: 20px 0;
            text-align: left;
        }

        .details p {
            margin: 10px 0;
            font-size: 1rem;
            color: #ffcc00; /* Gold text for details */
        }

        .footer-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #ffcc00; /* Gold for footer link */
            color: black;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            border: 2px solid #000; /* Black border */
        }

        .footer-link:hover {
            background: #ffd700; /* Lighter gold on hover */
        }

        .order-items {
            margin: 20px 0;
            text-align: left;
        }

        .order-items table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .order-items table, .order-items th, .order-items td {
            border: 1px solid #ddd;
        }

        .order-items th, .order-items td {
            padding: 10px;
            text-align: left;
        }

        .order-items th {
            background-color: #f4f4f4;
        }

        .order-items .total {
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Centered QR Code Text */
        .qr-code-text {
            font-size: 1.4rem;
            font-weight: bold;
            color: #ffcc00; /* Gold for QR text */
            margin-top: 20px;
            text-align: center;
        }

        /* Larger QR Code Image */
        .qr-code-container {
            margin-top: 20px;
            text-align: center;
        }

        .qr-code-container img {
            width: 300px; /* Increased size */
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order Status</h1>
        <div class="status-box <?= htmlspecialchars($animation['class']) ?>">
            <?= htmlspecialchars($animation['message']) ?>
        </div>
        <div class="details">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
            <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
            <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order['delivery_address'] ?? '') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
            <p><strong>Special Instructions:</strong> <?= htmlspecialchars($order['special_instructions'] ?? '') ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($paymentMethod) ?></p>
        </div>
        <div class="order-items">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>$<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2" style="text-align:right; font-weight:bold;">Total Amount:</td>
                        <td>$<?= htmlspecialchars(number_format($totalAmount, 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if (file_exists(__DIR__ . '/qr.png')): ?>
        <div class="qr-code-container">
            <div class="qr-code-text">Scan QR code to confirm payment</div>
            <img src="qr.png" alt="QR Code">
        </div>
        <?php endif; ?>
        <a href="krunchwebsite.php" class="footer-link">Return to Homepage</a>
    </div>
</body>
</html>
