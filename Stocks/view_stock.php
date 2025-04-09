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

$selectedBrand = isset($_GET['brand']) ? (int)$_GET['brand'] : '';
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : ''; // out_of_stock or low_stock

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Fetch brands
$brands = $conn->query("SELECT id, brand_name FROM brands");

// Fetch categories based on brand selection
$categoryQuery = "SELECT id, category_name FROM categories";
if ($selectedBrand) {
    $categoryQuery .= " WHERE id IN (SELECT DISTINCT category FROM products WHERE brand = $selectedBrand)";
}
$categories = $conn->query($categoryQuery);

// Fetch products with brand and category
$productQuery = "SELECT products.product_name, products.stock, brands.brand_name, categories.category_name
                 FROM products
                 LEFT JOIN brands ON products.brand = brands.id
                 LEFT JOIN categories ON products.category = categories.id
                 WHERE 1";

if ($selectedBrand) {
    $productQuery .= " AND products.brand = $selectedBrand";
}
if ($selectedCategory) {
    $productQuery .= " AND products.category = $selectedCategory";
}
if (!empty($search)) {
    $productQuery .= " AND products.product_name LIKE '%$search%'";
}

if ($type === 'out_of_stock') {
    $productQuery .= " AND products.stock = 0";
} elseif ($type === 'low_stock') {
    $productQuery .= " AND products.stock <= 25 AND products.stock > 0";
}

$productQuery .= " LIMIT $start, $limit";
$products = $conn->query($productQuery);

// Count total products for pagination
$countQuery = "SELECT COUNT(*) AS total FROM products WHERE 1";
if ($selectedBrand) {
    $countQuery .= " AND brand = $selectedBrand";
}
if ($selectedCategory) {
    $countQuery .= " AND category = $selectedCategory";
}
if (!empty($search)) {
    $countQuery .= " AND product_name LIKE '%$search%'";
}
if ($type === 'out_of_stock') {
    $countQuery .= " AND stock = 0";
} elseif ($type === 'low_stock') {
    $countQuery .= " AND stock <= 25 AND stock > 0";
}

$totalRecords = $conn->query($countQuery)->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/show/views_stock.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <script>
        function fetchCategories(brandId) {
            let categorySelect = document.getElementById("category");
            categorySelect.innerHTML = '<option value="">Loading...</option>';

            fetch('fetch_categories.php?brand=' + brandId)
                .then(response => response.json())
                .then(data => {
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    data.forEach(category => {
                        let option = document.createElement("option");
                        option.value = category.id;
                        option.textContent = category.category_name;
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching categories:', error));
        }
    </script>
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
            <h1>Stock Dashboard</h1>

            <form method="GET" class="search-bar">
                <div class="filter-group">
                    <select name="brand" id="brand" onchange="fetchCategories(this.value); this.form.submit();">
                        <option value="">Select Brand</option>
                        <?php while ($brand = $brands->fetch_assoc()) : ?>
                            <option value="<?php echo $brand['id']; ?>" <?php echo ($selectedBrand == $brand['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="">Select Category</option>
                        <?php while ($category = $categories->fetch_assoc()) : ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="search-group">
                    <input type="text" name="search" placeholder="Search Product..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="bx bx-search-alt"></i> Search</button>
                    <a href="view_stock.php" class="reset"><i class='bx bx-refresh'></i></a>
                    <a href="update_stock.php" class="add-stock"><i class='bx bx-plus'></i> Add Stock</a>
                    <a href="stock_summary.php" class="summary"><i class='bx bx-notepad'></i> Summary</a>
                </div>
            </form>

            <div class="stock-buttons" style="margin: 20px 0; text-align: center;">
                <a href="?brand=<?php echo $selectedBrand; ?>&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($search); ?>&type=out_of_stock" class="summary" style="margin-right: 15px;">List of Out of Stock Products</a>
                <a href="?brand=<?php echo $selectedBrand; ?>&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($search); ?>&type=low_stock" class="summary">List of Products Stock ≤ 25</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0) : ?>
                            <?php while ($row = $products->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td>
                                        <?php 
                                            if ($row['stock'] == 0) {
                                                echo '<span style="color: red; font-weight: bold;">Out of Stock</span>';
                                            } else {
                                                echo htmlspecialchars($row['stock']);
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: red; font-weight: bold;">No stock available for the selected filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?brand=<?php echo $selectedBrand; ?>&category=<?php echo $selectedCategory; ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>" class="prev"><i class='bx bx-left-arrow-alt'></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <a href="?brand=<?php echo $selectedBrand; ?>&category=<?php echo $selectedCategory; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"> <?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages) : ?>
                    <a href="?brand=<?php echo $selectedBrand; ?>&category=<?php echo $selectedCategory; ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>" class="next"><i class='bx bx-right-arrow-alt'></i></a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>