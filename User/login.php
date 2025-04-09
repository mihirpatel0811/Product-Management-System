<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once('../Database/db_conn.php');

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement
    $stmt = $connection->prepare("SELECT password FROM users WHERE username = ?");

    // Bind parameters
    $stmt->bind_param("s", $username);

    // Execute the statement
    $stmt->execute();

    // Store the result
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows == 1) {
        // Bind the result to a variable
        $stmt->bind_result($stored_password);
        $stmt->fetch();

        // Verify the password (no hashing, direct comparison)
        if ($password === $stored_password) {
            $_SESSION['username'] = $username;
            header('Location: ../Layout/home.php');
            exit(); // Always exit after a header redirect
        } else {
            echo "Invalid Username or Password";
        }
    } else {
        echo "Invalid Username or Password";
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="icon" type="image/png" href="../Images/website_logo.png">
    <link rel="stylesheet" href="../CSS/Login_Form.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <header class="header">
        <h3 class="logo">Product Management System</h3>

        <nav class="navbar">
            <a href="../Layout/home.php" class="active">Home</a>
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
            <h1>Login Here</h1><br>
            <form action="../User /login.php" method="post">
                <label for="username">Username : </label><br>
                <input type="text" name="username" placeholder="Enter your username......." required><br><br>
                <label for="password">Password : </label><br>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Enter your password......." required>
                    <i class='bx bx-hide' id="togglePassword"></i>
                </div><br><br>
                <button type="submit">Login</button>
            </form>
            <p>You don't have a registration?
                <a href="../User /registration.php">
                    Registration Here
                </a>
            </p>
            <a href="../User /change_password.php" class="change-password-btn">Forget Password</a>
        </main>
    </section>

    <script>
        document.getElementById("togglePassword").addEventListener("click", function() {
            var passwordField = document.getElementById("password");
            var icon = this;

            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("bx-hide");
                icon.classList.add("bx-show", "active"); // Add 'active' to grow the icon
            } else {
                passwordField.type = "password";
                icon.classList.remove("bx-show", "active");
                icon.classList.add("bx-hide");
            }
        });
    </script>



</body>

</html>