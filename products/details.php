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

<div style="padding: 2rem 0;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; border-radius: 8px;">
        </div>
        <div style="flex: 1; min-width: 300px;">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p style="margin: 1.5rem 0;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <p class="price" style="font-size: 1.5rem; margin-bottom: 2rem;"><?php echo number_format($product['price']); ?> تومان</p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="place_order.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-block" style="padding: 15px;">افزودن به سبد خرید</button>
                </form>
            <?php else: ?>
                <p style="background: #eee; padding: 10px; border-radius: 5px;">برای ثبت سفارش باید <a href="../login.php">وارد شوید</a>.</p>
            <?php endif; ?>
        </div>
    </div>

    <hr style="margin: 3rem 0; border: 1px solid #ddd;">

    <section>
        <h3>نظرات کاربران</h3>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="form-container" style="max-width: 100%; margin: 2rem 0;">
                <h4>ثبت نظر جدید</h4>
                <form action="post_comment.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="form-group">
                        <label>امتیاز (۱ تا ۵):</label>
                        <select name="rate" required>
                            <option value="5">۵ - عالی</option>
                            <option value="4">۴ - خوب</option>
                            <option value="3">۳ - معمولی</option>
                            <option value="2">۲ - ضعیف</option>
                            <option value="1">۱ - خیلی بد</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>متن نظر:</label>
                        <textarea name="comment" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn">ثبت نظر</button>
                </form>
            </div>
        <?php else: ?>
            <p style="margin: 1rem 0;">برای ثبت نظر باید <a href="../login.php">وارد شوید</a>.</p>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <?php if (empty($comments)): ?>
                <p>هنوز نظری برای این محصول ثبت نشده است.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border-right: 5px solid var(--primary-color);">
                        <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                        <span style="float: left; color: #777;"><?php echo $comment['created_at']; ?></span>
                        <div style="color: #f39c12; margin: 5px 0;">
                            <?php echo str_repeat('★', $comment['rate']) . str_repeat('☆', 5 - $comment['rate']); ?>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>

                        <?php
                        $replies = getReplies($pdo, $comment['id']);
                        foreach ($replies as $reply):
                        ?>
                            <div style="background: #f9f9f9; padding: 1rem; border-radius: 5px; margin-top: 1rem; border-right: 3px solid var(--secondary-color);">
                                <strong>پاسخ مدیر (<?php echo htmlspecialchars($reply['user_name']); ?>):</strong>
                                <p><?php echo nl2br(htmlspecialchars($reply['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
