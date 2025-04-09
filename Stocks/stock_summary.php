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

// Fetch brand, category, and search filters
$selectedBrand = isset($_GET['brand']) ? (int)$_GET['brand'] : '';
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Fetch brands
$brands = $conn->query("SELECT id, brand_name FROM brands");

// Fetch categories
$categoryQuery = "SELECT id, category_name FROM categories";
if ($selectedBrand) {
    $categoryQuery .= " WHERE id IN (SELECT DISTINCT category FROM products WHERE brand = $selectedBrand)";
}
$categories = $conn->query($categoryQuery);

// Query for stock summary
$stockSummaryQuery = "SELECT s.id, p.product_name, s.updated_by, s.update_date, s.added_stock, s.total_stock
                      FROM stock_updates s
                      JOIN products p ON s.product_id = p.id
                      WHERE 1";

if ($selectedBrand) {
    $stockSummaryQuery .= " AND p.brand = $selectedBrand";
}
if ($selectedCategory) {
    $stockSummaryQuery .= " AND p.category = $selectedCategory";
}
if (!empty($searchQuery)) {
    $stockSummaryQuery .= " AND (p.product_name LIKE '%$searchQuery%' OR s.updated_by LIKE '%$searchQuery%')";
}
$stockSummaryQuery .= " ORDER BY s.update_date DESC";

$stockSummary = $conn->query($stockSummaryQuery);
$dataExists = ($stockSummary->num_rows > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Summary</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Main/Stock_summary.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
    <header class="header">
        <a href="#" class="logo">MK Group</a>
        <nav class="navbar">
            <a href="../Layout/home.php">Home</a>
            <a href="../Product/product.php">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Stocks/view_stock.php" class="active">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <main class="main">
            <h1 style="border-bottom: .2rem solid #0ef; margin-bottom:20px;">Stock Summary</h1>

            <?php if ($dataExists): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Product Name</th>
                            <th>Updated By</th>
                            <th>Update Date</th>
                            <th>Added Stock</th>
                            <th>Total Stock</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; while ($row = $stockSummary->fetch_assoc()) : $i++; ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['updated_by']); ?></td>
                                <td><?php echo (new DateTime($row['update_date']))->format('d-m-Y'); ?></td>
                                <td><?php echo htmlspecialchars($row['added_stock']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_stock']); ?></td>
                                <td>
                                    <form action="../Stocks/delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this stock update?');">
                                        <input type="hidden" name="stock_update_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="delete-btn"><i class='bx bxs-trash'></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; font-size: 2.5rem; color: red; margin-bottom:20px;">No stock updates found.</p>
            <?php endif; ?>
            <a href="../Stocks/view_stock.php" class="back"><i class='bx bx-arrow-back'></i> Back</a>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>
