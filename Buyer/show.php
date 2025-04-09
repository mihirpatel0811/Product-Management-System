<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../User/login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Fetch buyer details
    $stmt = $conn->prepare("SELECT * FROM buyers WHERE id = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Buyer not found.";
            exit;
        } else {
            $buyer = $result->fetch_assoc();
        }
    } else {
        echo "Error executing statement: " . $stmt->error;
        exit;
    }
} else {
    echo "Error: Buyer ID is required.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/show/Buyer_show.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .show,
            .show * {
                visibility: visible;
            }

            .show {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .print-title {
                font-size: 24px;
                font-weight: bold;
                text-align: center;
                margin-bottom: 20px;
            }
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
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Buyer/buyer.php" class="active">Buyer</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Show Buyer Details</h1>
            <div class="show">
                <p>
                    <strong>Buyer Name: </strong>
                    <?php echo htmlspecialchars($buyer['buyer_name']); ?>
                </p>
                <p>
                    <strong>Owner Name: </strong>
                    <?php echo htmlspecialchars($buyer['owner_name']); ?>  <!-- Added Owner Name -->
                </p>
                <p>
                    <strong>Email: </strong>
                    <?php echo htmlspecialchars($buyer['buyer_email']); ?>
                </p>
                <p>
                    <strong>Address: </strong>
                    <?php echo htmlspecialchars($buyer['buyer_address']); ?>
                </p>
                <p>
                    <strong>Phone No: </strong>
                    <?php echo htmlspecialchars($buyer['buyer_phone_no']); ?>
                </p>
            </div>
            <a class="back" href="../Buyer/buyer.php"><i class='bx bx-arrow-back'></i> Back</a>
            <button class="print" onclick="printPage()">
                <i class='bx bx-printer'></i> Print
            </button>
            <script>
                function printPage() {
                    const printWindow = window.open('', '', 'height=600,width=800');
                    printWindow.document.write('<html><head><title>Print</title>');
                    printWindow.document.write('<link rel="stylesheet" href="../CSS/Create_Edit/buyers_create.css">'); // Include CSS
                    printWindow.document.write('</head><body>');
                    printWindow.document.write('<div class="print-title">Buyer Details</div>'); // Title for print
                    printWindow.document.write(document.querySelector('.show').innerHTML); // Write the show content to the new window
                    printWindow.document.write('</body></html>');
                    printWindow.document.close(); // Close the document
                    printWindow.print(); // Trigger the print dialog
                    printWindow.close(); // Close the print window after printing
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
