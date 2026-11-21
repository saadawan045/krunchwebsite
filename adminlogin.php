<?php
// Registering a new admin is only allowed for an admin who is already logged in.
// Create the first admin with the SQL in README.md.
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "krunchwebsite";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize user input
        $adminEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $adminPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

        // Prepare SQL statement to insert admin details
        $stmt = $conn->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
        $stmt->execute([$adminEmail, $adminPassword]);

        echo "Admin registered successfully!";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
        }

        .register-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"] {
            width: calc(100% - 20px); /* Full width minus padding */
            padding: 10px;
            margin-bottom: 20px; /* Space between inputs */
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background-color: #007bff; /* Bootstrap primary color */
            color: white;
            padding: 12px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
    </style>
</head>
<body>

<div class="register-container">
    <h1>Register Admin</h1>
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
