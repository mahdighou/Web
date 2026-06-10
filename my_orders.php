<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch completed orders for the user
$stmt = $pdo->prepare("
    SELECT o.id, o.order_date, p.name as product_name, p.price, p.image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ? AND o.status = 'completed'
    ORDER BY o.order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div style="padding: 3rem 0;">
    <h2 class="section-title">تاریخچه سفارشات شما</h2>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; background: #fff; padding: 3rem; border-radius: 15px; box-shadow: var(--shadow);">
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">شما هنوز هیچ سفارشی ثبت نکرده‌اید.</p>
            <a href="index.php" class="btn">مشاهده منوی غذاها</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>تصویر</th>
                        <th>نام محصول</th>
                        <th>قیمت</th>
                        <th>تاریخ ثبت سفارش</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($order['image']); ?>" width="60" style="border-radius: 8px;">
                            </td>
                            <td><strong><?php echo htmlspecialchars($order['product_name']); ?></strong></td>
                            <td><?php echo number_format($order['price']); ?> تومان</td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td>
                                <span style="background: var(--success-color); color: #fff; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">تکمیل شده</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
