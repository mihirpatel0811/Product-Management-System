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

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        header("Location: ../Product/product.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Error: ID not set";
    exit();
}

$stmt->close();
$conn->close();