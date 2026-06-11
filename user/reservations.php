<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle New Reservation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_table'])) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = $_POST['guests'];

    if ($date && $time && $guests) {
        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, reservation_date, reservation_time, guest_count) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $date, $time, $guests])) {
            $message = "درخواست رزرو شما با موفقیت ثبت شد و در انتظار تأیید مدیریت است.";
        }
    }
}

// Fetch user's reservations
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC, reservation_time DESC");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="padding: 3rem 0;">
    <h2 class="section-title">رزرو میز</h2>

    <?php if ($message): ?>
        <p style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-right: 5px solid var(--success-color);">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <div style="display: flex; gap: 40px; flex-wrap: wrap;">
        <!-- Form -->
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 2rem; border-radius: 15px; box-shadow: var(--shadow); align-self: flex-start;">
            <h3>ثبت درخواست رزرو جدید</h3>
            <form action="reservations.php" method="POST" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label>تاریخ رزرو:</label>
                    <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>ساعت:</label>
                    <input type="time" name="time" required>
                </div>
                <div class="form-group">
                    <label>تعداد نفرات:</label>
                    <input type="number" name="guests" min="1" max="20" value="2" required>
                </div>
                <button type="submit" name="book_table" class="btn btn-block">ثبت درخواست رزرو</button>
            </form>
        </div>

        <!-- History -->
        <div style="flex: 2; min-width: 300px;">
            <h3>تاریخچه رزروهای شما</h3>
            <?php if (empty($reservations)): ?>
                <p style="margin-top: 1rem; padding: 20px; background: #fff; border-radius: 10px;">هنوز درخواستی ثبت نکرده‌اید.</p>
            <?php else: ?>
                <div class="table-container" style="margin-top: 1.5rem;">
                    <table>
                        <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>ساعت</th>
                                <th>نفرات</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td><?php echo $res['reservation_date']; ?></td>
                                    <td><?php echo $res['reservation_time']; ?></td>
                                    <td><?php echo $res['guest_count']; ?> نفر</td>
                                    <td>
                                        <?php if ($res['status'] == 'pending'): ?>
                                            <span style="background: #f1c40f; color: #fff; padding: 3px 10px; border-radius: 15px; font-size: 0.8rem;">در انتظار</span>
                                        <?php elseif ($res['status'] == 'approved'): ?>
                                            <span style="background: var(--success-color); color: #fff; padding: 3px 10px; border-radius: 15px; font-size: 0.8rem;">تأیید شده</span>
                                        <?php else: ?>
                                            <span style="background: var(--danger-color); color: #fff; padding: 3px 10px; border-radius: 15px; font-size: 0.8rem;">لغو شده</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
