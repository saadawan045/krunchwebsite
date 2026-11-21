<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "krunchwebsite";

try {
    // Database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all orders from the 'yes' table including latitude and longitude
    $stmt = $conn->prepare("SELECT yes.id, yes.customer_id, yes.delivery_address, yes.billing_address, yes.phone, yes.order_date, yes.latitude, yes.longitude, yes.order_status, users.first_name, users.last_name FROM yes INNER JOIN users ON yes.customer_id = users.id ORDER BY yes.order_date DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order items and total price for each order, including price
    $order_items_stmt = $conn->prepare("SELECT oi.order_id, p.name, oi.quantity, p.price FROM order_items oi INNER JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");

    // Handle order status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['order_status'])) {
        $order_id = $_POST['order_id'];
        $order_status = $_POST['order_status'];

        // Update order status in the database
        $updateStmt = $conn->prepare("UPDATE yes SET order_status = :order_status WHERE id = :order_id");
        $updateStmt->bindParam(':order_status', $order_status);
        $updateStmt->bindParam(':order_id', $order_id);
        $updateStmt->execute();

        // Redirect to refresh the page
        header("Location: orders.php");
        exit();
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
    /* Enhanced Krunch Styling */

    body {
        font-family: 'Bangers', sans-serif;
        background-color: black; /* Black background */
        color: gold; /* Gold text */
        margin: 0;
        padding: 0;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 50px auto;
        padding: 20px;
        background-color: #1a1a1a; /* Dark background for contrast */
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    }

    h1 {
        color: #ffcc00; /* Krunch Yellow */
        text-align: center;
        font-size: 2.5em;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #292929; /* Slightly lighter dark tone */
        color: #fff; /* White text for better readability */
        border-radius: 10px;
        overflow: hidden; /* Ensure rounded corners */
    }

    th, td {
        padding: 15px;
        text-align: left;
    }

    th {
        background-color: #333; /* Darker background for table headers */
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #444;
    }

    td {
        border-bottom: 1px solid #444;
    }

    tr:hover td {
        background-color: #444; /* Highlight row on hover */
    }

    .table-container {
        margin-top: 20px;
        overflow-x: auto; /* Ensure horizontal scroll on smaller screens */
    }

    .map-container {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8); /* Darker overlay */
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .map-modal {
        background-color: #222; /* Slightly lighter dark tone */
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 900px;
        color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
    }

    .map {
        height: 500px;
        width: 100%;
        border-radius: 10px;
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 24px;
        color: #ffcc00; /* Gold color for the X button */
        cursor: pointer;
        background-color: transparent;
        border: none;
        transition: transform 0.2s;
    }

    .close-btn:hover {
        transform: scale(1.2); /* Slightly enlarge on hover */
    }

    .toggle-map {
        cursor: pointer;
        padding: 10px 20px;
        background-color: #ffcc00; /* Krunch Yellow */
        color: black;
        border: none;
        border-radius: 5px;
        font-size: 1em;
        transition: background-color 0.3s, transform 0.2s;
    }

    .toggle-map:hover {
        background-color: #ffd633; /* Lighter yellow on hover */
        transform: scale(1.05); /* Slight zoom on hover */
    }

    .order-status {
        display: flex;
        justify-content: center;
        margin-top: 10px;
    }

    select {
        padding: 8px 12px;
        font-size: 1em;
        border-radius: 5px;
        border: 1px solid #ddd;
        background-color: #292929; /* Match table background */
        color: #fff;
    }

    select:hover {
        border-color: #ffcc00; /* Highlight on hover */
    }
</style>

</head>

<body>

<!-- Include Sidebar -->
<?php include('sidebar.php'); ?>

<div class="container">
    <h1>Orders</h1>
    
    <?php if (count($orders) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Name</th>
                        <th>Delivery Address</th>
                        <th>Billing Address</th>
                        <th>Phone</th>
                        <th>Order Date</th>
                        <th>Order Status</th>
                        <th>Items Ordered</th>
                        <th>Total Price (PKR)</th>
                        <th>Map</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                            <td><?= htmlspecialchars($order['delivery_address']) ?></td>
                            <td><?= htmlspecialchars($order['billing_address']) ?></td>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td>
                                <form action="orders.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                    <select name="order_status" onchange="this.form.submit()">
                                        <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="completed" <?= $order['order_status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php
                                $order_items_stmt->bindParam(':order_id', $order['id']);
                                $order_items_stmt->execute();
                                $items = $order_items_stmt->fetchAll(PDO::FETCH_ASSOC);
                                $total_price = 0;
                                ?>
                                <ul>
                                    <?php foreach ($items as $item): ?>
                                        <li><?= htmlspecialchars($item['name']) ?> - Quantity: <?= $item['quantity'] ?></li>
                                        <?php $total_price += $item['quantity'] * $item['price']; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><?= number_format($total_price, 2) ?></td>
                            <td>
                                <button class="toggle-map" onclick="toggleMap(<?= $order['id'] ?>, <?= $order['latitude'] ?>, <?= $order['longitude'] ?>)">View Map</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

<!-- Map Modals -->
<?php foreach ($orders as $order): ?>
    <div id="map-container-<?= $order['id'] ?>" class="map-container">
        <div class="map-modal">
            <button class="close-btn" onclick="closeMap(<?= $order['id'] ?>)">X</button>
            <div id="map-<?= $order['id'] ?>" class="map"></div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    function toggleMap(orderId, latitude, longitude) {
        console.log("Opening map for order " + orderId);
        var container = document.getElementById('map-container-' + orderId);
        container.style.display = 'flex';

        // Initialize the map for the given order
        var map = L.map('map-' + orderId).setView([latitude, longitude], 13);
        
        console.log("Map initialized for order " + orderId);

        // Add CartoDB Positron tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://carto.com/attributions">CartoDB</a>'
        }).addTo(map);

        // Add marker for delivery location
        L.marker([latitude, longitude]).addTo(map)
            .bindPopup("<b>Delivery Location</b>").openPopup();
    }

    function closeMap(orderId) {
        var container = document.getElementById('map-container-' + orderId);
        container.style.display = 'none';
    }
</script>

</body>
</html>
