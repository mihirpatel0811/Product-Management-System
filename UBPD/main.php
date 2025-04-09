<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../User  /login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch personal details
$personalQuery = "SELECT * FROM personal LIMIT 1"; // Assuming you want only one record
$personalResult = $conn->query($personalQuery);
$personalInfo = $personalResult ? $personalResult->fetch_assoc() : null; // Fetch the first row

// Fetch banking details
$bankingQuery = "SELECT * FROM banking";
$bankingResult = $conn->query($bankingQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/UBPD/main.css">
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
            <a href="../User  /profile.php" class="active" id="username-link">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User  /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class=".body">
        <main class="main">
            <div class="personal">
                <h1>Personal Information</h1>
                <a href="../UBPD/personal.php"><i class='bx bx-plus'></i><?php echo $personalInfo ? "Edit" : "Add"; ?> Personal Information</a>
                <table>
                    <tr>
                        <th style="width: 150px;">Name</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th style="width: 155px;">Phone No</th>
                    </tr>
                    <?php if ($personalInfo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($personalInfo['name']); ?></td>
                            <td><?php echo htmlspecialchars($personalInfo['address']); ?></td>
                            <td><?php echo htmlspecialchars($personalInfo['email']); ?></td>
                            <td><?php echo htmlspecialchars($personalInfo['phone_no']); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No personal information found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="banking">
                <h1>Banking Information</h1>
                <a href="../UBPD/banking.php"><i class='bx bx-plus'></i><?php echo $bankingResult && $bankingResult->num_rows > 0 ? "Edit" : "Add"; ?> Banking Information</a>
                <table>
                    <tr>
                        <th>Bank Name</th>
                        <th>Address</th>
                        <th>Account No</th>
                        <th>IFSC Code</th>
                    </tr>
                    <?php if ($bankingResult && $bankingResult->num_rows > 0): ?>
                        <?php while ($bankingInfo = $bankingResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bankingInfo['bank_name']); ?></td>
                                <td><?php echo htmlspecialchars($bankingInfo['address']); ?></td>
                                <td><?php echo htmlspecialchars($bankingInfo['A_C_no']); ?></td>
                                <td><?php echo htmlspecialchars($bankingInfo['IFSC_code']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No banking information found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div><br><br>
            <a href="../User  /profile.php" class="back"><i class='bx bx-arrow-back' style="padding-right: 5px;"></i> Back</a>
        </main>
    </div>
    <script src="../JS/script.js"></script>
</body>

</html>

<?php
$conn->close();
?>