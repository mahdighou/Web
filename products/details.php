<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ../index.php');
    exit;
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("محصول یافت نشد.");
}

// Fetch comments (main comments)
$stmt = $pdo->prepare("
    SELECT c.*, u.name as user_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.product_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

// Function to fetch replies
function getReplies($pdo, $comment_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.name as user_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.parent_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$comment_id]);
    return $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div style="padding: 3rem 0;">
    <div style="display: flex; gap: 40px; flex-wrap: wrap; background: #fff; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow);">
        <div style="flex: 1; min-width: 300px;">
            <img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; border-radius: 15px; box-shadow: var(--shadow);">
        </div>
        <div style="flex: 1.2; min-width: 300px; display: flex; flex-direction: column; justify-content: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--secondary-color);"><?php echo htmlspecialchars($product['name']); ?></h2>
            <div style="margin-bottom: 1.5rem;">
                <span class="price" style="font-size: 2rem;"><?php echo number_format($product['price']); ?> تومان</span>
            </div>
            <p style="font-size: 1.1rem; color: #57606f; margin-bottom: 2.5rem; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="place_order.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-block" style="padding: 18px; font-size: 1.2rem;">افزودن به سبد خرید</button>
                </form>
            <?php else: ?>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-right: 5px solid var(--primary-color);">
                    برای ثبت سفارش و خرید این محصول باید ابتدا <a href="<?php echo BASE_URL; ?>login.php" style="color: var(--primary-color); font-weight: bold;">وارد حساب کاربری</a> خود شوید.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 4rem;">
        <h3 class="section-title" style="text-align: right; margin-bottom: 2rem;">نظرات مشتریان</h3>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="background: #fff; padding: 2rem; border-radius: 15px; box-shadow: var(--shadow); margin-bottom: 3rem;">
                <h4 style="margin-bottom: 1.5rem;">ثبت نظر جدید</h4>
                <form action="post_comment.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 150px;">
                            <label>امتیاز شما:</label>
                            <select name="rate" required>
                                <option value="5">★★★★★ - عالی</option>
                                <option value="4">★★★★☆ - خوب</option>
                                <option value="3">★★★☆☆ - معمولی</option>
                                <option value="2">★★☆☆☆ - ضعیف</option>
                                <option value="1">★☆☆☆☆ - خیلی بد</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 4; min-width: 250px;">
                            <label>متن نظر:</label>
                            <textarea name="comment" rows="3" required placeholder="تجربه خود را از این غذا بنویسید..."></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="min-width: 150px;">ثبت نظر</button>
                </form>
            </div>
        <?php else: ?>
            <p style="background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 2rem;">برای ثبت نظر باید <a href="<?php echo BASE_URL; ?>login.php" style="color: var(--primary-color);">وارد شوید</a>.</p>
        <?php endif; ?>

        <div style="display: grid; gap: 20px;">
            <?php if (empty($comments)): ?>
                <p style="text-align: center; color: #777; padding: 2rem; background: #fff; border-radius: 10px;">هنوز نظری برای این محصول ثبت نشده است. اولین نفری باشید که نظر می‌دهد!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div style="background: #fff; padding: 1.5rem; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-right: 6px solid var(--primary-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                            <span style="color: #a4b0be; font-size: 0.8rem;"><?php echo $comment['created_at']; ?></span>
                        </div>
                        <div style="color: #f1c40f; margin-bottom: 1rem; font-size: 1rem;">
                            <?php echo str_repeat('★', $comment['rate']) . str_repeat('☆', 5 - $comment['rate']); ?>
                        </div>
                        <p style="color: #2f3542; line-height: 1.7;"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>

                        <?php
                        $replies = getReplies($pdo, $comment['id']);
                        foreach ($replies as $reply):
                        ?>
                            <div style="background: #f1f2f6; padding: 1.2rem; border-radius: 10px; margin-top: 1.5rem; border-right: 4px solid var(--secondary-color);">
                                <div style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                                    <strong>پاسخ مدیر (<?php echo htmlspecialchars($reply['user_name']); ?>):</strong>
                                    <span style="color: #a4b0be; font-size: 0.8rem;"><?php echo $reply['created_at']; ?></span>
                                </div>
                                <p style="color: #57606f;"><?php echo nl2br(htmlspecialchars($reply['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
