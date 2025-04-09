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

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : '';

// Fetch dropdown lists
$productQuery = "SELECT id, product_name FROM products";
$productResult = $conn->query($productQuery);

$categoryQuery = "SELECT id, category_name FROM categories";
$categoryResult = $conn->query($categoryQuery);

$brandQuery = "SELECT id, brand_name FROM brands";
$brandResult = $conn->query($brandQuery);

$buyerQuery = "SELECT id, buyer_name FROM buyers";
$buyerResult = $conn->query($buyerQuery);

// Base query
$sql = "SELECT b.*, buyer.buyer_name, p.product_name, category.category_name, brand.brand_name, b.QTY 
        FROM bills b
        LEFT JOIN buyers buyer ON b.buyer_name = buyer.id
        LEFT JOIN products p ON b.product_name = p.id
        LEFT JOIN categories category ON p.category = category.id
        LEFT JOIN brands brand ON p.brand = brand.id
        WHERE 1=1";

// Apply date filter
if ($start_date && $end_date) {
    $sql .= " AND b.date BETWEEN '$start_date' AND '$end_date'";
}

// Apply dynamic filters
if (!empty($filter_type) && !empty($filter_value)) {
    if ($filter_type == 'product') {
        $sql .= " AND p.id = '$filter_value'";
    } elseif ($filter_type == 'category') {
        $sql .= " AND category.id = '$filter_value'";
    } elseif ($filter_type == 'brand') {
        $sql .= " AND brand.id = '$filter_value'";
    } elseif ($filter_type == 'buyer') {
        $sql .= " AND buyer.id = '$filter_value'";
    }
}

// Order by latest date
$sql .= " ORDER BY b.date DESC";
$result = $conn->query($sql);

// Calculate total sales based on filters
$totalQuery = "SELECT SUM(total) AS totalAmount FROM bills WHERE 1=1";
if ($start_date && $end_date) {
    $totalQuery .= " AND date BETWEEN '$start_date' AND '$end_date'";
}
if (!empty($filter_type) && !empty($filter_value)) {
    if ($filter_type == 'product') {
        $totalQuery .= " AND product_name = '$filter_value'";
    } elseif ($filter_type == 'category') {
        $totalQuery .= " AND category = '$filter_value'";
    } elseif ($filter_type == 'brand') {
        $totalQuery .= " AND brand = '$filter_value'";
    } elseif ($filter_type == 'buyer') {
        $totalQuery .= " AND buyer_name = '$filter_value'";
    }
}
$totalResult = $conn->query($totalQuery);
$totalAmount = $totalResult->fetch_assoc()['totalAmount'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Summary</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Summary.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <script>
        function updateTotal() {
            let checkboxes = document.querySelectorAll('.bill-checkbox');
            let total = 0;
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    total += parseFloat(checkbox.dataset.total);
                }
            });
            document.getElementById('filtered-total').textContent = '₹ ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function toggleAll(source) {
            let checkboxes = document.querySelectorAll('.bill-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
            updateTotal();
        }

        function resetFilters() {
            window.location.href = "summary.php";
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
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php" class="active">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <main class="main">
            <h1>Billing Summary Dashboard</h1>
            <div class="search-bar" style="text-align: center;">
                <form action="summary.php" method="get">
                    <label style="margin-left: 10px;">From: <input type="date" name="start_date" value="<?php echo $start_date; ?>"></label>
                    <label style="margin-left: 10px;">To: <input type="date" name="end_date" value="<?php echo $end_date; ?>"></label>

                    <label style="margin-left: 10px;">Filter by:
                        <select name="filter_type" onchange="this.form.submit()">
                            <option value="">Select Filter</option>
                            <option value="product" <?php if ($filter_type == 'product') echo 'selected'; ?>>Product</option>
                            <option value="category" <?php if ($filter_type == 'category') echo 'selected'; ?>>Category</option>
                            <option value="brand" <?php if ($filter_type == 'brand') echo 'selected'; ?>>Brand</option>
                            <option value="buyer" <?php if ($filter_type == 'buyer') echo 'selected'; ?>>Buyer</option>
                        </select>
                    </label>

                    <label style="margin-left: 10px;">Value:
                        <select name="filter_value" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php
                            if ($filter_type == 'product') {
                                while ($product = $productResult->fetch_assoc()) {
                                    echo "<option value='{$product['id']}'" . ($filter_value == $product['id'] ? ' selected' : '') . ">{$product['product_name']}</option>";
                                }
                            } elseif ($filter_type == 'category') {
                                while ($category = $categoryResult->fetch_assoc()) {
                                    echo "<option value='{$category['id']}'" . ($filter_value == $category['id'] ? ' selected' : '') . ">{$category['category_name']}</option>";
                                }
                            } elseif ($filter_type == 'brand') {
                                while ($brand = $brandResult->fetch_assoc()) {
                                    echo "<option value='{$brand['id']}'" . ($filter_value == $brand['id'] ? ' selected' : '') . ">{$brand['brand_name']}</option>";
                                }
                            } elseif ($filter_type == 'buyer') {
                                while ($buyer = $buyerResult->fetch_assoc()) {
                                    echo "<option value='{$buyer['id']}'" . ($filter_value == $buyer['id'] ? ' selected' : '') . ">{$buyer['buyer_name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </label>

                    <button type="submit" style="margin-left: 10px;"><i class='bx bx-search-alt'></i> Search</button>
                    <button style="margin-left: 10px;" class="reset" type="button" onclick="resetFilters()"><i class='bx bx-refresh'></i> Reset</button>
                    <a href="../Billing/billing.php" class="back" style="margin-left: 10px;"><i class='bx bx-arrow-back' style="padding-right: 5px;"></i> Back</a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" onclick="toggleAll(this)" style="margin-right: 5px;"> Select Bills</th>
                        <th>No.</th>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Buyer Name</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>QTY</th>
                        <th>QTY-Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    while ($row = $result->fetch_assoc()) :
                        $i++;
                    ?>
                        <tr>
                            <td><input type="checkbox" class="bill-checkbox" data-total="<?php echo $row['total']; ?>" onclick="updateTotal()"></td>
                            <td><?php echo $i; ?></td>
                            <td><?php $bill_date = new DateTime($row['date']);
                                echo $bill_date->format('d-m-Y'); ?></td>
                            <td><?php echo htmlspecialchars($row['bill_no'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['buyer_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['brand_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['QTY'] ?? ''); ?></td>
                            <td><?php echo '₹ ' . number_format($row['price'], 2 ?? ''); ?></td>
                            <td><?php echo '₹ ' . number_format($row['total'], 2 ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="9" style="color:#11ff00; text-align: right; font-weight: bold;">Total Sales:</td>
                        <td colspan="2" id="filtered-total" style="color: yellow;">₹ <?php echo number_format($totalAmount, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </main>
    </div>
</body>

</html>