<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../user/login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from the session
$username = $_SESSION['username'];

// Handle image upload
if (isset($_POST['upload'])) {
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $targetDir = "../Images/";
        $fileName = basename($_FILES['profile_img']['name']);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFilePath)) {
                $stmt = $conn->prepare("UPDATE users SET profile_img = ? WHERE username = ?");
                $stmt->bind_param("ss", $fileName, $username);
                if ($stmt->execute()) {
                    echo "Profile image updated successfully.";
                } else {
                    echo "Error updating profile image.";
                }
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}

// Handle profile image removal
if (isset($_POST['remove'])) {
    $defaultImg = "user icon.png"; // Default profile image
    $stmt = $conn->prepare("UPDATE users SET profile_img = ? WHERE username = ?");
    $stmt->bind_param("ss", $defaultImg, $username);

    if ($stmt->execute()) {
        echo "Profile image removed successfully.";
    } else {
        echo "Error removing profile image.";
    }
}

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Profile_page.css">
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
            <a href="../Billing/billing.php">Billing</a>
            <a href="../User/profile.php" id="username-link" class="active">
                <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <a href="../User/logout.php"><i class='bx bx-log-out'></i> Logout</a>
        </nav>
    </header>

    <div class="body">
        <main class="main">
            <h1>User Profile</h1>
            <div class="user">
                <img src="../Images/<?php echo !empty($user['profile_img']) && file_exists("../Images/" . $user['profile_img']) ? htmlspecialchars($user['profile_img']) : 'user_icon2.jpeg'; ?>"
                    alt="Profile Picture" class="profile-pic">
                <div class="user-info">
                    <?php if ($user): ?>
                        <p><strong>Username: </strong><samp><?php echo htmlspecialchars($user['username']); ?></samp></p>
                        <p><strong>Email: </strong><samp><?php echo htmlspecialchars($user['email']); ?></samp></p>
                        <p><strong>Password: </strong><samp><?php echo htmlspecialchars($user['password']); ?></samp></p>
                    <?php else: ?>
                        <p>User not found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-actions">
                <a href="../User/change_password.php" class="change-password-btn">
                    <i class='bx bx-lock' style="margin-right: 5px;"></i> Change Password
                </a>

                <form action="profile.php" method="POST">
                    <button type="submit" name="remove" class="remove-btn">
                        <i class='bx bx-trash' style="margin-right: 5px;"></i> Remove Profile Picture
                    </button>
                </form>
            </div>

            <a href="../UBPD/main.php" class="information-btn">
                <i class='bx bxs-user-circle' style="margin-right: 5px;"></i> Personal / Banking Information
            </a>

            <div class="form-container">
                <!-- Upload Profile Picture Form -->
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="change-img">
                        <input type="file" id="file" name="profile_img" required>
                        <button type="submit" name="upload" class="upload-btn">
                            <i class='bx bx-upload' style="margin-right: 5px;"></i> Upload
                        </button>
                    </div>
                </form>
            </div>

            <a href="../Product/product.php" class="back">
                <i class='bx bx-arrow-back' style="padding-right: 5px;"></i> Back
            </a>
        </main>
    </div>

    <footer id="contact" class="footer">
        <a href="#" class="logo">MK Group</a>
        <div class="social-media">
            <a aria-label="Chat on WhatsApp" href="https://wa.me/9510457100"><i class='bx bxl-whatsapp'></i></a>
            <a href="https://www.instagram.com/immihir17193/profilecard/?igsh=MXA1NjI2cGx1NXE0Yg==">
                <i class='bx bxl-instagram'></i></a>
            <a href="https://www.facebook.com/immihir17193?mibextid=ZbWKwL"><i class='bx bxl-facebook'></i></a>
            <a href="mailto:mihirbhayani8@gmail.com"><i class='bx bxl-gmail'></i></a>
        </div>
    </footer>

    <script src="../JS/script.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>