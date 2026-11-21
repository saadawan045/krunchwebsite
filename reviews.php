<?php
// reviews.php

// Database connection settings
$host = 'localhost';
$dbname = 'krunchwebsite';
$username = 'root';
$password = '';

try {
    // Create a PDO instance to connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If there is an error, display the message
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Start session to check logged-in user
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>You must be logged in to submit a review.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];  // Get the logged-in user's ID

// Handle form submission for a new review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $review_text = htmlspecialchars(trim($_POST['review_text']));
    $rating = intval($_POST['rating']);

    if (!empty($review_text) && $rating >= 1 && $rating <= 5) {
        // Insert review into the database
        $stmt = $pdo->prepare("INSERT INTO reviews (customer_id, review_text, rating) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $review_text, $rating]);

        echo "<p>Thank you for your review!</p>";
    } else {
        echo "<p>Please provide a valid review and rating (1-5).</p>";
    }
}

// Fetch all reviews from the database
$stmt = $pdo->prepare("SELECT r.review_text, r.rating, r.created_at, u.first_name, u.last_name 
                       FROM reviews r
                       JOIN users u ON r.customer_id = u.id
                       ORDER BY r.created_at DESC");
$stmt->execute();
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

    body {
        font-family: 'Bangers', cursive;
        margin: 0;
        padding: 0;
        background-color: #000000; /* Black background */
        color: #fff; /* White text */
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #222222; /* Dark grey background for content */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Enhanced shadow for depth */
        border-radius: 8px;
    }

    h1 {
        text-align: center;
        color: #ffd700; /* Gold color for the title */
        font-size: 2.5em;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    h2 {
        color: #ffd700; /* Gold color for subtitles */
        font-size: 1.6em;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .review-form {
        margin-bottom: 40px;
        background-color: #333333; /* Slightly lighter grey for form background */
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2); /* Light shadow for form */
    }

    .review-form textarea,
    .review-form select {
        width: 100%;
        padding: 14px;
        font-size: 1.1em;
        color: #fff;
        background-color: #444444; /* Dark background for form inputs */
        border: 1px solid #ffd700; /* Gold border for inputs */
        border-radius: 5px;
        margin-bottom: 20px;
        font-family: 'Bangers', cursive; /* Apply Bangers font */
    }

    .review-form textarea:focus,
    .review-form select:focus {
        border-color: #ffd700; /* Gold border on focus */
    }

    button {
        padding: 14px 30px;
        background-color: #ffd700; /* Gold button background */
        color: #000;
        border: none;
        border-radius: 5px;
        font-size: 1.2em;
        cursor: pointer;
        transition: background-color 0.3s ease;
        text-transform: uppercase;
        font-family: 'Bangers', cursive; /* Apply Bangers font to the button */
    }

    button:hover {
        background-color: #f9a500; /* Darker gold on hover */
    }

    .reviews {
        margin-top: 40px;
    }

    .review {
        border: 1px solid #444444;
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 20px;
        background-color: #333333; /* Dark grey background for review */
    }

    .review p {
        margin: 10px 0;
    }

    .review .rating {
        font-weight: bold;
        color: #ffd700; /* Gold color for rating */
    }

    .review .author {
        font-style: italic;
        font-size: 1em;
        color: #bbb; /* Light gray for author */
    }

    .review .author span {
        color: #ffd700; /* Gold color for the author's name */
    }

    .review .created-at {
        font-size: 0.9em;
        color: #888; /* Gray color for the timestamp */
    }

    @media screen and (max-width: 768px) {
        .container {
            padding: 15px;
        }

        h1 {
            font-size: 2em;
        }

        h2 {
            font-size: 1.4em;
        }

        button {
            width: 100%;
        }
    }
</style>


</head>
<body>

<div class="container">
    <h1>Customer Reviews</h1>

    <!-- Review Form -->
    <div class="review-form">
        <h2>Leave a Review</h2>
        <form action="reviews.php" method="POST">
            <label for="review_text">Your Review:</label>
            <textarea name="review_text" id="review_text" required></textarea>
            <label for="rating">Rating (1 to 5):</label>
            <select name="rating" id="rating" required>
                <option value="">Select Rating</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>
            <button type="submit">Submit Review</button>
        </form>
    </div>

    <!-- Display Reviews -->
    <div class="reviews">
        <h2>What Our Customers Say</h2>

        <?php if ($reviews): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p class="rating">Rating: <?php echo str_repeat('★', $review['rating']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    <p class="author">By: <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?> on <?php echo date("F j, Y, g:i a", strtotime($review['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to leave a review!</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
