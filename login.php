<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admindashboard.php"); // Redirect if already logged in as admin
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "krunchwebsite";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get email and password from POST request
        $inputEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $inputPassword = $_POST['password'];

        // Check if the user is an admin
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$inputEmail]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($inputPassword, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admindashboard.php"); // Redirect to admin dashboard
            exit();
        }

        // Check if the user is a regular user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$inputEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($inputPassword, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: krunchwebsite.php"); // Redirect to user dashboard or home page
            exit();
        }

        // Invalid credentials
        $error = "Invalid email or password.";
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial initial-scale=1.0">
    <title>Login - KRUNCH</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

        body {
            font-family: 'Bangers', Arial, sans-serif; /* Use Bangers for everything */
            background: url('login.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: #000;
            margin: 0;
            padding: 0;
            color: #FFD700;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #333;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            color: #FFD700;
        }

        h1 {
            font-family: 'Bangers', Arial, sans-serif;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #FFD700;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"],
        input[type="password"] {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 2px solid #FFD700;
            border-radius: 8px;
            background: #222;
            color: #FFD700;
            font-size: 1rem;
            font-family: 'Bangers', Arial, sans-serif; /* Apply Bangers to text fields */
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #0066cc;
            outline: none;
            color: #FFD700;
        }

        button {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            color: #000;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            font-family: 'Bangers', Arial, sans-serif; /* Apply Bangers to button */
        }

        button:hover {
            transform: scale(1.05);
            transition: 0.3s ease-in-out;
        }

        .error-message {
            background: #f8d7da;
            padding: 0.8rem;
            margin-top: 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-family: 'Bangers', Arial, sans-serif;
            color: #FFD700;
        }

        .register-section {
            margin-top: 1.5rem;
            font-size: 1rem;
            text-align: center;
        }

        .register-link {
            color: #FFD700;
            font-weight: bold;
            text-decoration: none;
        }

        .register-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }

            h1 {
                font-size: 2rem;
            }

            input[type="email"],
            input[type="password"] {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            button {
                padding: 0.6rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="register-section">
            <p>Don't have an account?</p>
            <a href="registration.php" class="register-link">Register here</a>
        </div>
    </div>
</body>
</html>
