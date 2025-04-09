<?php
session_start();

// Show all errors during development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'product_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate the request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['stock_update_id'])) {
    $stockUpdateId = intval($_POST['stock_update_id']);

    // Step 1: Get the product ID and stock quantity from stock_updates
    $query = "SELECT product_id, added_stock FROM stock_updates WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $stockUpdateId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($productId, $addedStock);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        $stmt->close();

        // Step 2: Subtract stock from products table
        $updateStockQuery = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $conn->prepare($updateStockQuery);
        $stmt->bind_param("iii", $addedStock, $productId, $addedStock);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();

            // Step 3: Delete the stock update entry
            $deleteQuery = "DELETE FROM stock_updates WHERE id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $stockUpdateId);

            if ($stmt->execute()) {
                $stmt->close();
                $_SESSION['message'] = "Stock update deleted and stock reduced from main product.";
                header("Location: stock_summary.php");
                exit();
            } else {
                $stmt->close();
                $_SESSION['error'] = "Failed to delete stock update entry.";
                header("Location: stock_summary.php");
                exit();
            }
        } else {
            $stmt->close();
            $_SESSION['error'] = "Not enough stock to subtract or failed to update.";
            header("Location: stock_summary.php");
            exit();
        }
    } else {
        $stmt->close();
        $_SESSION['error'] = "Stock update record not found.";
        header("Location: stock_summary.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: stock_summary.php");
    exit();
}
?>
