<?php
session_start();

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: ../User /login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch buyers for the dropdown
$buyers = $conn->query("SELECT id, buyer_name, buyer_address, buyer_email, buyer_phone_no FROM buyers");

// Fetch products for the dropdown
$products = $conn->query("
    SELECT p.id, p.product_name, c.category_name, b.brand_name, p.details, p.price, p.stock 
    FROM products p
    LEFT JOIN categories c ON p.category = c.id
    LEFT JOIN brands b ON p.brand = b.id
");

// Get the next bill number
$next_bill_no = $conn->query("SELECT COALESCE(MAX(bill_no), 1010) + 1 AS next_bill_no FROM bills")->fetch_assoc()['next_bill_no'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $date = $_POST['date'] ?? null;
    $bill_no = $_POST['bill_no'] ?? null;
    $buyer_id = $_POST['buyer_name'] ?? null;
    $buyer_address = $_POST['buyer_address'] ?? null;
    $buyer_email = $_POST['buyer_email'] ?? null;
    $buyer_phone_no = $_POST['buyer_phone_no'] ?? null;
    $product_id = $_POST['product_name'] ?? null;
    $category = $_POST['category'] ?? null;
    $details = $_POST['details'] ?? null;
    $brand = $_POST['brand'] ?? null;
    $qty = $_POST['qty'] ?? null;
    $price = $_POST['price'] ?? null;
    $total = $_POST['total'] ?? null;

    // Check if all required fields are filled
    if (
        is_null($date) || is_null($bill_no) || is_null($buyer_id) || is_null($buyer_address) ||
        is_null($buyer_email) || is_null($buyer_phone_no) || is_null($product_id) ||
        is_null($category) || is_null($details) || is_null($brand) ||
        is_null($qty) || is_null($price) || is_null($total)
    ) {
        $_SESSION['error'] = "Error: All fields are required.";
        header("Location: ../Billing/create.php");
        exit;
    }

    // Validate price and stock
    if (!is_numeric($price) || !is_numeric($qty)) {
        $_SESSION['error'] = "Error: Price and quantity must be numeric.";
        header("Location: ../Billing/create.php");
        exit;
    }

    // Check product stock
    $product_stock_query = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $product_stock_query->bind_param("i", $product_id);
    $product_stock_query->execute();
    $product_stock_result = $product_stock_query->get_result();
    $product_stock = $product_stock_result->fetch_assoc()['stock'];

    if ($product_stock === null || $product_stock <= 0) {
        $_SESSION['error'] = "Error: Stock is not available for this product.";
        header("Location: ../Billing/create.php");
        exit;
    }

    // Prepare SQL statement to insert into bills table
    $stmt = $conn->prepare("INSERT INTO bills (date, bill_no, buyer_name, buyer_address, buyer_email, buyer_phone_no, product_name, category, details, brand, QTY, price, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        $_SESSION['error'] = "Error preparing statement: " . $conn->error;
        header("Location: ../Billing/create.php");
        exit;
    }

    // Bind parameters
    $stmt->bind_param("ssisssiisiiii", $date, $bill_no, $buyer_id, $buyer_address, $buyer_email, $buyer_phone_no, $product_id, $category, $details, $brand, $qty, $price, $total);
    // Execute the statement
    if ($stmt->execute()) {
        // Update stock in the products table
        $update_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update_stmt->bind_param("ii", $qty, $product_id);
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: ../Billing/billing.php");
        exit();
    } else {
        $_SESSION['error'] = "Error executing statement: " . $stmt->error;
        header("Location: ../Billing/create.php");
        exit();
    }
    
    // Check product stock
    $product_stock_query = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $product_stock_query->bind_param("i", $product_id);
    $product_stock_query->execute();
    $product_stock_result = $product_stock_query->get_result();
    $product_stock = $product_stock_result->fetch_assoc()['stock'];

    if ($product_stock === null || $product_stock <= 0) {
        $_SESSION['error'] = "Error: Stock is not available for this product.";
        header("Location: ../Billing/create.php");
        exit();
    }

    if ($qty > $product_stock) {
        $_SESSION['error'] = "Error: Requested quantity exceeds available stock.";
        header("Location: ../Billing/create.php");
        exit();
    }


    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bill</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Create_Edit/Bills_create.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        function updateBuyerDetails() {
            const buyerSelect = document.getElementById('buyer_name');
            const selectedOption = buyerSelect.options[buyerSelect.selectedIndex];
            document.getElementById('buyer_address').value = selectedOption.getAttribute('data-address');
            document.getElementById('buyer_email').value = selectedOption.getAttribute('data-email');
            document.getElementById('buyer_phone_no').value = selectedOption.getAttribute('data-phone');
        }

        function updateProductDetails() {
            const productSelect = document.getElementById('product_name');
            const selectedOption = productSelect.options[productSelect.selectedIndex];

            // Get stock value
            const stock = parseInt(selectedOption.getAttribute('data-stock'), 10);

            // Check stock availability
            if (stock === 0 || isNaN(stock)) {
                alert("Stock is not available for this product.");
                document.getElementById('qty').value = ''; // Clear quantity input
                document.getElementById('total').value = ''; // Clear total input
                return; // Exit the function
            }

            document.getElementById('category').value = selectedOption.getAttribute('data-category');
            document.getElementById('brand').value = selectedOption.getAttribute('data-brand');
            document.getElementById('details').value = selectedOption.getAttribute('data-details');
            document.getElementById('price').value = selectedOption.getAttribute('data-price');
        }

        function calculateTotal() {
            const qty = parseFloat(document.getElementById('qty').value) || 0;
            const price = parseFloat(document.getElementById('price').value) || 0;
            const total = qty * price;
            document.getElementById('total').value = total.toFixed(2);
        }

        function calculateTotal() {
            const qtyInput = document.getElementById('qty');
            const stock = parseInt(document.getElementById('product_name').selectedOptions[0].getAttribute('data-stock'), 10);
            const qty = parseInt(qtyInput.value, 10) || 0;
            const price = parseFloat(document.getElementById('price').value) || 0;

            if (qty > stock) {
                alert("Error: Requested quantity exceeds available stock.");
                qtyInput.value = stock; // Set quantity to max available stock
            }

            const total = qty * price;
            document.getElementById('total').value = total.toFixed(2);
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
            <a href="../User /profile.php" id="username-link" class="active1">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <a href="../User /logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>
    <div class="body">
        <section>
            <main class="main">
                <h1>Add Bill Details</h1>
                <form action="../Billing/create.php" method="POST">
                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="date">Date:</label>
                            <input type="date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="bill_no">Bill No:</label>
                            <input type="text" name="bill_no" value="<?php echo $next_bill_no; ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="buyer_name">Buyer Name:</label>
                            <select name="buyer_name" id="buyer_name" onchange="updateBuyerDetails()" required>
                                <option value="">Select Buyer</option>
                                <?php while ($buyer = $buyers->fetch_assoc()) : ?>
                                    <option value="<?php echo $buyer['id']; ?>"
                                        data-address="<?php echo htmlspecialchars($buyer['buyer_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-email="<?php echo htmlspecialchars($buyer['buyer_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-phone="<?php echo htmlspecialchars($buyer['buyer_phone_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($buyer['buyer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_name">Product Name:</label>
                            <select name="product_name" id="product_name" onchange="updateProductDetails()" required>
                                <option value="">Select Product</option>
                                <?php while ($product = $products->fetch_assoc()) : ?>
                                    <option value="<?php echo $product['id']; ?>"
                                        data-category="<?php echo htmlspecialchars($product['category_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-brand="<?php echo htmlspecialchars($product['brand_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-details="<?php echo htmlspecialchars($product['details'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-price="<?php echo htmlspecialchars($product['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-stock="<?php echo htmlspecialchars($product['stock'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($product['product_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="buyer_address">Address:</label>
                        <input type="text" id="buyer_address" name="buyer_address" readonly>
                    </div>
                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="buyer_email">Email:</label>
                            <input type="email" id="buyer_email" name="buyer_email" readonly>
                        </div>
                        <div class="form-group">
                            <label for="buyer_phone_no">Phone No:</label>
                            <input type="text" id="buyer_phone_no" name="buyer_phone_no" readonly>
                        </div>
                    </div>
                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <input type="text" id="category" name="category" readonly>
                        </div>
                        <div class="form-group">
                            <label for="brand">Brand:</label>
                            <input type="text" id="brand" name="brand" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="details">Details:</label>
                        <textarea id="details" name="details" readonly></textarea>
                    </div>
                    <div class="three-row">
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="price">Price:</label>
                                <input type="number" id="price" name="price" readonly>
                            </div>
                            <div class="form-group">
                                <label for="qty">Quantity:</label>
                                <input type="number" id="qty" name="qty" oninput="calculateTotal()" required>
                            </div>
                            <div class="form-group">
                                <label for="total">Total:</label>
                                <input type="text" id="total" name="total" readonly>
                            </div>
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

<?php
$conn->close();
?>