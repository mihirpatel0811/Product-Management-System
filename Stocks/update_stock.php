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

// Fetch product list
$productOptions = "";
$result = $conn->query("SELECT id, product_name FROM products");
while ($row = $result->fetch_assoc()) {
    $selected = (isset($_POST['product_id']) && $_POST['product_id'] == $row['id']) ? "selected" : "";
    $productOptions .= "<option value='" . $row['id'] . "' $selected>" . $row['product_name'] . "</option>";
}

// Fetch product stock
$productStock = "";
$productName = "";
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $stmt = $conn->prepare("SELECT product_name, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stockResult = $stmt->get_result();
    
    if ($stockRow = $stockResult->fetch_assoc()) {
        $productStock = $stockRow['stock'];
        $productName = $stockRow['product_name'];
    }
    $stmt->close();
}

// Update stock
if (isset($_POST['update_stock'])) {
    $product_id = intval($_POST['product_id']);
    $new_stock = intval($_POST['new_stock']);
    $update_date = $_POST['update_date'];
    $updated_by = $_SESSION['username'];

    $conn->begin_transaction();
    try {
        // Update stock in products table
        $stmt1 = $conn->prepare("UPDATE products SET stock = stock + ?, last_stock_update = ? WHERE id = ?");
        $stmt1->bind_param("isi", $new_stock, $update_date, $product_id);
        $stmt1->execute();
        $stmt1->close();

        // Insert record into stock_updates table
        $stmt2 = $conn->prepare("INSERT INTO stock_updates (product_id, updated_by, update_date, added_stock, total_stock) 
                                 VALUES (?, ?, ?, ?, (SELECT stock FROM products WHERE id = ?))");
        $stmt2->bind_param("issii", $product_id, $updated_by, $update_date, $new_stock, $product_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();

        echo "<script>
                alert('Stock updated successfully!');
                window.location.href = '../Stocks/view_stock.php';
              </script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Stock update failed: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product Stock</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/Stock_update.css">
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

    <section>
        <main class="main">
            <h1>Update Product Stock</h1>
            <form method="post">
                <label>Select Product : </label>
                <select name="product_id" onchange="this.form.submit()">
                    <option value="">- - - - - - - - - - - - S e l e c t - - - - - - - - - - - -</option>
                    <?php echo $productOptions; ?>
                </select>
            </form>

            <?php if (!empty($productStock) || $productStock === 0) { ?>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo $_POST['product_id']; ?>">

                    <div class="form-group-row">
                        <p>Selected Product : <span><?php echo $productName; ?></span></p>
                        <p>Old Stock : 
                            <span style="color: <?php echo ($productStock == 0) ? 'red' : 'black'; ?>; font-weight: bold;">
                                <?php echo ($productStock == 0) ? "Out of Stock (0)" : $productStock; ?>
                            </span>
                        </p>
                    </div>

                    <div class="form-group-row" style="margin-bottom: 20px;">
                        <label>Add New Stock : </label>
                        <input type="number" name="new_stock" required>
                        <label>Date : </label>
                        <input type="date" name="update_date" required>
                    </div>
                    
                    <button type="submit" name="update_stock"><i class='bx bx-save'></i> Update Stock</button>
                </form>
            <?php } ?>
            <a href="../Stocks/view_stock.php" class="back"><i class='bx bx-arrow-back'></i> Back</a>
        </main>
    </section>
</body>
</html>

<?php $conn->close(); ?>
