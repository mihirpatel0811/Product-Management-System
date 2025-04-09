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

$search = $_GET['search'] ?? '';
$limit = 10;
$page = max((int)($_GET['page'] ?? 1), 1);
$start = ($page - 1) * $limit;

// Count total records for pagination
$countQuery = "SELECT COUNT(*) AS total FROM brands WHERE brand_name LIKE CONCAT('%', ?, '%')";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("s", $search);
$stmt->execute();
$countResult = $stmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch brands with pagination (ordered by newest first)
$query = "SELECT * FROM brands WHERE brand_name LIKE CONCAT('%', ?, '%') ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $search, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Main/Categories_brand_dashborad.css">
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
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>
    <div class="body">
        <main class="main">
            <h1>Brand Dashboard</h1>
            <div class="search-bar">
                <form action="brand.php" method="get">
                    <input type="text" name="search" placeholder="Search brands..." value="<?php echo htmlspecialchars($search); ?>" required>
                    <button type="submit"><i class='bx bx-search-alt'></i> Search</button>
                    <button class="reset" type="button" onclick="resetSearch()"><i class='bx bx-refresh'></i></button>
                    <a href="create.php"><i class="bx bx-plus"></i> Add Brand</a>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Brand Name</th>
                        <th style="width: 200px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $start + 1;
                    while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="edit"><i class='bx bx-edit'></i> Edit</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this brand?')">
                                    <i class='bx bx-trash'></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="prev"><i class='bx bx-left-arrow-alt'></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"> <?php echo $i; ?> </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages) : ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="next"><i class='bx bx-right-arrow-alt'></i></a>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        function resetSearch() {
            window.location.href = "brand.php";
        }
    </script>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>
