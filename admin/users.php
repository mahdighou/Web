<?php
require_once '../config/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$success = '';
$error = '';

// Handle Delete User
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Prevent self-deletion
    if ($delete_id == $_SESSION['user_id']) {
        $error = "شما نمی‌توانید حساب کاربری خودتان را حذف کنید!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = "کاربر با موفقیت حذف شد.";
    }
}

// Handle POST Add or Edit Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if ($action === 'add') {
        if (empty($name) || empty($email) || empty($password)) {
            $error = "لطفاً تمامی فیلدهای الزامی (نام، ایمیل و رمز عبور) را وارد کنید.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing_user = $stmt->fetch();

            if ($existing_user) {
                // Change form to edit mode for this user
                $_GET['edit'] = $existing_user['id'];
                $error = "این ایمیل قبلاً ثبت شده است. اکنون در حال ویرایش اطلاعات این کاربر هستید.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed, $phone, $address, $role]);
                $success = "کاربر جدید با موفقیت اضافه شد.";
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        if (empty($name) || empty($email)) {
            $error = "نام و ایمیل نمی‌توانند خالی باشند.";
        } else {
            // Check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $error = "این ایمیل قبلاً توسط کاربر دیگری ثبت شده است.";
            } else {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, phone = ?, address = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $hashed, $phone, $address, $role, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $address, $role, $id]);
                }

                // If editing own account, update session
                if ($id == $_SESSION['user_id']) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['role'] = $role;
                }

                $success = "اطلاعات کاربر با موفقیت ویرایش شد.";
            }
        }
    }
}

// Fetch all users with their pending cart items count
$users = $pdo->query("
    SELECT u.*,
           (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id AND o.status = 'pending') AS cart_count
    FROM users u
    ORDER BY u.id DESC
")->fetchAll();

// If editing, fetch that user
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch();
}

// If viewing cart, fetch cart details
$cart_user = null;
$cart_items = [];
$cart_total = 0;
if (isset($_GET['cart'])) {
    $cart_user_id = $_GET['cart'];
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$cart_user_id]);
    $cart_user = $stmt->fetch();

    if ($cart_user) {
        $stmt = $pdo->prepare("
            SELECT o.id, o.quantity, p.name as product_name, p.price, p.image
            FROM orders o
            JOIN products p ON o.product_id = p.id
            WHERE o.user_id = ? AND o.status = 'pending'
        ");
        $stmt->execute([$cart_user_id]);
        $cart_items = $stmt->fetchAll();

        foreach ($cart_items as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
    }
}

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>مدیریت کاربران</h2>
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 1rem;">&larr; بازگشت به داشبورد</a>

    <?php if ($success): ?>
        <p style="color: var(--success-color); background: #e9f7ef; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-right: 5px solid var(--success-color);"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: var(--danger-color); background: #fdf2f2; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-right: 5px solid var(--danger-color);"><?php echo $error; ?></p>
    <?php endif; ?>

    <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 2rem;">
        <!-- Left Side: Forms (Edit / Add / Cart View) -->
        <div style="flex: 1.2; min-width: 320px; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); align-self: flex-start;">

            <?php if ($cart_user): ?>
                <!-- Cart View Mode -->
                <h3>سبد خرید کاربر: <?php echo htmlspecialchars($cart_user['name']); ?></h3>
                <div style="margin-top: 1.5rem;">
                    <?php if (empty($cart_items)): ?>
                        <p style="text-align: center; color: #777; padding: 20px 0;">سبد خرید این کاربر در حال حاضر خالی است.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto; margin-bottom: 1.5rem;">
                            <?php foreach ($cart_items as $item): ?>
                                <div style="display: flex; gap: 15px; border-bottom: 1px solid #eee; padding: 10px 0; align-items: center;">
                                    <img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($item['image']); ?>" width="55" height="55" style="border-radius: 8px; object-fit: cover;">
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <p style="margin: 3px 0 0; color: var(--primary-color); font-size: 0.85rem; font-weight: bold;">
                                            <?php echo number_format($item['price']); ?> تومان &times; <?php echo $item['quantity']; ?>
                                        </p>
                                    </div>
                                    <div style="text-align: left; font-weight: bold; font-size: 0.9rem;">
                                        <?php echo number_format($item['price'] * $item['quantity']); ?> تومان
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem; border-top: 2px solid #eee; padding-top: 15px; margin-bottom: 1.5rem;">
                            <span>جمع کل سبد خرید:</span>
                            <span style="color: var(--primary-color);"><?php echo number_format($cart_total); ?> تومان</span>
                        </div>
                    <?php endif; ?>
                    <a href="users.php" class="btn btn-block" style="background: var(--secondary-color); color: white; text-align: center;">بستن سبد خرید</a>
                </div>

            <?php else: ?>
                <!-- Unified Add/Edit User Form -->
                <h3><?php echo $edit_user ? 'ویرایش اطلاعات کاربر' : 'افزودن کاربر جدید'; ?></h3>
                <form action="users.php" method="POST" style="margin-top: 1.5rem;">
                    <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>نام و نام خانوادگی:</label>
                        <input type="text" name="name" value="<?php echo $edit_user ? htmlspecialchars($edit_user['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>ایمیل:</label>
                        <input type="email" name="email" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>رمز عبور <?php echo $edit_user ? '(در صورت عدم تغییر، خالی بگذارید)' : ''; ?>:</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" <?php echo $edit_user ? '' : 'required'; ?>>
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>شماره تلفن:</label>
                        <input type="text" name="phone" value="<?php echo $edit_user ? htmlspecialchars($edit_user['phone'] ?? '') : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>آدرس:</label>
                        <textarea name="address" rows="3"><?php echo $edit_user ? htmlspecialchars($edit_user['address'] ?? '') : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>نقش:</label>
                        <select name="role" required>
                            <option value="user" <?php echo ($edit_user && $edit_user['role'] === 'user') ? 'selected' : ''; ?>>کاربر عادی</option>
                            <option value="admin" <?php echo ($edit_user && $edit_user['role'] === 'admin') ? 'selected' : ''; ?>>مدیر</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-block"><?php echo $edit_user ? 'ذخیره تغییرات' : 'ثبت کاربر جدید'; ?></button>
                    <?php if ($edit_user): ?>
                        <a href="users.php" class="btn btn-block" style="background: #777; margin-top: 10px; color: white; text-align: center;">انصراف</a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

        </div>

        <!-- Right Side: User List -->
        <div style="flex: 2; min-width: 300px;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: center;">ID</th>
                            <th>نام و نام خانوادگی</th>
                            <th>ایمیل</th>
                            <th>شماره تلفن</th>
                            <th>آدرس</th>
                            <th style="text-align: center;">نقش</th>
                            <th style="text-align: center; min-width: 220px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr style="text-align: center; <?php echo (($edit_user && $edit_user['id'] == $u['id']) || ($cart_user && $_GET['cart'] == $u['id'])) ? 'background: #f8f9fa;' : ''; ?>">
                                <td><?php echo $u['id']; ?></td>
                                <td style="text-align: right;"><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                <td style="text-align: right;"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td style="text-align: right;"><?php echo $u['phone'] ? htmlspecialchars($u['phone']) : '-'; ?></td>
                                <td style="text-align: right; font-size: 0.9rem;"><?php echo $u['address'] ? htmlspecialchars($u['address']) : '-'; ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span style="background: var(--danger-color); color: #fff; padding: 3px 8px; border-radius: 5px; font-size: 0.8rem;">مدیر</span>
                                    <?php else: ?>
                                        <span style="background: var(--secondary-color); color: #fff; padding: 3px 8px; border-radius: 5px; font-size: 0.8rem;">کاربر عادی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px; justify-content: center; align-items: center;">
                                        <a href="users.php?edit=<?php echo $u['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #007bff;">ویرایش</a>

                                        <!-- Dynamic shopping cart button color -->
                                        <?php if ($u['cart_count'] > 0): ?>
                                            <a href="users.php?cart=<?php echo $u['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: var(--success-color); color: #fff;">سبد خرید</a>
                                        <?php else: ?>
                                            <a href="users.php?cart=<?php echo $u['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #ffc107; color: #333;">سبد خرید</a>
                                        <?php endif; ?>

                                        <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: var(--danger-color);" onclick="return confirm('آیا از حذف این کاربر مطمئن هستید؟')">حذف</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    }
</script>

<?php include '../includes/footer.php'; ?>
