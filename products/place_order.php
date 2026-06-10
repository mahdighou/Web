<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];

    if (!empty($product_id)) {
        // Add item as a pending order (cart)
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $product_id]);

        header('Location: ../cart.php?success=1');
        exit;
    }
}

header('Location: ../index.php');
exit;
?>
