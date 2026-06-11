<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    $status = ($action == 'approve') ? 'approved' : 'cancelled';

    $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header('Location: reservations.php');
    exit;
}

// Fetch all reservations
$stmt = $pdo->query("
    SELECT r.*, u.name as user_name, u.phone
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$reservations = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>مدیریت رزروهای میز</h2>
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 1rem;">&larr; بازگشت به داشبورد</a>

    <div class="table-container" style="margin-top: 2rem;">
        <table>
            <thead style="background: var(--secondary-color); color: #fff;">
                <tr>
                    <th>مشتری</th>
                    <th>تماس</th>
                    <th>تاریخ</th>
                    <th>ساعت</th>
                    <th>نفرات</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 20px;">هیچ درخواستی ثبت نشده است.</td></tr>
                <?php endif; ?>
                <?php foreach ($reservations as $res): ?>
                    <tr style="text-align: center;">
                        <td style="padding: 15px;"><?php echo htmlspecialchars($res['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($res['phone']); ?></td>
                        <td><?php echo $res['reservation_date']; ?></td>
                        <td><?php echo $res['reservation_time']; ?></td>
                        <td><?php echo $res['guest_count']; ?> نفر</td>
                        <td>
                            <?php if ($res['status'] == 'pending'): ?>
                                <span style="color: orange;">در انتظار</span>
                            <?php elseif ($res['status'] == 'approved'): ?>
                                <span style="color: green;">تأیید شده</span>
                            <?php else: ?>
                                <span style="color: red;">لغو شده</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($res['status'] == 'pending'): ?>
                                <a href="reservations.php?action=approve&id=<?php echo $res['id']; ?>" style="color: green; margin-left: 10px;">تأیید</a>
                                <a href="reservations.php?action=cancel&id=<?php echo $res['id']; ?>" style="color: red;">لغو</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
