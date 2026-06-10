<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

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

    // Update user info if changed
    $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$phone, $address, $user_id]);

    // Mark all pending orders as completed
    $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);

    $message = "سفارش شما با موفقیت نهایی شد. از خرید شما سپاسگزاریم!";
}

// Fetch pending orders
$stmt = $pdo->prepare("
    SELECT o.id, p.name, p.price, p.image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ? AND o.status = 'pending'
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'];
}

// Fetch user info for checkout form
$stmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>سبد خرید شما</h2>

    <?php if ($message): ?>
        <p style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <?php if (empty($cart_items) && !$message): ?>
        <p>سبد خرید شما خالی است.</p>
        <a href="index.php" class="btn" style="margin-top: 1rem;">مشاهده منو غذا</a>
    <?php elseif (!empty($cart_items)): ?>
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 300px;">
                <table style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <thead style="background: var(--secondary-color); color: #fff; text-align: right;">
                        <tr>
                            <th style="padding: 15px;">تصویر</th>
                            <th style="padding: 15px;">محصول</th>
                            <th style="padding: 15px;">قیمت</th>
                            <th style="padding: 15px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px;"><img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" width="50" style="border-radius: 5px;"></td>
                                <td style="padding: 15px;"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td style="padding: 15px;"><?php echo number_format($item['price']); ?> تومان</td>
                                <td style="padding: 15px;"><a href="cart.php?remove=<?php echo $item['id']; ?>" style="color: var(--danger-color);">حذف</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="flex: 1; min-width: 300px; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); align-self: flex-start;">
                <h3>خلاصه سفارش</h3>
                <p style="margin: 1.5rem 0; font-size: 1.2rem;">مجموع: <strong><?php echo number_format($total_price); ?> تومان</strong></p>

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
</div>

<?php include 'includes/footer.php'; ?>
