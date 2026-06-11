<?php
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "لطفاً ایمیل و رمز عبور را وارد کنید.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $error = "ایمیل یا رمز عبور اشتباه است.";
        }
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h2>ورود به حساب کاربری</h2>
    <?php if ($error): ?>
        <p style="color: var(--danger-color);"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>ایمیل:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>رمز عبور:</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <i class="fas fa-eye" id="togglePassword"></i>
            </div>
        </div>
        <button type="submit" class="btn btn-block">ورود</button>
    </form>
    <p style="margin-top: 1rem; text-align: center;">حساب کاربری ندارید؟ <a href="register.php">ثبت نام کنید</a></p>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

<?php include '../includes/footer.php'; ?>
