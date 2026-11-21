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

    // Fetch all reviews
    $stmt = $conn->prepare("SELECT reviews.id, users.first_name, users.last_name, reviews.review_text, reviews.rating, reviews.created_at
                            FROM reviews
                            JOIN users ON reviews.customer_id = users.id
                            ORDER BY reviews.created_at DESC");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete review
    if (isset($_POST['delete_review'])) {
        $review_id = $_POST['delete_review'];
        $delete_stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $delete_stmt->execute([$review_id]);
        header("Location: review_moderation.php");
        exit();
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
    <title>Review Moderation</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

body {
    margin: 0;
    font-family: 'Bangers', sans-serif;
    background-color: black; /* Dark background */
    color: #ffd700; /* Gold text */
    display: flex;
    justify-content: center;
    align-items: flex-start;
    height: 100vh;
    padding: 20px;
    box-sizing: border-box;
}

.main-content {
    padding: 30px;
    width: 100%;
    max-width: 1000px;
    background-color: #222; /* Dark content background */
    box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3); /* Subtle gold glow */
    border-radius: 12px;
    color: #ffd700;
    overflow-y: auto;
    max-height: 90vh;
    font-size: 16px;
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    text-align: center;
    color: #ffd700;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #333;
    border-radius: 10px;
}

table thead {
    background-color: #444;
    color: #ffd700;
    text-align: left;
}

table thead th {
    padding: 15px;
    font-size: 16px;
    text-transform: uppercase;
}

table tbody tr {
    border-bottom: 1px solid #555;
}

table tbody tr:hover {
    background-color: #444; /* Highlight row on hover */
}

table tbody td {
    padding: 15px;
    font-size: 14px;
    color: #fff;
}

button {
    padding: 10px 20px;
    background-color: #444;
    color: #ffd700;
    border: 1px solid #ffd700;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease, color 0.3s ease, transform 0.2s ease;
}

button:hover {
    background-color: #ffd700;
    color: #333;
    transform: scale(1.05);
}

button:active {
    transform: scale(1);
}

button:focus {
    outline: none;
}

.sidebar {
    position: fixed;
    left: -250px; 
    top: 0;
    height: 100%;
    width: 250px;
    background-color: #333;
    color: #ffd700;
    z-index: 100;
    padding-top: 60px;
    transition: left 0.3s ease;
}

.openbtn {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 200;
    font-size: 30px;
    cursor: pointer;
    background-color: transparent;
    border: none;
    color: #ffd700;
}

    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Review Moderation</h1>

        <table border="1" cellspacing="0" cellpadding="10">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Review</th>
                    <th>Rating</th>
                    <th>Submitted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($review['first_name']) . ' ' . htmlspecialchars($review['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($review['review_text']); ?></td>
                            <td><?php echo htmlspecialchars($review['rating']); ?></td>
                            <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                            <td>
                                <form action="review_moderation.php" method="POST" style="display:inline;">
                                    <button type="submit" name="delete_review" value="<?php echo $review['id']; ?>">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No reviews available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
