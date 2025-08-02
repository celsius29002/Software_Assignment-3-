<?php
/**
 * Security Headers Configuration
 * This file contains all security headers and configurations
 */

// Prevent direct access
if (!defined('SECURITY_INCLUDED')) {
    define('SECURITY_INCLUDED', true);
}

// Security Headers
function set_security_headers() {
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
           "style-src 'self' 'unsafe-inline'; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' data:; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none'; " .
           "base-uri 'self'; " .
           "form-action 'self'; " .
           "upgrade-insecure-requests;";
    
    header("Content-Security-Policy: " . $csp);
    
    // Prevent clickjacking
    header("X-Frame-Options: DENY");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Enable XSS protection
    header("X-XSS-Protection: 1; mode=block");
    
    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Permissions Policy
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    
    // Remove server information
    header("Server: ");
    
    // Cache control for sensitive pages
    if (strpos($_SERVER['REQUEST_URI'], 'login.php') !== false || 
        strpos($_SERVER['REQUEST_URI'], 'register.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false) {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}

// Session Security Configuration
function configure_session_security() {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_lifetime', 3600); // 1 hour
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// CSRF Token Management
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input Sanitization
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Rate Limiting
function check_rate_limit($action, $limit = 5, $timeframe = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
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

// Password Validation
function validate_password($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Email Validation
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
}

// SQL Injection Prevention
function prepare_sql_params($params) {
    if (is_array($params)) {
        return array_map('prepare_sql_params', $params);
    }
    return htmlspecialchars($params, ENT_QUOTES, 'UTF-8');
}

// XSS Prevention
function prevent_xss($data) {
    if (is_array($data)) {
        return array_map('prevent_xss', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Log Security Events
function log_security_event($event_type, $details = '', $severity = 'info') {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'event_type' => $event_type,
        'details' => $details,
        'severity' => $severity,
        'user_id' => $_SESSION['user_id'] ?? null
    ];
    
    error_log("SECURITY: " . json_encode($log_entry));
}

// Check for suspicious activity
function detect_suspicious_activity() {
    $suspicious = false;
    $reasons = [];
    
    // Check for SQL injection attempts
    $sql_patterns = ['union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter'];
    $input = $_GET + $_POST;
    
    foreach ($input as $key => $value) {
        if (is_string($value)) {
            $lower_value = strtolower($value);
            foreach ($sql_patterns as $pattern) {
                if (strpos($lower_value, $pattern) !== false) {
                    $suspicious = true;
                    $reasons[] = "Potential SQL injection in $key";
                    break;
                }
            }
        }
    }
    
    // Check for XSS attempts
    $xss_patterns = ['<script', 'javascript:', 'onload', 'onerror', 'onclick'];
    foreach ($input as $key => $value) {
        if (is_string($value)) {
            $lower_value = strtolower($value);
            foreach ($xss_patterns as $pattern) {
                if (strpos($lower_value, $pattern) !== false) {
                    $suspicious = true;
                    $reasons[] = "Potential XSS in $key";
                    break;
                }
            }
        }
    }
    
    if ($suspicious) {
        log_security_event('suspicious_activity', implode(', ', $reasons), 'warning');
        return true;
    }
    
    return false;
}

// Initialize security
set_security_headers();
configure_session_security();

// Check for suspicious activity
if (detect_suspicious_activity()) {
    http_response_code(403);
    die('Access denied');
}
?> 