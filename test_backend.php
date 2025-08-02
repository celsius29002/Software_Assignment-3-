<?php
// Comprehensive Backend Test Script
// This will test all backend functionality

echo "<h1>🔧 RuralEdu Backend Test Results</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .error { color: red; background: #f8d7da; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .warning { color: orange; background: #fff3cd; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .info { color: blue; background: #d1ecf1; padding: 10px; margin: 5px 0; border-radius: 5px; }
</style>";

// Test 1: PHP Version
echo "<h2>1. PHP Environment</h2>";
echo "<div class='info'>PHP Version: " . phpversion() . "</div>";
echo "<div class='info'>Server Software: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</div>";

// Test 2: Required Extensions
echo "<h2>2. Required Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'session', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✅ $ext extension is loaded</div>";
    } else {
        echo "<div class='error'>❌ $ext extension is NOT loaded</div>";
    }
}

// Test 3: Database Connection
echo "<h2>3. Database Connection</h2>";
try {
    $host = 'localhost';
    $username = 'root';
    $password = 'root';
    $dbname = 'ruraledu_db';
    
    // Test connection without database first
    $pdo = new PDO("mysql:host=$host", $username, $password);
    echo "<div class='success'>✅ MySQL server connection successful</div>";
    
    // Test connection to specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "<div class='success'>✅ Database '$dbname' connection successful</div>";
    
    // Test if tables exist
    $tables = ['users', 'user_profiles', 'user_progress', 'user_assignments', 'activity_logs'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='error'>❌ Table '$table' does NOT exist</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
}

// Test 4: Session Functionality
echo "<h2>4. Session Functionality</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✅ Sessions are working</div>";
    
    // Test session variables
    $_SESSION['test_var'] = 'test_value';
    if (isset($_SESSION['test_var']) && $_SESSION['test_var'] === 'test_value') {
        echo "<div class='success'>✅ Session variables are working</div>";
    } else {
        echo "<div class='error'>❌ Session variables are NOT working</div>";
    }
} else {
    echo "<div class='error'>❌ Sessions are NOT working</div>";
}

// Test 5: File Permissions
echo "<h2>5. File Permissions</h2>";
$files_to_check = [
    'config.php',
    'login.php',
    'dashboard.php',
    'setup_db.php',
    'styles.css',
    'script.js'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<div class='success'>✅ $file exists and is readable</div>";
        } else {
            echo "<div class='error'>❌ $file exists but is NOT readable</div>";
        }
    } else {
        echo "<div class='error'>❌ $file does NOT exist</div>";
    }
}

// Test 6: Config File Functions
echo "<h2>6. Config File Functions</h2>";
if (file_exists('config.php')) {
    include_once 'config.php';
    
    // Test database connection function
    $pdo = get_db_connection();
    if ($pdo !== false) {
        echo "<div class='success'>✅ get_db_connection() function works</div>";
    } else {
        echo "<div class='error'>❌ get_db_connection() function failed</div>";
    }
    
    // Test login check function
    if (function_exists('is_logged_in')) {
        echo "<div class='success'>✅ is_logged_in() function exists</div>";
    } else {
        echo "<div class='error'>❌ is_logged_in() function does NOT exist</div>";
    }
    
    // Test sanitize function
    if (function_exists('sanitize_input')) {
        $test_input = '<script>alert("test")</script>';
        $sanitized = sanitize_input($test_input);
        if ($sanitized !== $test_input) {
            echo "<div class='success'>✅ sanitize_input() function works</div>";
        } else {
            echo "<div class='error'>❌ sanitize_input() function is NOT working properly</div>";
        }
    } else {
        echo "<div class='error'>❌ sanitize_input() function does NOT exist</div>";
    }
} else {
    echo "<div class='error'>❌ config.php file not found</div>";
}

// Test 7: Login System Test
echo "<h2>7. Login System Test</h2>";
if (file_exists('login.php')) {
    echo "<div class='info'>ℹ️ Login system exists - test with these credentials:</div>";
    echo "<div class='info'>Student: student@ruraledu.com / Student123!</div>";
    echo "<div class='info'>Teacher: teacher@ruraledu.com / Teacher123!</div>";
    echo "<div class='info'>Admin: admin@ruraledu.com / Admin123!</div>";
} else {
    echo "<div class='error'>❌ login.php file not found</div>";
}

// Test 8: Recommendations
echo "<h2>8. Recommendations</h2>";
echo "<div class='info'>📋 To fix any issues:</div>";
echo "<div class='info'>1. Run setup_db.php to create database and tables</div>";
echo "<div class='info'>2. Make sure MAMP/XAMPP is running</div>";
echo "<div class='info'>3. Check database credentials in config.php</div>";
echo "<div class='info'>4. Ensure all files are in the correct directory</div>";

echo "<h2>🎯 Quick Fix Commands</h2>";
echo "<div class='info'>1. Start MAMP/XAMPP</div>";
echo "<div class='info'>2. Go to: http://localhost:8888/your-project-folder/setup_db.php</div>";
echo "<div class='info'>3. Then test: http://localhost:8888/your-project-folder/login.php</div>";

echo "<br><div class='success'><strong>✅ Backend test completed!</strong></div>";
?> 