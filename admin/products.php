<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php');
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $_POST['image'];
    $id = $_POST['id'] ?? null;

    $stock = (int)($_POST['stock'] ?? 0);

    if ($id) {
        // Edit
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $image, $stock, $id]);
        $success = "محصول با موفقیت ویرایش شد.";
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $image, $stock]);
        $success = "محصول جدید با موفقیت اضافه شد.";
    }
}

// Fetch all products
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

// If editing, fetch that product
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>مدیریت محصولات</h2>
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 1rem;">&larr; بازگشت به داشبورد</a>

    <?php if ($success): ?>
        <p style="color: var(--success-color); background: #e9f7ef; padding: 10px; border-radius: 5px;"><?php echo $success; ?></p>
    <?php endif; ?>

    <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 2rem;">
        <!-- Form -->
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); align-self: flex-start;">
            <h3><?php echo $edit_product ? 'ویرایش محصول' : 'افزودن محصول جدید'; ?></h3>
            <form action="products.php" method="POST">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>نام محصول:</label>
                    <input type="text" name="name" value="<?php echo $edit_product['name'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>قیمت (تومان):</label>
                    <input type="number" name="price" value="<?php echo $edit_product['price'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>توضیحات:</label>
                    <textarea name="description" rows="4" required><?php echo $edit_product['description'] ?? ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>نام فایل تصویر (مثلاً 1.jpg):</label>
                    <input type="text" name="image" value="<?php echo $edit_product['image'] ?? ''; ?>" required>
                    <small>نکته: تصاویر باید در پوشه assets/images موجود باشند.</small>
                </div>
                <div class="form-group">
                    <label>موجودی انبار:</label>
                    <input type="number" name="stock" value="<?php echo $edit_product['stock'] ?? '0'; ?>" required>
                </div>
                <button type="submit" class="btn btn-block"><?php echo $edit_product ? 'بروزرسانی محصول' : 'ثبت محصول'; ?></button>
                <?php if ($edit_product): ?>
                    <a href="products.php" class="btn btn-block" style="background: #777; margin-top: 10px;">انصراف</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- List -->
        <div style="flex: 2; min-width: 300px;">
            <table style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <thead style="background: var(--secondary-color); color: #fff;">
                    <tr>
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">نام</th>
                        <th style="padding: 10px;">قیمت</th>
                        <th style="padding: 10px;">موجودی</th>
                        <th style="padding: 10px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr style="border-bottom: 1px solid #eee; text-align: center;">
                            <td style="padding: 10px;"><?php echo $p['id']; ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td style="padding: 10px;"><?php echo number_format($p['price']); ?></td>
                            <td style="padding: 10px;"><?php echo $p['stock']; ?></td>
                            <td style="padding: 10px;">
                                <a href="products.php?edit=<?php echo $p['id']; ?>" style="color: blue; margin-left: 10px;">ویرایش</a>
                                <a href="products.php?delete=<?php echo $p['id']; ?>" style="color: var(--danger-color);" onclick="return confirm('آیا از حذف این محصول مطمئن هستید؟')">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
