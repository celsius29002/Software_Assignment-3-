<?php
// Include security headers and functions
require_once 'security_headers.php';

// Database configuration
$host = 'localhost';
$dbname = 'ruraledu_db';
$username = 'root';  // MAMP default username
$password_db = 'root';    // MAMP default password (try 'root' if empty doesn't work)

// Application settings
define('SITE_NAME', 'RuralEdu');
define('SITE_URL', 'http://localhost/ruraledu'); // Change this to your site URL
define('ADMIN_EMAIL', 'admin@ruraledu.com');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Australia/Sydney');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: index.html");
        exit();
    }
}

// Database connection function
function get_db_connection() {
    global $host, $dbname, $username, $password_db;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

// Logging function
function log_activity($user_id, $action, $details = '') {
    try {
        $pdo = get_db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
        }
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Password validation function
function validate_password($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Email validation function
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Rate limiting function
function check_rate_limit($action, $limit = 5, $timeframe = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $key = "rate_limit_{$action}_{$ip}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeframe];
    }
    
    if (time() > $_SESSION[$key]['reset_time']) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeframe];
    }
    
    if ($_SESSION[$key]['count'] >= $limit) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}
?> 