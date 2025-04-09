<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../User /login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'product_management');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand_name = $_POST['brand_name'];

    $stmt = $conn->prepare("INSERT INTO `brands` (`brand_name`) VALUES (?)");

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $brand_name);

    if ($stmt->execute()) {
        header('Location: ../Brand/brand.php');
        exit();
    } else {
        echo "Error creating brand: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/brand_create.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .navbar a.active1 {
            color: #0ef;
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
            <a href="../User /profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo $_SESSION['username']; ?>
            </a>
            <a href="../User /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Add Brand Details</h1>
            <form action="../Brand/create.php" method="post">
                <div class="form-group">
                    <label for="brand_name">Brand Name : </label>
                    <input type="text" name="brand_name" placeholder="Brand Name" required>
                </div>

                <button type="submit"><i class='bx bx-save'></i> Save</button>
                <a href="../Brand/brand.php"><i class='bx bx-arrow-back'></i> Back</a>
            </form>
        </main>
    </section>

</body>

</html>

<?php
$conn->close();
?>