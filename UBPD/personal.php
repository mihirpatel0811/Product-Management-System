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

$personalQuery = "SELECT * FROM personal LIMIT 1";
$personalResult = $conn->query($personalQuery);
$personalInfo = $personalResult ? $personalResult->fetch_assoc() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];

    if ($personalInfo) {
        $updateQuery = "UPDATE personal SET name=?, address=?, email=?, phone_no=? WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $name, $address, $email, $phone_no, $personalInfo['id']);
        $stmt->execute();
        $stmt->close();
        $message = "Personal details updated successfully.";
    } else {
        $insertQuery = "INSERT INTO personal (name, address, email, phone_no) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $name, $address, $email, $phone_no);
        $stmt->execute();
        $stmt->close();
        $message = "Personal details added successfully.";
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
    <title>Manage Personal Details</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/UBPD/personals.css">
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

    <div class="body">
        <main class="main">
            <h1><?php echo $personalInfo ? "Update Personal Information" : "Add Personal Information"; ?></h1>
            <?php if (isset($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form action="../UBPD/personal.php" method="POST">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $personalInfo ? htmlspecialchars($personalInfo['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo $personalInfo ? htmlspecialchars($personalInfo['address']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $personalInfo ? htmlspecialchars($personalInfo['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone_no">Phone No:</label>
                    <input type="text" id="phone_no" name="phone_no" value="<?php echo $personalInfo ? htmlspecialchars($personalInfo['phone_no']) : ''; ?>" required>
                </div>
                <button type="submit"><i class='bx bxs-save' style="margin-right: 5px;"></i> <?php echo $personalInfo ? "Update" : "Add"; ?> Details</button>
            </form>
        </main>
    </div>
</body>

</html>