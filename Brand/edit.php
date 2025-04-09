<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../User/login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get brand ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: brand.php'); // Redirect if ID is not set
    exit();
}

$brand_id = intval($_GET['id']);
$error_message = "";

// Fetch existing brand details
$stmt = $conn->prepare("SELECT brand_name FROM brands WHERE id = ?");
$stmt->bind_param("i", $brand_id);
$stmt->execute();
$stmt->bind_result($brand_name);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_brand_name = trim($_POST['brand_name']);

    // Check if brand name already exists
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM brands WHERE brand_name = ? AND id != ?");
    $stmt_check->bind_param("si", $new_brand_name, $brand_id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_message = "Brand name already exists!";
    } else {
        // Update brand name
        $stmt_update = $conn->prepare("UPDATE brands SET brand_name = ? WHERE id = ?");
        $stmt_update->bind_param("si", $new_brand_name, $brand_id);

        if ($stmt_update->execute()) {
            header('Location: brand.php');
            exit();
        } else {
            $error_message = "Error updating brand: " . $stmt_update->error;
        }

        $stmt_update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Brand</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/brand_create.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .navbar a.active1 {
            color: #0ef;
        }
        .error-message {
            color: red;
            font-size: 1rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="header">
        <a href="#" class="logo">MK Group</a>
        <nav class="navbar">
            <a href="../Layout/home.php">Home</a>
            <a href="../Product/product.php">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php" class="active">Brand</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo $_SESSION['username']; ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Edit Brand</h1>

            <?php if (!empty($error_message)) : ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="Edit.php?id=<?php echo $brand_id; ?>" method="post">
                <div class="form-group">
                    <label for="brand_name">Brand Name: </label>
                    <input type="text" name="brand_name" value="<?php echo htmlspecialchars($brand_name); ?>" required>
                </div>

                <button type="submit"><i class='bx bx-save'></i> Update</button>
                <a href="brand.php"><i class='bx bx-arrow-back'></i> Back</a>
            </form>
        </main>
    </section>

</body>

</html>
