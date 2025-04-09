<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../User /login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("Error: Product ID is required.");
}

$product_id = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM `products` WHERE `id` = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Error: Product not found.");
}

// Fetch category and brand name
$category_id = $product['category'];
$brand_id = $product['brand'];

$stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($category_name);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT brand_name FROM brands WHERE id = ?");
$stmt->bind_param("i", $brand_id);
$stmt->execute();
$stmt->bind_result($brand_name);
$stmt->fetch();
$stmt->close();

// Fetch all categories & brands
$all_categories = $conn->query("SELECT id, category_name FROM categories")->fetch_all(MYSQLI_ASSOC);
$all_brands = $conn->query("SELECT id, brand_name FROM brands")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $details = trim($_POST['details']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $entry_date = $_POST['entry_date'];
    $new_category_id = intval($_POST['category']);
    $new_brand_id = intval($_POST['brand_id']);

    // Check for duplicate product name
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_name = ? AND id != ?");
    $stmt->bind_param("si", $product_name, $product_id);
    $stmt->execute();
    $stmt->bind_result($product_count);
    $stmt->fetch();
    $stmt->close();

    if ($product_count > 0) {
        echo "<script>alert('Error: Product name already exists! Please use a different name.'); window.history.back();</script>";
        exit;
    }

    $update_fields = "product_name=?, details=?, price=?, stock=?, entry_date=?, category=?, brand=?";
    $update_values = [$product_name, $details, $price, $stock, $entry_date, $new_category_id, $new_brand_id];
    $types = "sssissi";

    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file'];
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            die("Error: Invalid file type. Only JPG, JPEG, PNG, and GIF allowed.");
        }

        $new_file_name = uniqid() . '.' . $file_ext;
        $upload_path = 'uploads/' . $new_file_name;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $update_fields .= ", original_file_name=?, temp_file_name=?";
            $update_values[] = $file['name'];
            $update_values[] = $upload_path;
            $types .= "ss";
        } else {
            die("Error uploading file.");
        }
    }

    $update_values[] = $product_id;
    $types .= "i";

    $stmt = $conn->prepare("UPDATE products SET $update_fields WHERE id = ?");
    $stmt->bind_param($types, ...$update_values);

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
            <a href="../User /profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Update Product Details</h1>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group-row">
                    <div class="form-group">
                        <label for="product_name">Product Name:</label>
                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="entry_date">Entry Date:</label>
                        <input type="date" name="entry_date" value="<?php echo htmlspecialchars($product['entry_date']); ?>" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label for="details">Details:</label>
                    <textarea name="details" required><?php echo htmlspecialchars($product['details']); ?></textarea>
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category">
                            <?php foreach ($all_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($category['id'] == $category_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="brand_id">Brand:</label>
                        <select name="brand_id">
                            <?php foreach ($all_brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand['id']); ?>" <?php echo ($brand['id'] == $brand_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" readonly>
                    </div>
                </div>

                <div class="form-group file-upload">
                    <label for="file">Upload File:</label>
                    <?php if (!empty($product['original_file_name'])): ?>
                        <span class="uploaded-file"><?php echo htmlspecialchars($product['original_file_name']); ?></span>
                    <?php endif; ?>
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