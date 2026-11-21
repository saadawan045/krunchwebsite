<?php
// contact.php

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
    <title>Contact Us | Krunch</title>
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

        .contact-info {
            margin-top: 30px;
            padding: 20px;
            background-color: #333333; /* Slightly lighter grey for background */
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .contact-info p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .contact-info .info-title {
            font-weight: bold;
            font-size: 1.5em;
            color: #ffd700; /* Gold color for titles */
        }

        .contact-info .info-item {
            margin-bottom: 10px;
        }

        .contact-info .info-item span {
            color: #bbb; /* Light grey for the text */
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
                padding: 15px;
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

<div class="container">
    <h1>Contact Us</h1>

    <!-- Customer Service Section -->
    <div class="contact-info">
        <h2>Customer Service</h2>
        <div class="info-item">
            <p class="info-title">Phone:</p>
            <p>+1 (555) 123-4567</p>
        </div>
        <div class="info-item">
            <p class="info-title">Email:</p>
            <p>support@krunch.com</p>
        </div>
        <div class="info-item">
            <p class="info-title">Operating Hours:</p>
            <p>Monday to Friday: 9 AM - 6 PM</p>
            <p>Saturday: 10 AM - 4 PM</p>
            <p>Sunday: Closed</p>
        </div>
    </div>

    <!-- Delivery Section -->
    <div class="contact-info">
        <h2>Delivery Information</h2>
        <div class="info-item">
            <p class="info-title">Phone:</p>
            <p>+1 (555) 987-6543</p>
        </div>
        <div class="info-item">
            <p class="info-title">Email:</p>
            <p>delivery@krunch.com</p>
        </div>
        <div class="info-item">
            <p class="info-title">Delivery Hours:</p>
            <p>Daily: 11 AM - 10 PM</p>
        </div>
    </div>

    <div class="footer">
        <p>Visit our <a href="krunchwebsite.php">Homepage</a> or <a href="menuview.php">Check the Menu</a></p>
    </div>
</div>

</body>
</html>
