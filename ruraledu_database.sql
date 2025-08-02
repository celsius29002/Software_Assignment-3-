-- RuralEdu Database Setup
-- Run this script in your MySQL database to create the necessary tables

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS ruraledu_db;
USE ruraledu_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- User profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
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
);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
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
);

-- Password reset tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- User sessions table (for additional session management)
CREATE TABLE IF NOT EXISTS user_sessions (
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
);

-- Login attempts table (for security)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_email_ip (email, ip_address),
    INDEX idx_attempted_at (attempted_at)
);

-- User progress table
CREATE TABLE IF NOT EXISTS user_progress (
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
);

-- User assignments table
CREATE TABLE IF NOT EXISTS user_assignments (
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
);

-- Insert default admin user (password: Admin123!)
INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES
('Admin', 'User', 'admin@ruraledu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE)
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample student user (password: Student123!)
INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES
('John', 'Student', 'student@ruraledu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', TRUE)
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample teacher user (password: Teacher123!)
INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES
('Jane', 'Teacher', 'teacher@ruraledu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', TRUE)
ON DUPLICATE KEY UPDATE id=id;

-- Create user profiles for sample users
INSERT INTO user_profiles (user_id, school, grade_level, subjects_of_interest) VALUES
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Rural High School', 'Year 12', 'English Standard, Mathematics Standard 2, Design and Technology, Software Engineering, PDHPE')
ON DUPLICATE KEY UPDATE user_id=user_id;

INSERT INTO user_profiles (user_id, school, subjects_of_interest) VALUES
((SELECT id FROM users WHERE email = 'teacher@ruraledu.com'), 'Rural High School', 'English Standard, Mathematics Standard 2')
ON DUPLICATE KEY UPDATE user_id=user_id;

-- Insert sample progress data
INSERT INTO user_progress (user_id, subject, lesson_id, progress_percentage) VALUES
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'English Standard', 'module_1', 25.00),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Mathematics Standard 2', 'topic_1', 40.00),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Design and Technology', 'project_1', 65.00),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Software Engineering', 'project_1', 45.00),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'PDHPE', 'unit_1', 30.00)
ON DUPLICATE KEY UPDATE progress_percentage=VALUES(progress_percentage);

-- Insert sample assignments
INSERT INTO user_assignments (user_id, assignment_title, subject, description, due_date, status, priority, marks) VALUES
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'English Standard: Trial Examination', 'English Standard', 'Sit the English Standard Trial HSC Examination', '2024-08-04', 'pending', 'high', 100),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Mathematics Standard 2: Trial Examination', 'Mathematics Standard 2', 'Sit the Mathematics Standard 2 Trial HSC Examination', '2024-08-11', 'pending', 'high', 100),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'PDHPE: Trial Examination', 'PDHPE', 'Sit the PDHPE Trial HSC Examination', '2024-08-08', 'pending', 'high', 100),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Design and Technology: Major Project', 'Design and Technology', 'Submit your Major Design Project folio', '2024-08-28', 'in_progress', 'high', 60),
((SELECT id FROM users WHERE email = 'student@ruraledu.com'), 'Software Engineering: Major Project', 'Software Engineering', 'Develop a substantial software engineering project', '2024-08-01', 'in_progress', 'high', 100)
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Create indexes for better performance
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_activity_logs_user_created ON activity_logs(user_id, created_at);
CREATE INDEX idx_user_progress_updated ON user_progress(updated_at);
CREATE INDEX idx_user_assignments_due_status ON user_assignments(due_date, status); 