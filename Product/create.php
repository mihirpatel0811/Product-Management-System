<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../User/login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all categories & brands
$all_categories = $conn->query("SELECT id, category_name FROM categories")->fetch_all(MYSQLI_ASSOC);
$all_brands = $conn->query("SELECT id, brand_name FROM brands")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $details = trim($_POST['details']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $entry_date = $_POST['entry_date'];
    $category_id = intval($_POST['category']);
    $brand_id = intval($_POST['brand_id']);

    // Check for duplicate product name
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_name = ?");
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $stmt->bind_result($product_count);
    $stmt->fetch();
    $stmt->close();

    if ($product_count > 0) {
        echo "<script>alert('Error: Product name already exists! Please use a different name.'); window.history.back();</script>";
        exit;
    }

    $insert_fields = "(product_name, details, price, stock, entry_date, category, brand";
    $insert_values = "?, ?, ?, ?, ?, ?, ?";
    $values = [$product_name, $details, $price, $stock, $entry_date, $category_id, $brand_id];
    $types = "sssissi";

    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file'];
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif' , 'pdf'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            die("Error: Invalid file type. Only JPG, JPEG, PNG, and GIF allowed.");
        }

        $new_file_name = uniqid() . '.' . $file_ext;
        $upload_path = 'uploads/' . $new_file_name;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $insert_fields .= ", original_file_name, temp_file_name";
            $insert_values .= ", ?, ?";
            $values[] = $file['name'];
            $values[] = $upload_path;
            $types .= "ss";
        } else {
            die("Error uploading file.");
        }
    }

    $stmt = $conn->prepare("INSERT INTO products $insert_fields) VALUES ($insert_values)");
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        header("Location: ../Product/product.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/product_create.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header class="header">
        <a href="#" class="logo">MK Group</a>
        <nav class="navbar">
            <a href="../Layout/home.php">Home</a>
            <a href="../Product/product.php" class="active">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Add Product Details</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group-row">
                    <div class="form-group">
                        <label for="product_name">Product Name:</label>
                        <input type="text" name="product_name" placeholder="Enter Product Name" required>
                    </div>
                    <div class="form-group">
                        <label for="entry_date">Entry Date:</label>
                        <input type="date" name="entry_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="details">Details:</label>
                    <textarea name="details" placeholder="Enter the product details" required></textarea>
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($all_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="brand_id">Brand:</label>
                        <select name="brand_id" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($all_brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand['id']); ?>">
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" name="price" placeholder="Price" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" name="stock" placeholder="Stock" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="file">Upload File:</label>
                    <input type="file" name="file">
                </div>

                <button type="submit"><i class='bx bx-save'></i> Save</button>
                <a href="../Product/product.php"><i class='bx bx-arrow-back'></i> Back</a>
            </form>
        </main>
    </section>
</body>
</html>

<?php
$conn->close();
?>
