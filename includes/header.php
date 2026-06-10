<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رستوران آنلاین</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>"><h1>رستوران برتر</h1></a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>">صفحه اصلی</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo BASE_URL; ?>cart.php">سبد خرید</a></li>
                        <li><a href="<?php echo BASE_URL; ?>profile.php">پروفایل کاربری</a></li>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" style="color: var(--danger-color); font-weight: bold;">پنل مدیریت</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_URL; ?>logout.php">خروج (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>login.php">ورود</a></li>
                        <li><a href="<?php echo BASE_URL; ?>register.php">ثبت نام</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
