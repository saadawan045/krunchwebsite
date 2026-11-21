<?php
// Database connection
$host = 'localhost';
$dbname = 'krunchwebsite';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Error and success messages
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            $error_message = "Email is already registered.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)");
            $result = $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => $hashed_password
            ]);
            if ($result) {
                $success_message = "Registration successful! You can now <a href='login.php'>log in</a>.";
                $_POST = []; // Clear form fields
            } else {
                $error_message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KRUNCH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bangers', Arial, sans-serif; /* Fallback fonts added */
            background: url('login.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: #000;
            margin: 0;
            padding: 0;
            color: #FFD700;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #333;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            color: #FFD700;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Bangers', Arial, sans-serif;
            font-size: 1.8rem;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            background: #222;
            color: #FFD700;
            font-size: 1rem;
        }
        input::placeholder {
            color: #999;
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
        }
        button:hover {
            transform: scale(1.05);
            transition: 0.3s ease-in-out;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
        }
        .error {
            background-color: #842029;
            color: #fff;
        }
        .success {
            background-color: #0f5132;
            color: #fff;
        }
        a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        p {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create an Account</h2>
        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required placeholder="John">

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required placeholder="Doe">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="example@domain.com">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="******">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="******">

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>
