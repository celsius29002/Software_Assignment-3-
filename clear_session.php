<?php
// Simple session clearing script for testing
session_start();

echo "<h1>🔓 Session Management</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
    .button:hover { background: #0056b3; }
</style>";

echo "<div class='container'>";

// Check current session status
if (isset($_SESSION['user_id'])) {
    echo "<div class='info'>";
    echo "<h3>Current Session Status:</h3>";
    echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
    echo "<p><strong>Name:</strong> " . $_SESSION['first_name'] . " " . $_SESSION['last_name'] . "</p>";
    echo "<p><strong>Email:</strong> " . $_SESSION['email'] . "</p>";
    echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h3>Session Actions:</h3>";
    echo "<a href='logout.php' class='button'>🔓 Logout (Clear Session)</a>";
    echo "<a href='dashboard.php' class='button'>📊 Go to Dashboard</a>";
    echo "</div>";
} else {
    echo "<div class='info'>";
    echo "<h3>No Active Session</h3>";
    echo "<p>You are not currently logged in.</p>";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h3>Login Options:</h3>";
    echo "<a href='login.php' class='button'>🔐 Go to Login</a>";
    echo "<a href='login.html' class='button'>🔐 HTML Login</a>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>Testing Links:</h3>";
echo "<a href='test_backend.php' class='button'>🔧 Test Backend</a>";
echo "<a href='db_connection_test.php' class='button'>🔌 Test Database</a>";
echo "<a href='start.html' class='button'>🏠 Go Home</a>";
echo "</div>";

echo "</div>";
?> 