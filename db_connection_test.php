<?php
// Simple Database Connection Test
echo "<h1>🔌 Database Connection Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
    .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
    .info { color: blue; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
    .step { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #6c757d; }
</style>";

echo "<div class='container'>";

// Test 1: Basic MySQL Connection
echo "<h2>1. Testing MySQL Server Connection</h2>";
try {
    $host = 'localhost';
    $username = 'root';
    $password = 'root'; // MAMP default
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ MySQL server connection successful!</div>";
    echo "<div class='info'>Server: $host | Username: $username</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ MySQL server connection failed: " . $e->getMessage() . "</div>";
    echo "<div class='step'>💡 Make sure MAMP/XAMPP is running and MySQL is started</div>";
    exit;
}

// Test 2: Database Creation
echo "<h2>2. Testing Database Creation</h2>";
try {
    $dbname = 'ruraledu_db';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "<div class='success'>✅ Database '$dbname' created successfully!</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database creation failed: " . $e->getMessage() . "</div>";
    exit;
}

// Test 3: Connect to Specific Database
echo "<h2>3. Testing Database Connection</h2>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ Connected to database '$dbname' successfully!</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}

// Test 4: Check Tables
echo "<h2>4. Checking Database Tables</h2>";
$tables = ['users', 'user_profiles', 'user_progress', 'user_assignments', 'activity_logs'];
$existing_tables = [];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
            $existing_tables[] = $table;
        } else {
            echo "<div class='error'>❌ Table '$table' does NOT exist</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error checking table '$table': " . $e->getMessage() . "</div>";
    }
}

// Test 5: Create Tables if Missing
if (count($existing_tables) < count($tables)) {
    echo "<h2>5. Creating Missing Tables</h2>";
    echo "<div class='info'>Some tables are missing. Creating them now...</div>";
    
    // Users table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<div class='success'>✅ Users table created</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creating users table: " . $e->getMessage() . "</div>";
    }
    
    // User profiles table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            school VARCHAR(100),
            grade_level VARCHAR(20),
            subjects_of_interest TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        echo "<div class='success'>✅ User profiles table created</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creating user_profiles table: " . $e->getMessage() . "</div>";
    }
    
    // User progress table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subject VARCHAR(50) NOT NULL,
            progress_percentage DECIMAL(5,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        echo "<div class='success'>✅ User progress table created</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creating user_progress table: " . $e->getMessage() . "</div>";
    }
    
    // User assignments table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            assignment_title VARCHAR(255) NOT NULL,
            subject VARCHAR(50) NOT NULL,
            due_date DATE,
            status ENUM('pending', 'in_progress', 'completed', 'overdue') DEFAULT 'pending',
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            marks INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        echo "<div class='success'>✅ User assignments table created</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creating user_assignments table: " . $e->getMessage() . "</div>";
    }
}

// Test 6: Insert Sample Data
echo "<h2>6. Inserting Sample Data</h2>";
try {
    // Check if users exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();
    
    if ($user_count == 0) {
        // Insert sample users
        $pdo->exec("INSERT INTO users (first_name, last_name, email, password, role) VALUES 
            ('Xavier', 'Student', 'student@ruraledu.com', 'Student123!', 'student'),
            ('Jane', 'Teacher', 'teacher@ruraledu.com', 'Teacher123!', 'teacher'),
            ('Admin', 'User', 'admin@ruraledu.com', 'Admin123!', 'admin')
        ");
        echo "<div class='success'>✅ Sample users inserted</div>";
        
        // Insert sample progress data
        $pdo->exec("INSERT INTO user_progress (user_id, subject, progress_percentage) VALUES 
            (1, 'English Standard', 25),
            (1, 'Mathematics Standard 2', 40),
            (1, 'Design and Technology', 65),
            (1, 'Software Engineering', 45),
            (1, 'PDHPE', 30)
        ");
        echo "<div class='success'>✅ Sample progress data inserted</div>";
        
        // Insert sample assignments
        $pdo->exec("INSERT INTO user_assignments (user_id, assignment_title, subject, due_date, priority, marks) VALUES 
            (1, 'English Standard: Trial Examination', 'English Standard', '2024-08-04', 'high', 100),
            (1, 'Mathematics Standard 2: Trial Examination', 'Mathematics Standard 2', '2024-08-11', 'high', 100),
            (1, 'PDHPE: Trial Examination', 'PDHPE', '2024-08-08', 'high', 100)
        ");
        echo "<div class='success'>✅ Sample assignments inserted</div>";
    } else {
        echo "<div class='info'>ℹ️ Sample data already exists</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error inserting sample data: " . $e->getMessage() . "</div>";
}

// Test 7: Final Connection Test
echo "<h2>7. Final Connection Test</h2>";
try {
    // Test config.php connection function
    if (file_exists('config.php')) {
        include_once 'config.php';
        $test_pdo = get_db_connection();
        if ($test_pdo !== false) {
            echo "<div class='success'>✅ config.php get_db_connection() function works!</div>";
        } else {
            echo "<div class='error'>❌ config.php get_db_connection() function failed</div>";
        }
    } else {
        echo "<div class='error'>❌ config.php file not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Final test failed: " . $e->getMessage() . "</div>";
}

echo "<h2>🎉 Database Connection Summary</h2>";
echo "<div class='success'>✅ Database connection established successfully!</div>";
echo "<div class='info'>📋 Your website can now use the database for:</div>";
echo "<div class='step'>• User authentication and sessions</div>";
echo "<div class='step'>• Storing user progress data</div>";
echo "<div class='step'>• Managing assignments and due dates</div>";
echo "<div class='step'>• Tracking learning activities</div>";

echo "<h2>🚀 Next Steps</h2>";
echo "<div class='info'>1. Test the login system: <a href='login.php'>login.php</a></div>";
echo "<div class='info'>2. Test the dashboard: <a href='dashboard.php'>dashboard.php</a></div>";
echo "<div class='info'>3. Use these credentials to login:</div>";
echo "<div class='step'>• Student: student@ruraledu.com / Student123!</div>";
echo "<div class='step'>• Teacher: teacher@ruraledu.com / Teacher123!</div>";
echo "<div class='step'>• Admin: admin@ruraledu.com / Admin123!</div>";

echo "</div>";
?> 