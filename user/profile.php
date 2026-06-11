<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$name, $phone, $address, $user_id])) {
        $_SESSION['user_name'] = $name;
        $success = "اطلاعات با موفقیت بروزرسانی شد.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

<div class="form-container">
    <h2>پروفایل کاربری</h2>
    <?php if ($success): ?>
        <p style="color: var(--success-color);"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="profile.php" method="POST">
        <div class="form-group">
            <label>نام و نام خانوادگی:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>ایمیل (غیرقابل ویرایش):</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>شماره تلفن:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>آدرس:</label>
            <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-block">بروزرسانی اطلاعات</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
