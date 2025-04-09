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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $buyer_name = $_POST['buyer_name'] ?? null;
    $owner_name = $_POST['owner_name'] ?? null; // Added owner_name field
    $address = $_POST['buyer_address'] ?? null;
    $phone = $_POST['buyer_phone_no'] ?? null;
    $email = $_POST['buyer_email'] ?? null;

    if (
        is_null($buyer_name) || is_null($owner_name) || is_null($address) || is_null($phone) || is_null($email)
    ) {
        echo "Error: All fields are required.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO `buyers` (`buyer_name`, `owner_name`, `buyer_address`, `buyer_phone_no`, `buyer_email`) VALUES (?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sssss", $buyer_name, $owner_name, $address, $phone, $email);

    if ($stmt->execute()) {
        header('Location: ../Buyer/buyer.php');
        exit();
    } else {
        echo "Error creating buyer: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Buyers</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/Buyers_create.css">
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
            <a href="../Buyer/buyer.php" class="active">Buyer</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo $_SESSION['username']; ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Add Buyer Details</h1>

            <form action="../Buyer/create.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="buyer_name">Buyer Name : </label>
                    <input type="text" id="buyer_name" name="buyer_name" required>
                </div>

                <div class="form-group">
                    <label for="owner_name">Owner Name : </label> <!-- Added owner name field -->
                    <input type="text" id="owner_name" name="owner_name" required>
                </div>

                <div class="form-group">
                    <label for="buyer_address">Address : </label>
                    <input type="text" id="buyer_address" name="buyer_address" required>
                </div>

                <div class="form-group">
                    <label for="buyer_phone_no">Phone Number : </label>
                    <input type="text" id="buyer_phone_no" name="buyer_phone_no" required>
                </div>

                <div class="form-group">
                    <label for="buyer_email">Email : </label>
                    <input type="email" id="buyer_email" name="buyer_email" required>
                </div>

                <button type="submit"><i class='bx bx-save'></i> Save</button>
                <a href="../Buyer/buyer.php"><i class='bx bx-arrow-back'></i> Back</a>
            </form>
        </main>
    </section>
</body>

</html>
