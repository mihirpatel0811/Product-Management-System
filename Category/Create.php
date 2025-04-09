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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $category_name = trim($_POST['category_name']);

    // Check if category already exists
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM `categories` WHERE `category_name` = ?");
    $stmt_check->bind_param("s", $category_name);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // Category already exists, show error
        $error_message = "Category already exists!";
    } else {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO `categories` (`category_name`) VALUES (?)");

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("s", $category_name);

        // Execute the statement
        if ($stmt->execute()) {
            header('Location: ../Category/category.php');
            exit();
        } else {
            $error_message = "Error creating category: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Dashboard</title>
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
            <h1>Add Category Details</h1>

            <?php if (!empty($error_message)) : ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="Create.php" method="post">
                <div class="form-group">
                    <label for="category_name">Category Name: </label>
                    <input type="text" name="category_name" placeholder="Category Name" required>
                </div>

                <button type="submit"><i class='bx bx-save'></i> Save</button>
                <a href="../Category/category.php"><i class='bx bx-arrow-back'></i> Back</a>
            </form>
        </main>
    </section>

</body>

</html>
