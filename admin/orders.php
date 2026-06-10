<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Fetch all completed orders
$stmt = $pdo->query("
    SELECT o.id, o.order_date, o.quantity, u.name as user_name, u.phone, u.address, p.name as product_name, p.price
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    WHERE o.status = 'completed'
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>گزارش سفارشات نهایی</h2>
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 1rem;">&larr; بازگشت به داشبورد</a>

    <div style="margin-top: 2rem; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <thead style="background: var(--secondary-color); color: #fff;">
                <tr>
                    <th style="padding: 15px;">ID</th>
                    <th style="padding: 15px;">تاریخ</th>
                    <th style="padding: 15px;">مشتری</th>
                    <th style="padding: 15px;">تماس</th>
                    <th style="padding: 15px;">محصول</th>
                    <th style="padding: 15px;">تعداد</th>
                    <th style="padding: 15px;">مبلغ کل</th>
                    <th style="padding: 15px;">آدرس</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" style="padding: 20px; text-align: center;">هیچ سفارشی ثبت نشده است.</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $o): ?>
                    <tr style="border-bottom: 1px solid #eee; text-align: center;">
                        <td style="padding: 15px;"><?php echo $o['id']; ?></td>
                        <td style="padding: 15px;"><?php echo $o['order_date']; ?></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($o['user_name']); ?></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($o['phone']); ?></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($o['product_name']); ?></td>
                        <td style="padding: 15px;"><?php echo $o['quantity']; ?></td>
                        <td style="padding: 15px;"><?php echo number_format($o['price'] * $o['quantity']); ?></td>
                        <td style="padding: 15px; text-align: right;"><?php echo htmlspecialchars($o['address']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
