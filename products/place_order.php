<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);

    if (!empty($product_id)) {
        // Check stock
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product && $product['stock'] >= $quantity) {
            // Add item as a pending order (cart)
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $product_id, $quantity]);

            header('Location: ../cart.php?success=1');
            exit;
        } else {
            header('Location: details.php?id=' . $product_id . '&error=stock');
            exit;
        }
    }
}

header('Location: ../index.php');
exit;
?>
