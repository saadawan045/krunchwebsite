<?php
session_start(); // Start session
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable error reporting for debugging

$orderConfirmed = false;
$orderData = [];
$cartData = [];
$errorMessage = '';

// Set default values for latitude and longitude
$latitude = null;
$longitude = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "krunchwebsite";

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validate and sanitize user input
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $delivery_address = filter_input(INPUT_POST, 'delivery_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $billing_address = filter_input(INPUT_POST, 'billing_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $special_instructions = filter_input(INPUT_POST, 'special_instructions', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Get cart data (sent as JSON in POST request)
        $cartData = isset($_POST['cart_data']) ? json_decode($_POST['cart_data'], true) : [];

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($delivery_address) || empty($billing_address) || empty($phone)) {
            $errorMessage = "Please fill in all required fields.";
        } elseif (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
            $errorMessage = "Phone number format is invalid. Use XXX-XXX-XXXX.";
        } elseif (empty($cartData)) {
            $errorMessage = "Your cart is empty. Please add items to your cart before confirming the order.";
        } else {
            // Prepare and execute the SQL statement to save the order data into the yes table
            $stmt = $conn->prepare("INSERT INTO yes (first_name, last_name, delivery_address, billing_address, special_instructions, phone, latitude, longitude, order_date) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$first_name, $last_name, $delivery_address, $billing_address, $special_instructions, $phone, $latitude, $longitude]);
            $orderId = $conn->lastInsertId(); // Get the last inserted order ID

            // Optionally, save cart data (ordered items) to 'ordered_items' in the yes table
            $orderedItems = json_encode($cartData);
            $stmt = $conn->prepare("UPDATE yes SET ordered_items = ? WHERE id = ?");
            $stmt->execute([$orderedItems, $orderId]);

            $orderConfirmed = true;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $errorMessage = "An error occurred while processing your order. Please try again later.";
    }
    $_SESSION['cart'] = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .confirmation-container {
            margin: 30px;
        }
        .order-details {
            margin-bottom: 20px;
        }
        .coordinates {
            margin-top: 20px;
        }
        #map {
            height: 400px;
            margin-top: 20px;
        }
        .checkmark-container {
            margin-top: 20px;
        }
        .checkmark {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: green;
            position: relative;
        }
        .checkmark::before {
            content: '✓';
            font-size: 30px;
            position: absolute;
            top: 8px;
            left: 13px;
            color: white;
        }
    </style>
</head>
<body>

<h1>Order Confirmation</h1>
<div class="confirmation-container">
    <?php if ($errorMessage): ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php elseif ($orderConfirmed): ?>
        <div class="order-details">
            <p>Your order has been successfully placed!</p>
            <p>Thank you, <?= htmlspecialchars($first_name) ?>! Your order details are as follows:</p>
            <p><strong>Delivery Address:</strong> <?= htmlspecialchars($delivery_address) ?></p>
            <p><strong>Billing Address:</strong> <?= htmlspecialchars($billing_address) ?></p>
            <p><strong>Special Instructions:</strong> <?= htmlspecialchars($special_instructions) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>

            <!-- Fetch and display ordered items -->
            <h3>Your Cart:</h3>
            <ul>
                <?php
                // Fetch ordered items from the database
                $stmt = $conn->prepare("SELECT ordered_items FROM yes WHERE id = ?");
                $stmt->execute([$orderId]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                $orderedItems = json_decode($order['ordered_items'], true);
                if ($orderedItems): 
                    foreach ($orderedItems as $item): ?>
                        <li>
                            Product: <?= htmlspecialchars($item['product_name']) ?>, Quantity: <?= htmlspecialchars($item['quantity']) ?>
                        </li>
                    <?php endforeach; 
                else: ?>
                    <li>No items found in the order.</li>
                <?php endif; ?>
            </ul>

            <!-- Checkmark Animation Container -->
            <div class="checkmark-container" id="checkmarkContainer">
               <div class="checkmark"></div>
           </div>
       </div>
   <?php else: ?>
       <p>Please enter your details:</p>
       <form action="" method="POST">
           <!-- Form inputs here -->
           <input type="text" id="first_name" name="first_name" placeholder="Your First Name" required>
           <input type="text" id="last_name" name="last_name" placeholder="Your Last Name" required>
           <input type="text" id="delivery_address" name="delivery_address" placeholder="Delivery Address" required>
           <label>
               <input type="checkbox" id="same_as_delivery"> Billing address same as delivery address
           </label>
           <input type="text" id="billing_address" name="billing_address" placeholder="Billing Address" required>
           <input type="text" id="special_instructions" name="special_instructions" placeholder="Special Instructions (Optional)">
           <input type="tel" id="phone" name="phone" placeholder="123-456-7890" pattern="\d{3}-\d{3}-\d{4}" required>
           <input type="hidden" id="latitude" name="latitude" value="">
           <input type="hidden" id="longitude" name="longitude" value="">
           <input type="hidden" name="cart_data" value="<?= htmlspecialchars(json_encode($cartData)) ?>">
           <button type="submit">Confirm Order</button>
       </form>
   <?php endif; ?>

   <!-- Coordinates display -->
   <div class="coordinates">
       Latitude: <span id="latValue"><?= $latitude ?: 'N/A' ?></span><br>
       Longitude: <span id="lngValue"><?= $longitude ?: 'N/A' ?></span>
   </div>

   <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([<?php echo $latitude ?: '51.505'; ?>, <?php echo $longitude ?: '-0.09'; ?>], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker = L.marker([<?php echo $latitude ?: '51.505'; ?>, <?php echo $longitude ?: '-0.09'; ?>], { draggable: true }).addTo(map)
        .bindPopup('Your Location')
        .openPopup();

    marker.on('dragend', function(event) {
        var latLng = event.target.getLatLng();
        document.getElementById('latitude').value = latLng.lat;
        document.getElementById('longitude').value = latLng.lng;
        document.getElementById('latValue').textContent = latLng.lat;
        document.getElementById('lngValue').textContent = latLng.lng;
    });

    document.getElementById('same_as_delivery').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('billing_address').value = document.getElementById('delivery_address').value;
            document.getElementById('billing_address').disabled = true;
        } else {
            document.getElementById('billing_address').disabled = false;
        }
    });
</script>

</body>
</html>
