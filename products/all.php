<?php
require_once '../config/db.php';

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

// Base Query
$query = "
    SELECT p.*, AVG(c.rate) as avg_rating
    FROM products p
    LEFT JOIN comments c ON p.id = c.product_id AND c.parent_id IS NULL
    WHERE p.name LIKE ? OR p.description LIKE ?
    GROUP BY p.id
";

// Sorting logic
switch ($sort) {
    case 'price_asc': $query .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $query .= " ORDER BY p.price DESC"; break;
    case 'rate_desc': $query .= " ORDER BY avg_rating DESC"; break;
    case 'name_desc': $query .= " ORDER BY p.name DESC"; break;
    default: $query .= " ORDER BY p.name ASC"; break;
}

$stmt = $pdo->prepare($query);
$stmt->execute(['%'.$search.'%', '%'.$search.'%']);
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2 class="section-title">تمام محصولات</h2>

    <!-- Search and Filter -->
    <div style="background: #fff; padding: 20px; border-radius: 15px; box-shadow: var(--shadow); margin-bottom: 2rem;">
        <form action="all.php" method="GET" style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
                <label>جستجوی محصول:</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="نام غذا...">
            </div>
            <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                <label>مرتب‌سازی بر اساس:</label>
                <select name="sort">
                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>نام (الفبا)</option>
                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>نام (معکوس)</option>
                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>قیمت (ارزان‌ترین)</option>
                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>قیمت (گران‌ترین)</option>
                    <option value="rate_desc" <?php echo $sort == 'rate_desc' ? 'selected' : ''; ?>>امتیاز (بیشترین)</option>
                </select>
            </div>
            <button type="submit" class="btn" style="height: 45px;">اعمال فیلتر</button>
        </form>
    </div>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p style="text-align: center; grid-column: 1/-1; padding: 3rem;">محصولی با این مشخصات یافت نشد.</p>
        <?php endif; ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars(mb_substr($product['description'], 0, 80, 'UTF-8')) . '...'; ?></p>
                    <div class="price-rating">
                        <span class="price"><?php echo number_format($product['price']); ?> تومان</span>
                        <span class="rating">
                            <?php
                            $rating = $product['avg_rating'] ? round($product['avg_rating'], 1) : 0;
                            echo $rating > 0 ? "★ $rating" : "بدون امتیاز";
                            ?>
                        </span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>products/details.php?id=<?php echo $product['id']; ?>" class="btn btn-block">مشاهده و سفارش</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
