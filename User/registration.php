<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once('../Database/db_conn.php');

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conform_password = $_POST['conform_password'];

    if ($password == $conform_password) {
        // Prepare the SQL statement
        $stmt = $connection->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

        // Store the password in plaintext (not recommended)
        // No hashing is done here
        $plaintextPassword = $password;

        // Bind parameters
        $stmt->bind_param("sss", $username, $email, $plaintextPassword); // Use $plaintextPassword here

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            header('Location: ../User /login.php');
            exit(); // Always exit after a header redirect
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Password and Confirm Password are not the same.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/registraction_Form.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <header class="header">
        <h3 class="logo">Product Management System</h3>

        <nav class="navbar">
            <a href="../Layout/home.php">Home</a>
            <a href="../Product/product.php">Product</a>
            <a href="../Category/category.php">Category</a>
            <a href="../Brand/brand.php">Brand</a>
            <a href="../Stocks/view_stock.php">Stocks</a>
            <a href="../Buyer/buyer.php">Buyer</a>
            <a href="../Billing/billing.php">Billing</a>
        </nav>
    </header>

    <script src="../JS/script.js"></script>

    <section class="body">
        <main class="main">
            <h1>Register Form</h1><br>
            <form action="../User /registration.php" method="POST">
                <label for="username">Username : </label><br>
                <input type="text" name="username" placeholder="Username" required><br><br>
                <label for="email">Email : </label><br>
                <input type="email" name="email" placeholder="Email" required><br><br>
                <!-- Password Field with Eye Icon -->
                <label for="password">Password : </label><br>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Enter your password......." required>
                    <i class='bx bx-hide togglePassword' id="togglePassword"></i>
                </div><br><br>

                <!-- Confirm Password Field with Eye Icon -->
                <label for="conform_password">Confirm Password : </label><br>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="conform_password" placeholder="Confirm Password" required>
                    <i class='bx bx-hide togglePassword' id="toggleConfirmPassword"></i>
                </div><br><br>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <br><a href="../User /login.php">Login here</a></p>
        </main>
    </section>

    <script src="../JS/script.js"></script>
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
        const passwordField = document.getElementById("password");
        const confirmPasswordField = document.getElementById("confirm_password");
        const togglePasswordIcon = document.getElementById("togglePassword");
        const toggleConfirmPasswordIcon = document.getElementById("toggleConfirmPassword");

        // Attach event listeners
        togglePasswordVisibility(togglePasswordIcon, passwordField);
        togglePasswordVisibility(toggleConfirmPasswordIcon, confirmPasswordField);
    });
</script>


</body>

</html>