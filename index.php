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
    <p>بهترین غذاها را با ما تجربه کنید</p>
</section>

<div class="product-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <div class="product-info">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                <div style="margin: 10px 0;">
                    <span class="price"><?php echo number_format($product['price']); ?> تومان</span>
                </div>
                <div style="margin-bottom: 15px;">
                    امتیاز: <?php echo $product['avg_rating'] ? round($product['avg_rating'], 1) : 'بدون امتیاز'; ?> / 5
                </div>
                <a href="products/details.php?id=<?php echo $product['id']; ?>" class="btn btn-block">مشاهده و خرید</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
