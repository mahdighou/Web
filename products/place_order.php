<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);

    if (!empty($product_id)) {
        // 1. Fetch total stock
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            $total_stock = $product['stock'];

            // 2. Check if already in 'pending' orders (cart)
            $stmt = $pdo->prepare("SELECT id, quantity FROM orders WHERE user_id = ? AND product_id = ? AND status = 'pending'");
            $stmt->execute([$user_id, $product_id]);
            $existing_order = $stmt->fetch();

            $existing_quantity = $existing_order ? $existing_order['quantity'] : 0;
            $new_total_quantity = $existing_quantity + $quantity;

            // 3. Verify total quantity doesn't exceed stock
            if ($new_total_quantity <= $total_stock) {
                if ($existing_order) {
                    // Update existing
                    $stmt = $pdo->prepare("UPDATE orders SET quantity = ? WHERE id = ?");
                    $stmt->execute([$new_total_quantity, $existing_order['id']]);
                } else {
                    // Insert new
                    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->execute([$user_id, $product_id, $quantity]);
                }
                header('Location: ' . BASE_URL . 'user/cart.php?success=1');
                exit;
            } else {
                // Not enough stock
                $available_to_add = $total_stock - $existing_quantity;
                header('Location: details.php?id=' . $product_id . '&error=stock&max=' . $available_to_add);
                exit;
            }
        }
    }
}

header('Location: ' . BASE_URL . 'index.php');
exit;
?>
