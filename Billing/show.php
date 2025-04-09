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

$personalQuery = "SELECT * FROM personal LIMIT 1"; // Assuming you want only one record
$personalResult = $conn->query($personalQuery);
$personalInfo = $personalResult ? $personalResult->fetch_assoc() : null; // Fetch the first row

// Fetch banking details
$bankingQuery = "SELECT * FROM banking";
$bankingResult = $conn->query($bankingQuery);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("
        SELECT b.*, 
               buyer.buyer_name, 
               product.product_name, 
               category.category_name, 
               brand.brand_name 
        FROM bills b
        LEFT JOIN buyers buyer ON b.buyer_name = buyer.id
        LEFT JOIN products product ON b.product_name = product.id
        LEFT JOIN categories category ON product.category = category.id
        LEFT JOIN brands brand ON product.brand = brand.id
        WHERE b.id = ?
    ");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Bill not found.";
            exit;
        } else {
            $bill = $result->fetch_assoc();
        }
    } else {
        echo "Error executing statement: " . $stmt->error;
        exit;
    }
} else {
    echo "Error: Bill ID is required.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/show/billing_show.css">
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
            <a href="../User  /profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User  /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">

        <main class="main" style="width: 900px;">

            <h1>Show Bill Details</h1>

            <div class="show">

                <p style="border-bottom: .2rem solid #00f2ff;">
                    <strong>Bill No: </strong>
                    <?php echo htmlspecialchars($bill['bill_no']); ?>
                    <strong style="margin-left: 450px;">Date: </strong>
                    <?php echo htmlspecialchars(date('d-m-Y', strtotime($bill['date']))); ?>
                </p>

                <div class="form-group-row" style="padding-top: 10px;">
                    <div class="form-group">
                        <h5 style="color: deeppink;">Buyer Details</h5>
                        <p style="padding-top: 10px;">
                            <strong>Name : </strong>
                            <?php echo htmlspecialchars($bill['buyer_name']); ?>
                        </p>
                        <p>
                            <strong>Address : </strong>
                            <?php echo htmlspecialchars($bill['buyer_address']); ?>
                        </p>
                        <p>
                            <strong>Email : </strong>
                            <?php echo htmlspecialchars($bill['buyer_email']); ?>
                        </p>
                        <p>
                            <strong>Phone No : </strong>
                            <?php echo htmlspecialchars($bill['buyer_phone_no']); ?>
                        </p>
                    </div>

                    <div class="form-group" style=" border-left: .2rem solid #00f2ff; padding-left: 10px;">
                        <h5 style="color: deeppink;">Personal Details</h5>
                        <p style="padding-top: 10px;">
                            <strong>Name : </strong>
                            <?php echo htmlspecialchars($personalInfo['name']); ?>
                        </p>
                        <p>
                            <strong>Address : </strong>
                            <?php echo htmlspecialchars($personalInfo['address']); ?>
                        </p>
                        <p>
                            <strong>Email : </strong>
                            <?php echo htmlspecialchars($personalInfo['email']); ?>
                        </p>
                        <p>
                            <strong>Phone No : </strong>
                            <?php echo htmlspecialchars($personalInfo['phone_no']); ?>
                        </p>
                    </div>
                </div>

                <h5 style="color: deeppink; margin-bottom: 10px; border-top: .2rem solid #00f2ff; padding-top: 10px;">Product Details</h5>
                <div class="form-group-row">
                    <div class="form-group" style="padding-top: 10px;">
                        <p>
                            <strong>Product Name: </strong>
                            <?php echo htmlspecialchars($bill['product_name']); ?>
                        </p>
                        <p>
                            <strong>Category: </strong>
                            <?php echo htmlspecialchars($bill['category_name']); ?>
                        </p>
                        <p>
                            <strong>Brand: </strong>
                            <?php echo htmlspecialchars($bill['brand_name']); ?>
                        </p>
                        <p>
                            <strong>Product Details: </strong>  
                            <?php echo htmlspecialchars($bill['details']); ?>
                        </p>
                    </div>
                </div>

                <p style="border-top: .2rem solid #00f2ff; padding-top: 10px;">
                    <strong>Quantity: </strong>
                    <?php echo htmlspecialchars($bill['QTY']); ?>
                    <strong style="margin-left: 175px;">Price: </strong>
                    <?php echo '₹ ' . number_format($bill['price'],2); ?>
                    <strong style="margin-left: 175px;">Total: </strong>
                    <?php echo '₹ ' . number_format($bill['total'],2); ?>
                </p>

                <div class="form-group-row" style="border-top: .2rem solid #00f2ff; padding-top: 10px;">
                    <div class="form-group" style="padding-top: 10px;">
                        <h5 style="color: deeppink;">Banking Details</h5>
                        <?php while ($bank = $bankingResult->fetch_assoc()) : ?>
                            <p style="padding-top: 10px;">
                                <strong>Bank Name : </strong>
                                <?php echo htmlspecialchars($bank['bank_name']); ?>
                                <strong style="margin-left: 25px;">Address : </strong>
                                <?php echo htmlspecialchars($bank['address']); ?><br><br>
                                <strong>Account No : </strong>
                                <?php echo htmlspecialchars($bank['A_C_no']); ?>
                                <strong style="margin-left: 25px;">IFSC Code : </strong>
                                <?php echo htmlspecialchars($bank['IFSC_code']); ?>
                            </p>
                        <?php endwhile; ?>
                    </div>
                </div>

            </div>

            <a class="back" href="../Billing/billing.php"><i class='bx bx-arrow-back'></i> Back</a>

            <button class="print" onclick="printPage()">
                <i class='bx bx-printer'></i> Print
            </button>

            <script>
                function printPage() {
                    const printWindow = window.open('', '', 'height=600,width=800');
                    printWindow.document.write('<html><head><title>Print</title>');
                    // Include the print CSS file
                    printWindow.document.write('<link rel="stylesheet" href="../CSS/show/Billing_show.css">');
                    printWindow.document.write('</head><body>');
                    // Include the entire content of the main section
                    printWindow.document.write(document.querySelector('.show').innerHTML);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.print();
                    printWindow.close();
                }
            </script>
        </main>
    </section>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>