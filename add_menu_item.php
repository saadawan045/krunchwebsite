<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "krunchwebsite";

$product_id = isset($_GET['id']) ? $_GET['id'] : null;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // If we're editing, fetch the product data
    if ($product_id) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $product_data = null;
    }

    // Fetch all products for listing, joining with categories to get category name
    $stmt = $conn->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all categories from the categories table
    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Handle the form submission for adding or updating a product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category_id = $_POST['category'];
        $image = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : null;
        $target_dir = __DIR__ . "/uploads/";

        if ($image) {
            $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                die('Only image files (jpg, png, webp, gif) can be uploaded.');
            }
            $target_file = $target_dir . basename($image);
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        }

        try {
            if ($product_id) {
                $stmt = $conn->prepare("UPDATE products SET name = :name, description = :description, price = :price, category_id = :category_id" . ($image ? ", image = :image" : "") . " WHERE id = :id");
                $stmt->bindParam(':id', $product_id);
                if ($image) {
                    $stmt->bindParam(':image', $target_file);
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id" . ($image ? ", image" : "") . ") VALUES (:name, :description, :price, :category_id" . ($image ? ", :image" : "") . ")");
                if ($image) {
                    $stmt->bindParam(':image', $target_file);
                }
            }

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category_id', $category_id);

            $stmt->execute();
            header("Location: add_menu_item.php");
        } catch (PDOException $e) {
            echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
        }
    }

    if (isset($_POST['delete_selected'])) {
        if (isset($_POST['selected_products'])) {
            $selected_ids = $_POST['selected_products'];
            $ids = implode(",", array_map('intval', (array) $selected_ids));
            try {
                $stmt = $conn->prepare("DELETE FROM products WHERE id IN ($ids)");
                $stmt->execute();
                header("Location: add_menu_item.php");
            } catch (PDOException $e) {
                echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
            }
        } else {
            echo "<script>alert('No products selected for deletion.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product_id ? 'Edit' : 'Add'; ?> Menu Item - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>

        body, h1, h2, p, label, button, a, input, textarea, select, table, th, td {
            font-family: 'Bangers', Arial, sans-serif;
        }

        body {
            background-color: black;
            margin: 0;
            padding: 20px;
            color: gold;
        }

        h1, h2 {
            color: gold;
            font-weight: 300;
        }

        .container {
            background-color: #333;
            color: gold;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            margin: 20px auto;
            max-width: 800px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: gold;
        }

        th, td {
            border: 1px solid gold;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #444;
        }

        select, input, textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid gold;
            border-radius: 5px;
            background-color: black;
            color: gold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-danger {
            background-color: darkred;
            color: gold;
        }

        .btn:hover {
            background-color: darkgoldenrod;
        }

        .btn-danger:hover {
            background-color: crimson;
        }

        a {
            color: gold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h1><?php echo $product_id ? 'Edit' : 'Add'; ?> Menu Item</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo $product_data['name'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo $product_data['description'] ?? ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="text" id="price" name="price" value="<?php echo $product_data['price'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($product_data['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo $category['category_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image">
                <?php if ($product_data && $product_data['image']) { ?>
                    <div class="image-container">
                    <img src="uploads/<?php echo basename($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    </div>
                <?php } ?>
            </div>

            <div class="form-group">
                <button type="submit" class="btn"><?php echo $product_id ? 'Update' : 'Add'; ?> Product</button>
            </div>
        </form>

        <form method="POST">
            <h2>All Products</h2>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" onclick="toggleSelectAll()"></th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) { ?>
                        <tr>
                            <td><input type="checkbox" name="selected_products[]" value="<?php echo $product['id']; ?>"></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['description']; ?></td>
                            <td><?php echo $product['price']; ?></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td><img src="uploads/<?php echo basename($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image" style="max-width: 100px;"></td>
                            <td><a href="add_menu_item.php?id=<?php echo $product['id']; ?>">Edit</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="form-group">
                <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected</button>
            </div>
        </form>
    </div>

    <script>
        function toggleSelectAll() {
            var selectAllCheckbox = document.getElementById('select_all');
            var checkboxes = document.getElementsByName('selected_products[]');
            for (var checkbox of checkboxes) {
                checkbox.checked = selectAllCheckbox.checked;
            }
        }
    </script>
</body>
</html>