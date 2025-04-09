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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$start = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) AS total FROM products";
$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

if ($search) {
    $stmt = $conn->prepare("SELECT p.*, c.category_name, b.brand_name FROM products p LEFT JOIN categories c ON p.category = c.id LEFT JOIN brands b ON p.brand = b.id WHERE p.product_name LIKE CONCAT('%', ?, '%') OR c.category_name LIKE CONCAT('%', ?, '%') OR b.brand_name LIKE CONCAT('%', ?, '%') OR p.stock LIKE CONCAT('%', ?, '%') OR p.entry_date LIKE CONCAT('%', ?, '%') ORDER BY p.id DESC LIMIT ?, ?");
    $stmt->bind_param("ssssssi", $search, $search, $search, $search, $search, $start, $limit);
} else {
    $stmt = $conn->prepare("SELECT p.*, c.category_name, b.brand_name FROM products p LEFT JOIN categories c ON p.category = c.id LEFT JOIN brands b ON p.brand = b.id ORDER BY p.id DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $start, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Main/Product_dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
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
    <div class="body">
        <main class="main">
            <h1>Add Product Details</h1>
            <div class="search-bar">
                <form action="../Product/product.php" method="GET">
                    <input type="text" name="search" placeholder="Search Products......" required>
                    <button type="submit"><i class="bx bx-search-alt"></i> Search</button>
                    <button class="reset" type="button" onclick="resetSearch()"><i class='bx bx-refresh'></i></button>
                    <a href="../Product/create.php"><i class='bx bx-plus'></i> Add Product</a>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Entry Date</th>
                        <th>Product Name</th>
                        <th style="width: 150px;">Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th style="width: 275px;">Image/Files</th>
                        <th style="width: 300px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = $start;
                    while ($row = $result->fetch_assoc()) :
                        $i++;
                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo (new DateTime($row['entry_date']))->format('d-m-Y'); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo isset($row['category_name']) ? htmlspecialchars($row['category_name']) : 'N/A'; ?></td>
                            <td><?php echo isset($row['brand_name']) ? htmlspecialchars($row['brand_name']) : 'N/A'; ?></td>
                            <td><?php echo '₹ ' . number_format($row['price'], 2); ?></td>
                            <td class="file-link">
                                <?php
                                $uploadDir = '../Product/uploads/';
                                if (!empty($row['original_file_name']) && !empty($row['temp_file_name'])) {
                                    $originalFiles = explode(',', $row['original_file_name']);
                                    $tempFiles = explode(',', $row['temp_file_name']);

                                    foreach ($tempFiles as $index => $tempFile) {
                                        $filePath = $uploadDir . basename($tempFile);
                                        if (file_exists($filePath)) {
                                            echo "<a href='$filePath' target='_blank' class='file-link'>" . htmlspecialchars($originalFiles[$index]) . "</a><br>";
                                        } else {
                                            echo "<span style='color:red;'>File not found: " . htmlspecialchars($originalFiles[$index]) . "</span><br>";
                                        }
                                    }
                                } else {
                                    echo "No Files";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="../Product/show.php?id=<?php echo $row['id']; ?>" class="show"><i class='bx bx-show'></i> Show</a>
                                <a href="../Product/edit.php?id=<?php echo $row['id']; ?>" class="edit"><i class='bx bxs-edit'></i> Edit</a>
                                <a href="../Product/delete.php?id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class='bx bxs-trash'></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="prev"><i class='bx bx-left-arrow-alt'></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"> <?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages) : ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next"><i class='bx bx-right-arrow-alt'></i></a>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        function resetSearch() {
            window.location.href = "../Product/product.php";
        }
    </script>
</body>
</html>