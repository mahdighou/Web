<?php
require_once '../config/db.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_reservations = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>پنل مدیریت</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 2rem;">
        <div style="background: #fff; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>کاربران</h3>
            <p style="font-size: 2rem; color: var(--primary-color);"><?php echo $total_users; ?></p>
        </div>
        <div style="background: #fff; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>محصولات</h3>
            <p style="font-size: 2rem; color: var(--primary-color);"><?php echo $total_products; ?></p>
        </div>
        <div style="background: #fff; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>سفارشات نهایی</h3>
            <p style="font-size: 2rem; color: var(--primary-color);"><?php echo $total_orders; ?></p>
        </div>
        <div style="background: #fff; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>نظرات</h3>
            <p style="font-size: 2rem; color: var(--primary-color);"><?php echo $total_comments; ?></p>
        </div>
        <div style="background: #fff; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>رزروهای جدید</h3>
            <p style="font-size: 2rem; color: var(--primary-color);"><?php echo $total_reservations; ?></p>
        </div>
    </div>

    <div style="margin-top: 3rem; display: flex; gap: 20px; flex-wrap: wrap;">
        <a href="products.php" class="btn" style="flex: 1; text-align: center; background: var(--secondary-color);">مدیریت محصولات</a>
        <a href="comments.php" class="btn" style="flex: 1; text-align: center; background: var(--secondary-color);">مدیریت نظرات</a>
        <a href="orders.php" class="btn" style="flex: 1; text-align: center; background: var(--secondary-color);">گزارش سفارشات</a>
        <a href="reservations.php" class="btn" style="flex: 1; text-align: center; background: var(--secondary-color);">مدیریت رزرو میز</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
