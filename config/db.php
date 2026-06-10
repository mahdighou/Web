<?php
// Configuration for Database Connection
$host = 'localhost';
$db   = 'restaurant_db';
$user = 'root';
$pass = ''; // Default XAMPP/WAMP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // In a real project, you might want to log this instead of showing it
     // throw new \PDOException($e->getMessage(), (int)$e->getCode());
     die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

// Start Session globally
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define base path for easier includes if needed
define('BASE_PATH', dirname(__DIR__) . '/');

// Dynamically determine the base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $protocol . $domainName . ($scriptName == '/' ? '' : $scriptName);

// If we are in a subdirectory like 'admin' or 'products', we need to go up
if (basename(dirname($_SERVER['SCRIPT_NAME'])) == 'admin' || basename(dirname($_SERVER['SCRIPT_NAME'])) == 'products') {
    $baseUrl = str_replace(array('/admin', '/products'), '', $baseUrl);
}

define('BASE_URL', rtrim($baseUrl, '/') . '/');

// Global Helper Functions
function getReplies($pdo, $comment_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.name as user_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.parent_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$comment_id]);
    return $stmt->fetchAll();
}
?>
