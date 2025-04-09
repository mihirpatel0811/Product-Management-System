<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'product_management');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['username'])) {
        // User is logged in
        $username = $_SESSION['username'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            // Update the password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_password, $username); // Store plaintext password (not recommended)

            if ($stmt->execute()) {
                $message = "Password changed successfully.";
            } else {
                $message = "Error changing password.";
            }

            $stmt->close();
        } else {
            $message = "New password and confirm password do not match.";
        }
    } else {
        // User is not logged in
        $username = $_POST['username'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            // Update the password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_password, $username); // Store plaintext password (not recommended)

            if ($stmt->execute()) {
                $message = "Password changed successfully. You can now log in.";
            } else {
                $message = "Error changing password.";
            }

            $stmt->close();
        } else {
            $message = "New password and confirm password do not match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Change_password.css">
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
            <?php if (isset($_SESSION['username'])): ?>
                <a href="../User /profile.php" id="username-link" class="active">
                    <i class='bx bxs-user'></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                </a>
                <a href="../User /logout.php"><i class='bx bx-log-out'></i> Logout</a>
            <?php else: ?>
                <a href="../User /login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <section class="body">
        <main class="main">
            <h1>Change Password</h1>
            <form action="" method="POST">
                <?php if (!isset($_SESSION['username'])): ?>
                    <label for="username">Username:</label>
                    <input type="text" name="username" placeholder="Enter the username......" required>
                <?php endif; ?>

                <!-- New Password Field with Eye Icon -->
                <label for="new_password">New Password:</label>
                <div class="password-container">
                    <input type="password" id="new_password" placeholder="Enter the new passowrd......" name="new_password" required>
                    <i class='bx bx-hide togglePassword' id="toggleNewPassword"></i>
                </div>

                <!-- Confirm Password Field with Eye Icon -->
                <label for="confirm_password">Confirm New Password:</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" placeholder="Enter the new passowrd......." name="confirm_password" required>
                    <i class='bx bx-hide togglePassword' id="toggleConfirmPassword"></i>
                </div>

                <button type="submit">Change Password</button>
            </form>

            <?php if (isset($_SESSION['username'])): ?>
                <a href="../User /profile.php" class="back"><i class='bx bx-arrow-back' style="padding-right: 5px;"></i> Back to Profile</a>
            <?php else: ?>
                <a href="../User /login.php" class="back"><i class='bx bx-arrow-back' style="padding-right: 5px;"></i> Back to Login Form</a>
            <?php endif; ?>

            <?php if ($message): ?>
                <br>
                <h2><?php echo $message; ?></h2>
            <?php endif; ?>

        </main>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function togglePasswordVisibility(toggleIcon, passwordField) {
                toggleIcon.addEventListener("click", function() {
                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        toggleIcon.classList.remove("bx-hide");
                        toggleIcon.classList.add("bx-show", "active"); // Icon grows
                    } else {
                        passwordField.type = "password";
                        toggleIcon.classList.remove("bx-show", "active");
                        toggleIcon.classList.add("bx-hide");
                    }
                });
            }

            // Get elements
            const newPasswordField = document.getElementById("new_password");
            const confirmPasswordField = document.getElementById("confirm_password");
            const toggleNewPasswordIcon = document.getElementById("toggleNewPassword");
            const toggleConfirmPasswordIcon = document.getElementById("toggleConfirmPassword");

            // Attach event listeners
            togglePasswordVisibility(toggleNewPasswordIcon, newPasswordField);
            togglePasswordVisibility(toggleConfirmPasswordIcon, confirmPasswordField);
        });
    </script>


</body>

</html>

<?php
$conn->close();
?>