<?php
// Start session
session_start();

// Database connection
$host = 'localhost';
$dbname = 'krunchwebsite';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $isLoggedIn = isset($_SESSION['user_id']);
    $first_name = '';

    // If logged in, fetch the user's first name
    if ($isLoggedIn) {
        $stmt = $pdo->prepare("SELECT first_name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $first_name = htmlspecialchars($user['first_name']); // Prevent XSS attacks
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KRUNCH - Authentic Nashville Fried Chicken</title>
    <link href="https://fonts.googleapis.com/css2?family=Monoton&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

    /* General Styles */
    body {
        margin: 0;
        padding: 0;
        font-family: 'Bangers', Arial, sans-serif; /* Fallback fonts added */
        background-color: #000;
        overflow-x: hidden;
        }.banner h1 {
    font-family: 'Bangers', cursive;
    font-size: 3rem; /* Adjusted font size */
    margin-bottom: 20px;
    color: #FFD700; /* Matches the navbar text color */
    text-shadow: none; /* Remove shadow for a cleaner match with the navbar */
}

.navbar {
    width: 100%;
    padding: 15px 5%;
    display: flex;
    justify-content: space-between; /* Space between logo and nav items */
    align-items: center; /* Center items vertically */
    background: #222;
    position: fixed;
    top: 0;
    z-index: 1000;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    box-sizing: border-box;
}

.navbar img {
    width: 150px;
}

nav ul {
    display: flex; /* Horizontal layout */
    align-items: center; /* Vertical centering */
    justify-content: center; /* Center items */
    list-style: none; /* Remove default list style */
    margin: 0;
    padding: 0;
}

nav ul li {
    margin: 0 10px; /* Spacing between menu items */
}

.navbar ul li a {
    text-decoration: none;
    color: #FFD700;
    font-size: 16px;
    text-transform: uppercase;
    transition: color 0.3s;
    position: relative;
    padding: 5px 10px;
}

.navbar ul li a:hover {
    color: #fff;
}

.navbar ul li a::after {
    content: '';
    width: 0;
    height: 3px;
    background: #FFD700;
    position: absolute;
    left: 50%;
    bottom: -5px;
    transform: translateX(-50%);
    transition: width 0.3s;
}

.navbar ul li a:hover::after {
    width: 100%;
}

/* Styling for the welcome text */
.welcome-text {
    font-size: 1.2rem;
    color: #FFD700;
    margin-left: 15px; /* Spacing from the nav items */
    font-family: 'Bangers', cursive;
}

/* Adjust for mobile */
@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap; /* Allow items to wrap */
        justify-content: center;
    }
    nav ul {
        flex-wrap: wrap; /* Stack items on smaller screens */
        justify-content: center;
    }
}


        /* Banner Section */
        .banner {
            width: 100%;
            height: 100vh;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3)), url('newkrunch.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

       /* Update this in the CSS */
       .banner h1 {
    font-family: 'Bangers', cursive;
    font-size: 3rem; /* Adjusted font size */
    margin-bottom: 20px;
    color: #FFD700; /* Matches the navbar text color */
    text-shadow: none; /* Remove shadow for a cleaner match with the navbar */
}


        }

        .banner p {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: #fff;
        }

        .banner .btn {
            font-family: 'Bangers', Arial, sans-serif;
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            color: #fff;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .banner .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        /* Features Section */
        .features {
            text-align: center;
            padding: 80px 10%;
            background: #000;
            color: #FFD700;
        }

        .features h2 {
            font-size: 3rem;
            margin-bottom: 60px;
            color: #FFD700;
        }

        .features .feature-card {
            display: inline-block;
            width: 280px;
            margin: 10px;
            padding: 20px;
            background: linear-gradient(145deg, #f0f0f0, #ffffff);
            border-radius: 15px;
            box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.1), -4px -4px 8px rgba(255, 255, 255, 0.6);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .features .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
        }

        .features .feature-card img {
            width: 100px;
            margin-bottom: 20px;
        }

        .features .feature-card h3 {
            font-size: 1.5rem;
            color: #FFD700;
            margin-bottom: 15px;
        }

        .features .feature-card p {
            color: #555;
            font-size: 1rem;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 80px 10%;
            background: #FFD700;
            color: #000;
        }

        .testimonials h2 {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 40px;
            color: #000;
        }

        .testimonials .testimonial {
            max-width: 500px;
            margin: 0 auto 40px;
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: #333;
        }

        .testimonials .testimonial p {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .testimonials .testimonial span {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            font-size: 1rem;
            color: #555;
        }

        /* Footer */
        .footer {
            padding: 20px;
            background: #222;
            text-align: center;
            color: #fff;
        }

        .footer .social-icons {
            margin-top: 10px;
        }

        .footer .social-icons a {
            text-decoration: none;
            color: #fff;
            margin: 0 10px;
            font-size: 20px;
            transition: color 0.3s;
        }

        .footer .social-icons a:hover {
            color: #FFD700;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .banner h1 {
                font-size: 3rem;
            }

            .features .feature-card {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
    <img src="logo.png" alt="KRUNCH Logo">
    <nav>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="menuview.php">Menu</a></li>
            <li><a href="ourstory.php">Our Story</a></li>
            <li><a href="reviews.php">Reviews</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="myorders.php">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="registration.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php if ($isLoggedIn): ?>
        <div class="welcome-text">Welcome, <?php echo $first_name; ?>!</div>
    <?php endif; ?>
</div>


    <!-- Banner -->
    <div class="banner">
        <h1>Welcome to KRUNCH</h1>
        <p>Bold, Fiery, and Authentic Nashville Fried Chicken.</p>
        <?php if ($isLoggedIn): ?>
            <button class="btn" onclick="window.location.href='myorders.php'">Order Now</button>
        <?php else: ?>
            <button class="btn" onclick="window.location.href='login.php'">Order Now</button>
        <?php endif; ?>
    </div>

    <!-- Features Section -->
    <section class="features">
        <h2>Why Choose KRUNCH?</h2>
        <div class="feature-card">
            <h3>Fresh Ingredients</h3>
            <p>Only the best for your plate.</p>
        </div>
        <div class="feature-card">
            <h3>Fast Delivery</h3>
            <p>Hot and fresh, every time.</p>
        </div>
        <div class="feature-card">
            <h3>Great Taste</h3>
            <p>A flavor explosion with every bite.</p>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonial">
            <p>"I don’t do chicken burgers usually BUT THIS. So beautiful. Looked exactly like the picture. So so good. Two giant juicy patties. Sauce. Love it"</p>
            <span>- Mahnoor on Foodpanda</span>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 KRUNCH - Authentic Nashville Fried Chicken</p>
        <div class="social-icons">
            <a href="#" class="fab fa-facebook"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="https://www.instagram.com/krunchlahore" class="fab fa-instagram"></a>
        </div>
    </footer>
</body>
</html>
