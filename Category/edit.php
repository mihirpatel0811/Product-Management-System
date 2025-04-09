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

// Get category ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: category.php'); // Redirect if ID is not set
    exit();
}

$category_id = intval($_GET['id']);
$error_message = "";

// Fetch existing category details
$stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($category_name);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_category_name = trim($_POST['category_name']);

    // Check if category name already exists
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ? AND id != ?");
    $stmt_check->bind_param("si", $new_category_name, $category_id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_message = "Category name already exists!";
    } else {
        // Update category name
        $stmt_update = $conn->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
        $stmt_update->bind_param("si", $new_category_name, $category_id);

        if ($stmt_update->execute()) {
            header('Location: category.php');
            exit();
        } else {
            $error_message = "Error updating category: " . $stmt_update->error;
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
    <title>Edit Category</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/category_create.css">
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
            <a href="../Category/category.php" class="active">Category</a>
            <a href="../Brand/brand.php">Brand</a>
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
            <h1>Edit Category</h1>

            <?php if (!empty($error_message)) : ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="Edit.php?id=<?php echo $category_id; ?>" method="post">
                <div class="form-group">
                    <label for="category_name">Category Name: </label>
                    <input type="text" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required>
                </div>

                <button type="submit"><i class='bx bx-save'></i> Update</button>
                <a href="category.php"><i class='bx bx-arrow-back'></i> Back</a>
            </form>
        </main>
    </section>

</body>

</html>
