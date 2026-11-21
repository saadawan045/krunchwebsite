<?php
session_start();

// Database connection (replace with your actual database credentials)
$host = 'localhost'; 
$db = 'krunchwebsite'; 
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4';

// Create PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Get the order ID and customer ID from the session or URL
$orderId = $_GET['order_id'] ?? null;
$customerId = $_SESSION['user_id'] ?? null;

if (!$orderId || !$customerId) {
    // Redirect or show an error if order or customer ID is not available
    echo "Invalid request. Order ID or Customer ID missing.";
    exit;
}

// Fetch order details from the database
$stmt = $pdo->prepare("SELECT * FROM yes WHERE id = ? AND customer_id = ?");
$stmt->execute([$orderId, $customerId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    // Handle case when order is not found
    echo "Order not found.";
    exit;
}

// Handle form submission and update order if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $deliveryAddress = $_POST['delivery_address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $specialInstructions = $_POST['special_instructions'] ?? '';
    $latitude = $_POST['latitude'] ?? 31.5497;  // Default to Lahore coordinates
    $longitude = $_POST['longitude'] ?? 74.3436;  // Default to Lahore coordinates
    $paymentMethod = $_POST['payment_method'] ?? '';

    // Validate phone number format (e.g., XXX-XXX-XXXX)
    if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $phone)) {
        echo "Invalid phone number format. Use XXX-XXX-XXXX.";
        exit;
    } else {
        // Update order details in the database
        $stmt = $pdo->prepare("UPDATE yes SET delivery_address = ?, phone = ?, special_instructions = ?, latitude = ?, longitude = ?, payment_method = ? WHERE id = ?");
        if ($stmt->execute([$deliveryAddress, $phone, $specialInstructions, $latitude, $longitude, $paymentMethod, $orderId])) {
            echo "Order updated successfully!";
        } else {
            echo "Error updating order.";
        }

        // Clear cart session after order update
        unset($_SESSION['cart']);  // Unset the cart session
        unset($_SESSION['order_id']); // Clear the order ID for future orders

        // Redirect to order status page after update
        header('Location: orderstatus.php?order_id=' . $orderId); // Redirect to orderstatus.php with the order ID
        exit; // Ensure no further code is executed
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css"> <!-- Link your CSS file here -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        /* Custom Krunch Theme Styling */
        body {
            font-family: 'Bangers', cursive;
            margin: 0;
            padding: 0;
            background-color: #000; /* Black background */
            color: #FFD700; /* Gold text */
        }
        h1 {
            text-align: center;
            color: #FFD700; /* Gold */
            margin: 20px 0;
            font-size: 3rem;
            letter-spacing: 1px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .checkout-container {
            background: #222; /* Dark background for checkout section */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border: 2px solid #FFD700; /* Gold border */
        }
        .form-section {
            margin-bottom: 30px;
        }
        label {
            font-weight: 600;
            margin: 10px 0;
            display: block;
            color: #FFD700; /* Gold text */
            font-size: 1rem;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #444; /* Dark background for inputs */
            color: #FFD700; /* Gold text */
        }
        input:focus, textarea:focus, select:focus {
            border-color: #007bff; /* Blue */
            outline: none;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3); /* Light blue shadow */
        }
        button {
            display: block;
            width: 100%;
            background: #007bff; /* Blue background */
            color: #fff;
            padding: 12px 0;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-family: 'Bangers', cursive;
            margin-top: 20px; /* Ensure space above the button */
        }
        button:hover {
            background: #0056b3; /* Darker blue */
            transform: translateY(-2px);
        }
        button:active {
            background: #003366; /* Even darker blue */
        }
        .map-section {
            margin-top: 30px;
        }
        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .note {
            font-size: 0.9rem;
            color: #FFD700; /* Gold note */
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkout-container">
            <h1>Checkout</h1>
            <form method="POST" action="">

                <!-- Map Section for selecting delivery coordinates -->
                <div class="map-section">
                    <div id="map"></div>
                </div>

                <!-- Customer Information Section -->
                <div class="form-section">
                    <label for="delivery_address">Delivery Address (Type to search):</label>
                    <input type="text" name="delivery_address" id="delivery_address" value="<?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?>" required placeholder="Type address or search location" />

                    <div class="note">
                        <em>Start typing your delivery address to search. The map marker will move to your selected location.</em>
                    </div>

                    <label for="phone">Phone:</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($order['phone'] ?? ''); ?>" pattern="\d{3}-\d{3}-\d{4}" title="Phone number format: XXX-XXX-XXXX" required />

                    <label for="special_instructions">Special Instructions:</label>
                    <textarea name="special_instructions"><?php echo htmlspecialchars($order['special_instructions'] ?? ''); ?></textarea>

                    <label for="payment_method">Payment Method:</label>
<select name="payment_method" required>
    <option value="jazzcash">JazzCash</option>
    <option value="cash_on_delivery">Cash on Delivery</option>
</select>

                </div>

                <!-- Hidden fields for latitude and longitude -->
                <input type="hidden" name="latitude" id="latitude" value="<?php echo htmlspecialchars($order['latitude'] ?? '31.5497'); ?>">
                <input type="hidden" name="longitude" id="longitude" value="<?php echo htmlspecialchars($order['longitude'] ?? '74.3436'); ?>">

                <!-- Submit Button -->
                <button type="submit">Confirm Order</button>
            </form>
        </div>
    </div>

    <script>
        // Initialize Leaflet map
        const map = L.map('map').setView([<?php echo $order['latitude'] ?? '31.5497'; ?>, <?php echo $order['longitude'] ?? '74.3436'; ?>], 13);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add draggable marker
        const marker = L.marker([<?php echo $order['latitude'] ?? '31.5497'; ?>, <?php echo $order['longitude'] ?? '74.3436'; ?>], { draggable: true }).addTo(map);

        // Update latitude and longitude when the marker is moved
        marker.on('dragend', function() {
            const latlng = marker.getLatLng();
            document.getElementById('latitude').value = latlng.lat;
            document.getElementById('longitude').value = latlng.lng;
        });

        // Use Nominatim API to convert address to coordinates when address is typed
        const addressInput = document.getElementById('delivery_address');
        addressInput.addEventListener('input', function() {
            const address = addressInput.value;
            if (address.length > 3) { // Trigger geocoding after typing a few characters
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${address}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            const lat = data[0].lat;
                            const lon = data[0].lon;

                            // Move marker to the new coordinates
                            marker.setLatLng([lat, lon]);
                            map.setView([lat, lon], 13);

                            // Update latitude and longitude hidden inputs
                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lon;
                        }
                    })
                    .catch(error => console.error('Error fetching geocoding data:', error));
            }
        });
    </script>
</body>
</html>
