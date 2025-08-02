<?php
// Simple database setup script
// Run this once to set up your database

$host = 'localhost';
$username = 'root';
$password = 'root'; // MAMP default password
$dbname = 'ruraledu_db';

try {
    // Connect without specifying database first
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database '$dbname' created or already exists.<br>";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables manually (without the CREATE DATABASE part)
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
        is_active BOOLEAN DEFAULT TRUE,
        email_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_email (email),
        INDEX idx_role (role)
    )");
    echo "Users table created.<br>";
    
    // User profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        avatar_url VARCHAR(255),
        bio TEXT,
        school VARCHAR(100),
        grade_level VARCHAR(20),
        subjects_of_interest TEXT,
        learning_goals TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_profile (user_id)
    )");
    echo "User profiles table created.<br>";
    
    // User progress table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(50) NOT NULL,
        lesson_id VARCHAR(50),
        progress_percentage DECIMAL(5,2) DEFAULT 0.00,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_subject_lesson (user_id, subject, lesson_id),
        INDEX idx_user_subject (user_id, subject)
    )");
    echo "User progress table created.<br>";
    
    // User assignments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        assignment_title VARCHAR(255) NOT NULL,
        subject VARCHAR(50) NOT NULL,
        description TEXT,
        due_date DATE,
        status ENUM('pending', 'in_progress', 'completed', 'overdue') DEFAULT 'pending',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        marks INT,
        grade DECIMAL(5,2),
        submitted_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_status (user_id, status),
        INDEX idx_due_date (due_date)
    )");
    echo "User assignments table created.<br>";
    
    // Activity logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_user_action (user_id, action),
        INDEX idx_created_at (created_at)
    )");
    echo "Activity logs table created.<br>";
    
    // Password reset tokens table
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_token (token),
        INDEX idx_expires (expires_at)
    )");
    echo "Password reset tokens table created.<br>";
    
    // User sessions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_id VARCHAR(255) UNIQUE NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_session_id (session_id),
        INDEX idx_expires (expires_at)
    )");
    echo "User sessions table created.<br>";
    
    // Login attempts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        success BOOLEAN DEFAULT FALSE,
        INDEX idx_email_ip (email, ip_address),
        INDEX idx_attempted_at (attempted_at)
    )");
    echo "Login attempts table created.<br>";
    
    // Insert default users if they don't exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    
    // Check for admin user
    $stmt->execute(['admin@ruraledu.com']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES
        ('Admin', 'User', 'admin@ruraledu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE)");
        echo "Admin user created.<br>";
    }
    
    // Check for student user
    $stmt->execute(['student@ruraledu.com']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES
        ('John', 'Student', 'student@ruraledu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', TRUE)");
        echo "Student user created.<br>";
    }
    
    // Check for teacher user
    $stmt->execute(['teacher@ruraledu.com']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES
        ('Jane', 'Teacher', 'teacher@ruraledu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', TRUE)");
        echo "Teacher user created.<br>";
    }
    
    // Insert sample data
    $studentId = $pdo->query("SELECT id FROM users WHERE email = 'student@ruraledu.com'")->fetchColumn();
    
    if ($studentId) {
        // Insert sample progress data
        $pdo->exec("INSERT IGNORE INTO user_progress (user_id, subject, lesson_id, progress_percentage) VALUES
        ($studentId, 'English Standard', 'module_1', 25.00),
        ($studentId, 'Mathematics Standard 2', 'topic_1', 40.00),
        ($studentId, 'Design and Technology', 'project_1', 65.00),
        ($studentId, 'Software Engineering', 'project_1', 45.00),
        ($studentId, 'PDHPE', 'unit_1', 30.00)");
        echo "Sample progress data inserted.<br>";
        
        // Insert sample assignments
        $pdo->exec("INSERT IGNORE INTO user_assignments (user_id, assignment_title, subject, description, due_date, status, priority, marks) VALUES
        ($studentId, 'English Standard: Trial Examination', 'English Standard', 'Sit the English Standard Trial HSC Examination', '2024-08-04', 'pending', 'high', 100),
        ($studentId, 'Mathematics Standard 2: Trial Examination', 'Mathematics Standard 2', 'Sit the Mathematics Standard 2 Trial HSC Examination', '2024-08-11', 'pending', 'high', 100),
        ($studentId, 'PDHPE: Trial Examination', 'PDHPE', 'Sit the PDHPE Trial HSC Examination', '2024-08-08', 'pending', 'high', 100),
        ($studentId, 'Design and Technology: Major Project', 'Design and Technology', 'Submit your Major Design Project folio', '2024-08-28', 'in_progress', 'high', 60),
        ($studentId, 'Software Engineering: Major Project', 'Software Engineering', 'Develop a substantial software engineering project', '2024-08-01', 'in_progress', 'high', 100)");
        echo "Sample assignments inserted.<br>";
    }
    
    echo "<br><strong>Database setup completed successfully!</strong><br>";
    echo "You can now <a href='login.php'>login here</a><br>";
    echo "<br>Login credentials:<br>";
    echo "Email: student@ruraledu.com<br>";
    echo "Password: Student123!<br>";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 