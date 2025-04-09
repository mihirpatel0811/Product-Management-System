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

// Search and filter variables
$search = $_GET['search'] ?? '';
$payment_filter = $_GET['payment_status'] ?? '';

// Count pending bills
$pending_query = $conn->query("SELECT COUNT(*) AS pending_count, SUM(pending_amount) AS total_pending_amount FROM bills WHERE payment_status = 'Pending'");
$pending_result = $pending_query->fetch_assoc();
$pending_count = $pending_result['pending_count'];
$total_pending_amount = $pending_result['total_pending_amount'] ?? 0;

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build query condition for searching multiple fields
$where_clause = "WHERE (b.bill_no LIKE CONCAT('%', ?, '%') 
                    OR buyer.buyer_name LIKE CONCAT('%', ?, '%') 
                    OR product.product_name LIKE CONCAT('%', ?, '%') 
                    OR category.category_name LIKE CONCAT('%', ?, '%') 
                    OR brand.brand_name LIKE CONCAT('%', ?, '%'))";
$params = ["sssss", &$search, &$search, &$search, &$search, &$search];

if ($payment_filter) {
    $where_clause .= " AND b.payment_status = ?";
    $params[0] .= "s";
    $params[] = &$payment_filter;
}

// Count total number of bills for pagination
$count_query = "SELECT COUNT(*) AS total FROM bills b
                LEFT JOIN buyers buyer ON b.buyer_name = buyer.id
                LEFT JOIN products product ON b.product_name = product.id
                LEFT JOIN categories category ON product.category = category.id
                LEFT JOIN brands brand ON product.brand = brand.id
                $where_clause";

$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param(...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_records = $count_result['total'];
$total_pages = ceil($total_records / $limit);

// Fetch bills with pagination
$query = "SELECT b.*, buyer.buyer_name, product.product_name, category.category_name, brand.brand_name 
          FROM bills b
          LEFT JOIN buyers buyer ON b.buyer_name = buyer.id
          LEFT JOIN products product ON b.product_name = product.id
          LEFT JOIN categories category ON product.category = category.id
          LEFT JOIN brands brand ON product.brand = brand.id
          $where_clause
          ORDER BY b.date DESC, b.id DESC
          LIMIT ? OFFSET ?";

$params[0] .= "ii";
$params[] = &$limit;
$params[] = &$offset;

$stmt = $conn->prepare($query);
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/logonew.png">
    <link rel="stylesheet" href="../CSS/Main/billing_dashborad.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        function resetSearch() {
            window.location.href = "billing.php";
        }
    </script>
</head>
<body>
    <header class="header">
        <a href="#" class="logo">Product Management System</a>
        <nav class="navbar">
            <a href="../Layout/home.php">Home</a>
            <a href="../Product/product.php">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php" class="active">Billing</a>
            <a href="../User/profile.php" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <main class="main">
            <h1>Billing Dashboard</h1>
            <div class="search-bar">
                <form action="billing.php" method="get">
                    <select name="payment_status" style="margin-right: 10px;" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="Pending" <?php echo $payment_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo $payment_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                    </select>
                    <input type="text" name="search" placeholder="Search bills...." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class='bx bx-search-alt'></i> Search</button>
                    <button class="reset" type="button" onclick="resetSearch()"><i class='bx bx-refresh'></i></button>
                    <a href="../Billing/summary.php" class="summary"><i class='bx bx-notepad'></i> Summary</a>
                    <a href="create.php"><i class='bx bx-plus'></i> Add Bill</a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Buyer Name</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>QTY</th>
                        <th style="width: 125px;">Price</th>
                        <th style="width: 125px;">Total</th>
                        <th>Payment Status</th>
                        <th style="width: 125px;">Pending Amount</th>
                        <th style="width: 310px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $i = $offset;
                    while ($row = $result->fetch_assoc()) :
                        $i++;
                ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo (new DateTime($row['date']))->format('d-m-Y'); ?></td>
                            <td><?php echo htmlspecialchars($row['bill_no'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['buyer_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['brand_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['QTY'] ?? ''); ?></td>
                            <td><?php echo '₹ ' . number_format($row['price'] ?? 0, 2); ?></td>
                            <td><?php echo '₹ ' . number_format($row['total'] ?? 0, 2); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_status'] ?? ''); ?></td>
                            <td><?php echo '₹ ' . number_format($row['pending_amount'] ?? 0, 2); ?></td>
                            <td>
                                <a href="show.php?id=<?php echo $row['id']; ?>" class="show"><i class='bx bx-show'></i> Show</a>
                                <a href="update.php?id=<?php echo $row['id']; ?>" class="edit"><i class='bx bxs-edit'></i> Payment</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure?');">
                                    <i class='bx bxs-trash'></i> Delete
                                </a>
                            </td>
                        </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pending-status">
                <h2>Pending Payments: <span><?php echo $pending_count; ?></span></h2>
                <h2>Total Pending Amount: <span>₹ <?php echo number_format($total_pending_amount, 2); ?></span></h2>
            </div>

            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&payment_status=<?php echo urlencode($payment_filter); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>