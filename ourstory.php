<?php
// ourstory.php

// Database connection settings (if needed, depending on use)
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Story | Krunch</title>
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
        max-width: 900px;
        margin: 0 auto;
        padding: 60px 20px 20px 20px; /* Added padding-top to move content further down */
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

    .story-text {
        font-size: 1.2em;
        color: #ddd;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .story-text p {
        margin-bottom: 20px;
    }

    .image-container {
        text-align: center;
        margin-top: 50px;
    }

    .story-image {
        width: 100%;
        height: auto;
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    }

    .footer {
        margin-top: 40px;
        text-align: center;
        padding: 20px;
        font-size: 1.1em;
        color: #bbb;
    }

    .footer a {
        color: #ffd700; /* Gold color for links */
        text-decoration: none;
    }

    .footer a:hover {
        text-decoration: underline;
    }

    @media screen and (max-width: 768px) {
        .container {
            padding: 70px 15px 15px 15px; /* Increased padding-top for smaller screens */
        }

        h1 {
            font-size: 2em;
        }

        h2 {
            font-size: 1.4em;
        }
    }
</style>

</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h1>Our Story</h1>

    <div class="story-text">
        <p>
            Krunch was born out of a dream shared by two young Pakistani brothers, Saad and Ismael. After growing up in Dubai and moving to Lahore in 2021, the two brothers found themselves craving the authentic flavors of Nashville’s fried chicken scene – a taste that was hard to find in Lahore.
        </p>
        <p>
            After years of enjoying places like Dave's Hot Chicken and Pickl, Saad and Ismael decided to bring that unforgettable taste to Lahore. Their vision was clear – to introduce the Nashville fried chicken experience, focusing on authenticity, flavor, and the power of high-quality ingredients.
        </p>
        <p>
            Saad, with his exceptional marketing skills, understood the importance of creating a brand that resonated with people. He knew how to tell the story of Krunch and how to make it stand out. His passion for storytelling and connecting with customers became the backbone of the brand.
        </p>
        <p>
            Ismael, on the other hand, was the culinary genius behind Krunch. With years of experience in the kitchen, he had the perfect balance of creativity and precision. His passion for cooking and dedication to perfecting the art of fried chicken brought Saad’s vision to life in the most flavorful way possible.
        </p>
        <p>
            Together, Saad and Ismael combined their unique strengths and worked tirelessly to make their dream a reality. The bond between the two brothers is the heart of Krunch, and it’s what drives the restaurant to keep delivering the best Nashville fried chicken experience in Lahore.
        </p>
        <p>
            Today, Krunch stands as a testament to their hard work, love for food, and unwavering bond as brothers. It's not just about the food; it's about the experience, the flavors, and the passion that Saad and Ismael pour into everything they do.
        </p>
    </div>

    <div class="image-container">
        <img src="burger.jpg" alt="Krunch double chicken burger" class="story-image">
    </div>

    <div class="footer">
        <p>Visit our <a href="krunchwebsite.php">Homepage</a> or <a href="menu.php">Check the Menu</a></p>
    </div>
</div>

</body>
</html>
