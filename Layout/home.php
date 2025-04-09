<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../User/login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total products
$totalProductsQuery = $conn->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $totalProductsQuery->fetch_assoc()['total'];

// Fetch total stock
$totalStockQuery = $conn->query("SELECT SUM(stock) as total_stock FROM products");
$totalStock = $totalStockQuery->fetch_assoc()['total_stock'];

// Fetch total categories
$totalCategoriesQuery = $conn->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $totalCategoriesQuery->fetch_assoc()['total'];

// Fetch total brands
$totalBrandsQuery = $conn->query("SELECT COUNT(*) as total FROM brands");
$totalBrands = $totalBrandsQuery->fetch_assoc()['total'];

// Fetch total buyers
$totalBuyersQuery = $conn->query("SELECT COUNT(*) as total FROM buyers");
$totalBuyers = $totalBuyersQuery->fetch_assoc()['total'];

// Fetch total bills
$totalBillsQuery = $conn->query("SELECT COUNT(*) as total FROM bills");
$totalBills = $totalBillsQuery->fetch_assoc()['total'];

// Fetch products with stock less than 25 or out of stock
$outOfStockQuery = $conn->query("
    SELECT p.product_name AS product_name, c.category_name AS category, b.brand_name AS brand, p.stock AS stock 
    FROM products p 
    JOIN categories c ON p.category = c.id 
    JOIN brands b ON p.brand = b.id 
    WHERE p.stock = 0 OR p.stock <= 25
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Home_Page.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <header class="header">
        <a href="#" class="logo">MK Group</a>
        <nav class="navbar">
            <a href="../Layout/home.php" class="active">Home</a>
            <a href="../Product/product.php">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <main class="main">

            <!-- Out of Stock Products Section -->
            <div class="out-of-stock">
                <h1>Out of Stock or Low Stock Products</h1>
                <?php if ($outOfStockQuery->num_rows > 0) : ?>
                    <table class="out-of-stock-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $outOfStockQuery->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                    <td>
                                        <?php 
                                            if ($row['stock'] == 0) {
                                                echo "<span style='color: red; font-weight: bold;'>Out of Stock</span>";
                                            } else {
                                                echo htmlspecialchars($row['stock']);
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No products are out of stock or low stock.</p>
                <?php endif; ?>
            </div>

            <!-- Dashboard Section -->
            <div class="dashboard">
                <h1>Dashboard</h1>
                <div class="main2">
                    <div class="box-container">
                        <div class="box">
                            <a class="file-link" href="../Product/product.php">
                                <p>Total Products: <br>
                                    <?php echo $totalProducts; ?>
                                </p>
                            </a>
                        </div>

                        <div class="box">
                            <a class="file-link" href="../Stocks/view_stock.php">
                                <p style="font-size: 1.9rem;">Total Stock Available: <br>
                                    <?php echo $totalStock; ?>
                                </p>
                            </a>
                        </div>

                        <div class="box">
                            <a class="file-link" href="../Category/category.php">
                                <p>Total Categories: <br>
                                    <?php echo $totalCategories; ?>
                                </p>
                            </a>
                        </div>
                    </div>

                    <div class="box-container">
                        <div class="box">
                            <a class="file-link" href="../Brand/brand.php">
                                <p>Total Brands: <br>
                                    <?php echo $totalBrands; ?>
                                </p>
                            </a>
                        </div>

                        <div class="box">
                            <a class="file-link" href="../Buyer/buyer.php">
                                <p>Total Buyers: <br>
                                    <?php echo $totalBuyers; ?>
                                </p>
                            </a>
                        </div>

                        <div class="box">
                            <a class="file-link" href="../Billing/billing.php">
                                <p>Total Bills: <br>
                                    <?php echo $totalBills; ?>
                                </p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <footer id="contact" class="footer">
        <a href="#" class="logo">MK Group</a>
        <div class="social-media">
            <a aria-label="Chat on WhatsApp" href="https://wa.me/9510457100"><i class='bx bxl-whatsapp'></i></a>
            <a href="https://www.instagram.com/immihir17193/profilecard/?igsh=MXA1NjI2cGx1NXE0Yg==">
                <i class='bx bxl-instagram'></i></a>
            <a href="https://www.facebook.com/immihir17193?mibextid=ZbWKwL"><i class='bx bxl-facebook'></i></a>
            <a href="mailto:mihirbhayani8@gmail.com"><i class='bx bxl-gmail'></i></a>
        </div>
    </footer>

</body>

</html>
