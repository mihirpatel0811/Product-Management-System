<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../User /login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$bankingQuery = "SELECT * FROM banking LIMIT 1";
$bankingResult = $conn->query($bankingQuery);
$bankingInfo = $bankingResult ? $bankingResult->fetch_assoc() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = $_POST['bank_name'];
    $address = $_POST['address']; // Add this line to get the address from the form
    $A_C_no = $_POST['A_C_no'];
    $IFSC_code = $_POST['IFSC_code'];

    if ($bankingInfo) {
        $updateQuery = "UPDATE banking SET bank_name=?, A_C_no=?, IFSC_code=?, address=? WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $bank_name, $A_C_no, $IFSC_code, $address, $bankingInfo['id']); // Include address here
        $stmt->execute();
        $stmt->close();
        $message = "Banking details updated successfully.";
    } else {
        $insertQuery = "INSERT INTO banking (bank_name, A_C_no, IFSC_code, address) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $bank_name, $A_C_no, $IFSC_code, $address); // Include address here
        $stmt->execute();
        $stmt->close();
        $message = "Banking details added successfully.";
    }

    header("Location: ../UBPD/main.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Banking Details</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/UBPD/banking.css">
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
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User /profile.php" class="active" id="username-link">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <main class="main">
            <h1><?php echo $bankingInfo ? "Update Banking Information" : "Add Banking Information"; ?></h1>
            <?php if (isset($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form action="../UBPD/banking.php" method="POST">
                <div class="form-group">
                    <label for="bank_name">Bank Name : </label>
                    <input type="text" id="bank_name" name="bank_name" value="<?php echo $bankingInfo ? htmlspecialchars($bankingInfo['bank_name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address : </label>
                    <input type="text" id="address" name="address" value="<?php echo $bankingInfo ? htmlspecialchars($bankingInfo['address']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="A_C_no">Account No : </label>
                    <input type="text" id="A_C_no" name="A_C_no" value="<?php echo $bankingInfo ? htmlspecialchars($bankingInfo['A_C_no']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="IFSC_code">IFSC Code : </label>
                    <input type="text" id="IFSC_code" name="IFSC_code" value="<?php echo $bankingInfo ? htmlspecialchars($bankingInfo['IFSC_code']) : ''; ?>" required>
                </div>
                <button type="submit"><i class='bx bxs-save' style="margin-right: 5px;"></i> <?php echo $bankingInfo ? "Update" : "Add"; ?> Details</button>
            </form>
        </main>
    </div>
</body>

</html>