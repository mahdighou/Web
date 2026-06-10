<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: comments.php');
    exit;
}

// Handle Reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $parent_id = $_POST['parent_id'];
    $product_id = $_POST['product_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, product_id, comment, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $comment, $parent_id]);
    }
    header('Location: comments.php');
    exit;
}

// Fetch all main comments
$stmt = $pdo->query("
    SELECT c.*, u.name as user_name, p.name as product_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN products p ON c.product_id = p.id
    WHERE c.parent_id IS NULL
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>مدیریت نظرات</h2>
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 1rem;">&larr; بازگشت به داشبورد</a>

    <div style="margin-top: 2rem;">
        <?php foreach ($comments as $c): ?>
            <div style="background: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between;">
                    <strong><?php echo htmlspecialchars($c['user_name']); ?> در مورد محصول <?php echo htmlspecialchars($c['product_name']); ?></strong>
                    <span style="color: #777;"><?php echo $c['created_at']; ?></span>
                </div>
                <div style="color: #f39c12; margin: 5px 0;">
                    <?php echo str_repeat('★', $c['rate']) . str_repeat('☆', 5 - $c['rate']); ?>
                </div>
                <p style="margin: 10px 0;"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>

                <div style="margin-top: 10px;">
                    <a href="comments.php?delete=<?php echo $c['id']; ?>" style="color: var(--danger-color);" onclick="return confirm('حذف شود؟')">حذف این نظر</a>
                </div>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                    <h5>ثبت پاسخ:</h5>
                    <form action="comments.php" method="POST" style="display: flex; gap: 10px; margin-top: 5px;">
                        <input type="hidden" name="parent_id" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $c['product_id']; ?>">
                        <input type="text" name="comment" placeholder="پاسخ خود را اینجا بنویسید..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        <button type="submit" name="reply" class="btn">ارسال پاسخ</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
