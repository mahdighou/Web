<?php
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Helper to get first admin ID
function getAdminId($pdo) {
    return $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
}

switch ($action) {
    case 'send':
        $message = $_POST['message'] ?? '';
        $receiver_id = $_POST['receiver_id'] ?? null;

        if ($role !== 'admin') {
            $receiver_id = getAdminId($pdo);
        }

        if ($message && $receiver_id) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $receiver_id, $message]);
            echo json_encode(['success' => true]);
        }
        break;

    case 'fetch_messages':
        $chat_partner_id = $_GET['user_id'] ?? null;

        if ($role !== 'admin') {
            $chat_partner_id = getAdminId($pdo);
        }

        if ($chat_partner_id) {
            // Mark as read
            $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$chat_partner_id, $user_id]);

            $stmt = $pdo->prepare("
                SELECT * FROM messages
                WHERE (sender_id = ? AND receiver_id = ?)
                OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC
            ");
            $stmt->execute([$user_id, $chat_partner_id, $chat_partner_id, $user_id]);
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'fetch_users':
        if ($role === 'admin') {
            $stmt = $pdo->prepare("
                SELECT u.id, u.name,
                (SELECT COUNT(*) FROM messages m WHERE m.sender_id = u.id AND m.receiver_id = ? AND m.is_read = 0) as unread_count
                FROM users u
                WHERE u.role != 'admin'
                AND EXISTS (SELECT 1 FROM messages m WHERE m.sender_id = u.id OR m.receiver_id = u.id)
                ORDER BY (SELECT MAX(created_at) FROM messages m WHERE m.sender_id = u.id OR m.receiver_id = u.id) DESC
            ");
            $stmt->execute([$user_id]);
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'check_new':
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        echo json_encode(['new_messages' => $stmt->fetchColumn()]);
        break;
}
?>
