<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle removal
if (isset($_GET['remove'])) {
    $order_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$order_id, $user_id]);
    header('Location: cart.php');
    exit;
}

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // 1. Fetch pending orders to check stock one last time
    $stmt = $pdo->prepare("
        SELECT o.id, o.product_id, o.quantity, p.name, p.stock
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ? AND o.status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $pending_orders = $stmt->fetchAll();

    $can_checkout = true;
    foreach ($pending_orders as $po) {
        if ($po['stock'] < $po['quantity']) {
            $can_checkout = false;
            $error = "متأسفانه موجودی محصول '{$po['name']}' کمتر از تعداد درخواستی شماست.";
            break;
        }
    }

    if ($can_checkout) {
        // Start Transaction
        $pdo->beginTransaction();
        try {
            // Update user info
            $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$phone, $address, $user_id]);

            // Deduct stock and mark as completed
            foreach ($pending_orders as $po) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$po['quantity'], $po['product_id']]);
            }

            $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$user_id]);

            $pdo->commit();
            $message = "سفارش شما با موفقیت نهایی شد. از خرید شما سپاسگزاریم!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "خطایی در ثبت نهایی سفارش رخ داد.";
        }
    }
}

// Fetch pending orders
$stmt = $pdo->prepare("
    SELECT o.id, o.quantity, p.name, p.price, p.image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ? AND o.status = 'pending'
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += ($item['price'] * $item['quantity']);
}

// Fetch completed orders (My Orders)
$stmt = $pdo->prepare("
    SELECT o.id, o.order_date, o.quantity, p.name as product_name, p.price, p.image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ? AND o.status = 'completed'
    ORDER BY o.order_date DESC
");
$stmt->execute([$user_id]);
$completed_orders = $stmt->fetchAll();

// Fetch user info for checkout form
$stmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div style="padding: 3rem 0;">
    <h2 class="section-title">سبد خرید شما</h2>

    <?php if ($message): ?>
        <p style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-right: 5px solid var(--success-color);">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-right: 5px solid var(--danger-color);">
            <?php echo $error; ?>
        </p>
    <?php endif; ?>

    <?php if (empty($cart_items) && !$message && !$error): ?>
        <div style="text-align: center; background: #fff; padding: 3rem; border-radius: 15px; box-shadow: var(--shadow);">
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">سبد خرید شما در حال حاضر خالی است.</p>
            <a href="index.php" class="btn">مشاهده منوی غذاها</a>
        </div>
    <?php elseif (!empty($cart_items)): ?>
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 300px;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>تصویر</th>
                                <th>محصول</th>
                                <th>تعداد</th>
                                <th>قیمت واحد</th>
                                <th>جمع</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($item['image']); ?>" width="50" style="border-radius: 5px;"></td>
                                    <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo number_format($item['price']); ?> تومان</td>
                                    <td><?php echo number_format($item['price'] * $item['quantity']); ?> تومان</td>
                                    <td><a href="cart.php?remove=<?php echo $item['id']; ?>" style="color: var(--danger-color);">حذف</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="flex: 1; min-width: 300px; background: #fff; padding: 2rem; border-radius: 15px; box-shadow: var(--shadow); align-self: flex-start;">
                <h3>خلاصه سفارش</h3>
                <div style="display: flex; justify-content: space-between; margin: 1.5rem 0; font-size: 1.2rem;">
                    <span>جمع کل:</span>
                    <span style="font-weight: 700; color: var(--primary-color);"><?php echo number_format($total_price); ?> تومان</span>
                </div>

                <hr style="margin-bottom: 1.5rem; border: 0.5px solid #eee;">

                <h4>اطلاعات تحویل</h4>
                <form action="cart.php" method="POST">
                    <div class="form-group">
                        <label>شماره تماس:</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>آدرس دقیق:</label>
                        <textarea name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="checkout" class="btn btn-block">تکمیل فرایند خرید</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- History Section -->
    <?php if (!empty($completed_orders)): ?>
        <h2 class="section-title" style="margin-top: 5rem;">تاریخچه سفارشات نهایی شده</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>تصویر</th>
                        <th>نام محصول</th>
                        <th>تعداد</th>
                        <th>قیمت واحد</th>
                        <th>تاریخ ثبت</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed_orders as $order): ?>
                        <tr>
                            <td><img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($order['image']); ?>" width="50" style="border-radius: 5px;"></td>
                            <td><strong><?php echo htmlspecialchars($order['product_name']); ?></strong></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td><?php echo number_format($order['price']); ?> تومان</td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td><span style="background: var(--success-color); color: #fff; padding: 3px 10px; border-radius: 15px; font-size: 0.8rem;">تکمیل شده</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
