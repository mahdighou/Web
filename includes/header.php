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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>"><h1>رستوران برتر</h1></a>
            </div>

            <!-- Hamburger Button -->
            <div class="menu-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </div>

            <nav id="main-nav">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>">صفحه اصلی</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products/all.php">تمام محصولات</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo BASE_URL; ?>user/cart.php">سبد خرید و سفارشات</a></li>
                        <li><a href="<?php echo BASE_URL; ?>user/profile.php">پروفایل کاربری</a></li>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" style="color: var(--danger-color); font-weight: bold;">پنل مدیریت</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/logout.php">خروج (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/login.php">ورود</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/register.php">ثبت نام</a></li>
                    <?php endif; ?>
                </ul>
                <!-- Close Button for Mobile -->
                <div class="close-menu" onclick="toggleMobileMenu()">
                    <i class="fas fa-times"></i>
                </div>
            </nav>
        </div>
    </header>
    <div class="overlay" id="overlay" onclick="toggleMobileMenu()"></div>

    <script>
        function toggleMobileMenu() {
            const nav = document.getElementById('main-nav');
            const overlay = document.getElementById('overlay');
            nav.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>
    <main class="container">
