<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $comment = $_POST['comment'];
    $rate = $_POST['rate'];

    if (!empty($comment) && !empty($product_id)) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, product_id, comment, rate) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $comment, $rate]);
    }

    header("Location: details.php?id=" . $product_id);
    exit;
}
?>
