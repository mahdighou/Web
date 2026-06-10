<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "لطفاً تمامی فیلدها را پر کنید.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "این ایمیل قبلاً ثبت شده است.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $success = "ثبت نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.";
            } else {
                $error = "خطایی رخ داد. مجدداً تلاش کنید.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>ثبت نام در سایت</h2>
    <?php if ($error): ?>
        <p style="color: var(--danger-color);"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: var(--success-color);"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label>نام و نام خانوادگی:</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>ایمیل:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>رمز عبور:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-block">ثبت نام</button>
    </form>
    <p style="margin-top: 1rem; text-align: center;">حساب کاربری دارید؟ <a href="login.php">وارد شوید</a></p>
</div>

<?php include 'includes/footer.php'; ?>
