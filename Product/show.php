<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../User /login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Prepare a statement to fetch product details along with category and brand names
    $stmt = $conn->prepare("
        SELECT p.*, c.category_name, b.brand_name 
        FROM products p 
        LEFT JOIN categories c ON p.category = c.id 
        LEFT JOIN brands b ON p.brand = b.id 
        WHERE p.id = ?
    ");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Product not found.";
            exit;
        } else {
            $product = $result->fetch_assoc();
        }
    } else {
        echo "Error executing statement: " . $stmt->error;
        exit;
    }
} else {
    echo "Error: Product ID is required.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Dashboard</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/show/products_Show.css">
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
            <a href="../Product/product.php" class="active">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User /profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <section class="body">
        <main class="main">
            <h1>Show Product Details</h1>
            <div class="show">
                <p>
                    <strong>Product Name : </strong>
                    <?php echo htmlspecialchars($product['product_name']); ?>
                </p>
                <p>
                    <strong>Details : </strong>
                    <?php echo htmlspecialchars($product['details']); ?>
                </p>
                <p>
                    <strong>Category : </strong>
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </p>
                <p>
                    <strong>Brand : </strong>
                    <?php echo htmlspecialchars($product['brand_name']); ?>
                </p>
                <p>
                    <strong>Price : </strong>
                    <?php echo '₹ ' . number_format($product['price'], 2); ?>
                </p>
                <p>
                    <strong>Product Images / Files : </strong>
                    <?php
                    $uploadDir = 'uploads/'; // Assuming uploads folder is inside Product folder and you're already in Product/
                    if (!empty($product['original_file_name']) && !empty($product['temp_file_name'])) {
                        $originalFiles = explode(',', $product['original_file_name']);
                        $tempFiles = explode(',', $product['temp_file_name']);

                        foreach ($tempFiles as $index => $tempFile) {
                            $filePath = $uploadDir . basename($tempFile);
                            if (file_exists($filePath)) {
                                echo "<a href='$filePath' target='_blank' class='file-link'>" . htmlspecialchars($originalFiles[$index]) . "</a><br>";
                            } else {
                                echo "<span style='color:red;'>File not found: " . htmlspecialchars($originalFiles[$index]) . "</span><br>";
                            }
                        }
                    } else {
                        echo "No Files";
                    }
                    ?>
                </p>

            </div>
            <a class="back" href="../Product/product.php"><i class='bx bx-arrow-back'></i> Back</a>
            <button class="print" onclick="printPage()">
                <i class='bx bx-printer'></i> Print
            </button>
            <script>
                function printPage() {
                    const printWindow = window.open('', '', 'height=600,width=800');
                    printWindow.document.write('<html><head><title>Print</title>');
                    printWindow.document.write('<link rel="stylesheet" href="../CSS/Create_Edit/products_create.css">'); // Include CSS
                    printWindow.document.write('</head><body>');
                    printWindow.document.write('<div class="print-title">Product Details</div>'); // Title for print
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