<?php
require_once 'config/db.php';

// Fetch products with average rating
$query = "
    SELECT p.*, AVG(c.rate) as avg_rating
    FROM products p
    LEFT JOIN comments c ON p.id = c.product_id AND c.parent_id IS NULL
    GROUP BY p.id
";
$stmt = $pdo->query($query);
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="hero">
    <h2>لذت یک غذای خوشمزه</h2>
    <p>بهترین غذاها را با ما در سریع‌ترین زمان تجربه کنید</p>
</section>

<h2 class="section-title">منوی غذاهای محبوب</h2>

<div class="product-grid">
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

<?php include 'includes/footer.php'; ?>
