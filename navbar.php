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

<!-- Navbar -->
<div class="navbar">
    <img src="logo.png" alt="KRUNCH Logo">
    <nav>
        <ul>
            <li><a href="krunchwebsite.php">Home</a></li>
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

<!-- Styles -->
<link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
.navbar {
    width: 100%;
    padding: 15px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    display: flex;
    align-items: center;
    justify-content: center;
    list-style: none;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin: 0 10px;
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

.welcome-text {
    font-size: 1.2rem;
    color: #FFD700;
    margin-left: 15px;
    font-family: 'Bangers', cursive;
}

/* Adjust for mobile */
@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap;
        justify-content: center;
    }
    nav ul {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
