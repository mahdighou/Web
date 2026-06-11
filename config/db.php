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
     die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

// Start Session globally
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Dynamically determine the base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
// Calculate script name and base path accurately
$fullPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// If we are in a subdirectory, find the root
$parts = explode('/', trim($fullPath, '/'));
$subfolders = ['auth', 'user', 'products', 'admin'];
$cleanParts = [];
foreach($parts as $part) {
    if (in_array($part, $subfolders)) break;
    if ($part !== '') $cleanParts[] = $part;
}
$basePath = '/' . implode('/', $cleanParts);
$basePath = rtrim($basePath, '/') . '/';

$baseUrl = $protocol . $domainName . $basePath;
define('BASE_URL', $baseUrl);

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
