<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch bill details
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid Bill ID.";
    header("Location: billing.php");
    exit();
}

$bill_id = $_GET['id'];
$bill_query = $conn->prepare("SELECT b.*, p.product_name, c.category_name, br.brand_name, 
                              buyer.buyer_name, buyer.buyer_phone_no, buyer.buyer_email
                       FROM bills b
                       JOIN products p ON b.product_name = p.id
                       JOIN buyers buyer ON b.buyer_name = buyer.id
                       JOIN categories c ON p.category = c.id
                       JOIN brands br ON p.brand = br.id
                       WHERE b.id = ?");
$bill_query->bind_param("i", $bill_id);
$bill_query->execute();
$bill_result = $bill_query->get_result();
$bill = $bill_result->fetch_assoc();

if (!$bill) {
    $_SESSION['error'] = "Bill not found.";
    header("Location: billing.php");
    exit();
}

// Process update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $qty = $_POST['qty'];
    $price = $_POST['price'];
    $paid_amount = $_POST['paid_amount'];

    $total = $qty * $price;
    $pending_amount = max($total - $paid_amount, 0);
    $payment_status = ($pending_amount == 0) ? "Paid" : "Pending";

    // Update bill
    $update_query = $conn->prepare("UPDATE bills SET QTY = ?, price = ?, total = ?, payment_status = ?, pending_amount = ? WHERE id = ?");
    $update_query->bind_param("iidssi", $qty, $price, $total, $payment_status, $pending_amount, $bill_id);

    if ($update_query->execute()) {
        // Adjust stock
        $old_qty = $bill['QTY'];
        $qty_difference = $qty - $old_qty;
        $update_stock_query = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update_stock_query->bind_param("ii", $qty_difference, $bill['product_id']);
        $update_stock_query->execute();

        $_SESSION['success'] = "Bill updated successfully.";
        header("Location: billing.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating bill.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Billing</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/Billing_update.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
            <a href="../User/profile.php" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <section>
            <main class="main">
                <h1>Update Bill</h1>
                <form action="" method="POST">
                    <div class="form-group-row">
                        <div class="form-group">
                            <label>Bill Date:</label>
                            <input type="date" value="<?php echo (new DateTime($row['date']))->format('d-m-Y'); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Bill No.:</label>
                            <input type="text" value="<?php echo htmlspecialchars($bill['bill_no']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Buyer Name:</label>
                        <input type="text" value="<?php echo htmlspecialchars($bill['buyer_name']); ?>" readonly>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label>Phone Number:</label>
                            <input type="text" value="<?php echo htmlspecialchars($bill['buyer_phone_no']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Email ID:</label>
                            <input type="text" value="<?php echo htmlspecialchars($bill['buyer_email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Product Name:</label>
                        <input type="text" value="<?php echo htmlspecialchars($bill['product_name']); ?>" readonly>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label>Category:</label>
                            <input type="text" value="<?php echo htmlspecialchars($bill['category_name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Brand:</label>
                            <input type="text" value="<?php echo htmlspecialchars($bill['brand_name']); ?>" readonly>
                        </div>
                    </div>

                    <div class="three-row">
                        <div class="form-group-row">
                            <div class="form-group">
                                <label>Quantity:</label>
                                <input type="number" name="qty" value="<?php echo htmlspecialchars($bill['QTY']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Price per Unit:</label>
                                <input type="number" name="price" value="<?php echo htmlspecialchars($bill['price']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Total Amount:</label>
                                <input type="text" value="<?php echo  '₹ ' .  number_format($bill['QTY'] * $bill['price']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label>Paid Amount:</label>
                            <input type="number" name="paid_amount" value="<?php echo htmlspecialchars($bill['total'] - $bill['pending_amount']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Payment Status:</label>
                            <select name="payment_status">
                                <option value="Paid" <?php echo ($bill['payment_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="Pending" <?php echo ($bill['payment_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit"><i class='bx bx-save'></i> Save</button>
                    <a href="../Billing/billing.php"><i class='bx bx-arrow-back'></i> Back</a>
                </form>
            </main>
        </section>
    </div>
</body>

</html>