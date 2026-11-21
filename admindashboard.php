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
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Orders per month
    $stmt = $conn->prepare("SELECT MONTH(order_date) AS month, COUNT(*) AS total_orders 
                            FROM yes 
                            GROUP BY MONTH(order_date) 
                            ORDER BY MONTH(order_date)");
    $stmt->execute();
    $orders_per_month = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $months = [];
    $order_counts = [];
    foreach ($orders_per_month as $data) {
        $months[] = date('F', mktime(0, 0, 0, $data['month'], 10)); // Get month name
        $order_counts[] = $data['total_orders'];
    }

    // Sales per month
    $stmt = $conn->prepare("SELECT MONTH(order_date) AS month, SUM(price) AS total_sales 
                            FROM order_items 
                            JOIN yes ON order_items.order_id = yes.id 
                            GROUP BY MONTH(order_date) 
                            ORDER BY MONTH(order_date)");
    $stmt->execute();
    $sales_per_month = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sales_counts = [];
    foreach ($sales_per_month as $data) {
        $sales_counts[] = $data['total_sales'];
    }

    // Number of times each product was ordered this month
    $stmt = $conn->prepare("SELECT products.name, COUNT(order_items.product_id) AS product_count
                            FROM order_items 
                            JOIN products ON order_items.product_id = products.id
                            JOIN yes ON order_items.order_id = yes.id
                            WHERE MONTH(order_date) = MONTH(CURDATE())
                            GROUP BY products.name
                            ORDER BY products.name");
    $stmt->execute();
    $products_this_month = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $product_names_this_month = [];
    $product_counts_this_month = [];
    foreach ($products_this_month as $data) {
        $product_names_this_month[] = $data['name'];
        $product_counts_this_month[] = $data['product_count'];
    }

    // Orders per day in the week
    $stmt = $conn->prepare("SELECT DAYNAME(order_date) AS day, COUNT(*) AS total_orders 
                            FROM yes 
                            WHERE WEEK(order_date) = WEEK(CURDATE()) 
                            GROUP BY DAYOFWEEK(order_date) 
                            ORDER BY DAYOFWEEK(order_date)");
    $stmt->execute();
    $orders_per_week = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $days_of_week = [];
    $weekly_order_counts = [];
    foreach ($orders_per_week as $data) {
        $days_of_week[] = $data['day'];
        $weekly_order_counts[] = $data['total_orders'];
    }

    // Sales per day in the week
    $stmt = $conn->prepare("SELECT DAYNAME(order_date) AS day, SUM(price) AS total_sales 
                            FROM order_items 
                            JOIN yes ON order_items.order_id = yes.id 
                            WHERE WEEK(order_date) = WEEK(CURDATE()) 
                            GROUP BY DAYOFWEEK(order_date) 
                            ORDER BY DAYOFWEEK(order_date)");
    $stmt->execute();
    $sales_per_week = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $weekly_sales_counts = [];
    foreach ($sales_per_week as $data) {
        $weekly_sales_counts[] = $data['total_sales'];
    }

    // Number of times each product was ordered in the week
    $stmt = $conn->prepare("SELECT products.name, COUNT(order_items.product_id) AS product_count
                            FROM order_items 
                            JOIN products ON order_items.product_id = products.id
                            JOIN yes ON order_items.order_id = yes.id
                            WHERE WEEK(order_date) = WEEK(CURDATE()) 
                            GROUP BY products.name
                            ORDER BY products.name");
    $stmt->execute();
    $products_per_week = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $weekly_product_names = [];
    $weekly_product_counts = [];
    foreach ($products_per_week as $data) {
        $weekly_product_names[] = $data['name'];
        $weekly_product_counts[] = $data['product_count'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bangers', Arial, sans-serif;
            background-color: black; /* Dark gray background */
            color: #FFD700; /* Gold text */
            margin: 0;
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            min-height: 100vh; /* Full viewport height */
        }

        /* Main Wrapper to Center Content */
        .main-wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 1400px; /* Limit maximum width */
            background-color: #202020; /* Darker background for contrast */
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            font-family: 'Bangers', Arial, sans-serif;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: #202020; /* Darker gray for sidebar */
            color: #FFF; /* White text */
            height: 100vh;
            position: fixed;
            top: 0;
            left: -250px; /* Hide sidebar by default */
            transition: left 0.3s ease;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
        }

        .sidebar a {
            display: block;
            padding: 10px 20px;
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }

        .sidebar a:hover {
            background-color: #333;
            color: #FFF;
        }

        .main-content {
            margin-left: 0; /* No sidebar by default */
            padding: 20px;
            width: 100%;
        }


        /* Header Styling */
        h1 {
            color: #FFD700; /* Gold color for heading */
            font-weight: 300;
            text-align: center; /* Center the header */
            margin-bottom: 30px;
            font-family: 'Bangers', Arial, sans-serif;
        }

        /* Chart Container Styling */
        .chart-container {
            width: 100%;
            max-width: 1200px; /* Increase max width */
            margin: 40px auto; /* Center charts and add spacing */
            background-color: #202020; /* Dark background for chart container */
            border-radius: 8px;
            padding: 0px; /* Increase padding */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            font-family: 'Bangers', Arial, sans-serif;
        }

        canvas {
            display: block;
            margin: 0 auto;
            width: 100%; /* Ensure the graph takes full container width */
            max-width: 1000px; /* Set maximum graph width */
            height: 500px; /* Increase graph height */
            font-family: 'Bangers', Arial, sans-serif;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .chart-container {
                padding: 20px;
            }

            canvas {
                height: 400px; /* Adjust height for smaller screens */
            }
        }
    </style>
<body>
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        

        <h1>Admin Dashboard</h1>

        <!-- Charts for Orders, Sales, and Products -->
        <div class="chart-container">
            <canvas id="ordersChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="productChart"></canvas>
        </div>

        <!-- Orders per Day in the Week -->
        <div class="chart-container">
            <canvas id="weeklyOrdersChart"></canvas>
        </div>

        <!-- Sales per Day in the Week -->
        <div class="chart-container">
            <canvas id="weeklySalesChart"></canvas>
        </div>

        <!-- Product Orders per Week -->
        <div class="chart-container">
            <canvas id="weeklyProductChart"></canvas>
        </div>
    </div>

    <script>
        // Function to toggle the sidebar visibility
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isVisible = sidebar.style.left === '0px';

            if (isVisible) {
                sidebar.style.left = '-250px'; // Hide sidebar
            } else {
                sidebar.style.left = '0'; // Show sidebar
            }
        }

        // Chart.js initialization code (for all charts)
        const ctxOrders = document.getElementById('ordersChart').getContext('2d');
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        const ctxProduct = document.getElementById('productChart').getContext('2d');
        const ctxWeeklyOrders = document.getElementById('weeklyOrdersChart').getContext('2d');
        const ctxWeeklySales = document.getElementById('weeklySalesChart').getContext('2d');
        const ctxWeeklyProduct = document.getElementById('weeklyProductChart').getContext('2d');

        const ordersChart = new Chart(ctxOrders, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Orders per Month',
                    data: <?php echo json_encode($order_counts); ?>,
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true
            }
        });

        const salesChart = new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Sales per Month',
                    data: <?php echo json_encode($sales_counts); ?>,
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true
            }
        });

        const productChart = new Chart(ctxProduct, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($product_names_this_month); ?>,
                datasets: [{
                    label: 'Product Orders per Month',
                    data: <?php echo json_encode($product_counts_this_month); ?>,
                    backgroundColor: '#FFD700',
                    borderColor: '#FFD700',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        const weeklyOrdersChart = new Chart(ctxWeeklyOrders, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($days_of_week); ?>,
                datasets: [{
                    label: 'Orders per Day (This Week)',
                    data: <?php echo json_encode($weekly_order_counts); ?>,
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true
            }
        });

        const weeklySalesChart = new Chart(ctxWeeklySales, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($days_of_week); ?>,
                datasets: [{
                    label: 'Sales per Day (This Week)',
                    data: <?php echo json_encode($weekly_sales_counts); ?>,
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true
            }
        });

        const weeklyProductChart = new Chart(ctxWeeklyProduct, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($weekly_product_names); ?>,
                datasets: [{
                    label: 'Product Orders (This Week)',
                    data: <?php echo json_encode($weekly_product_counts); ?>,
                    backgroundColor: '#FFD700',
                    borderColor: '#FFD700',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
