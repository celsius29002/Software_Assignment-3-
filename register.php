<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once 'config.php';

$error_message = "";
$success_message = "";

// Handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
        log_security_event('csrf_violation', 'Registration form CSRF token mismatch', 'warning');
    } else {
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = 'student'; // Default role for new registrations
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (!validate_email($email)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!validate_password($password)) {
        $error_message = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (!check_rate_limit('register', 3, 3600)) { // Max 3 registrations per hour
        $error_message = "Too many registration attempts. Please try again later.";
    } else {
        try {
            $pdo = get_db_connection();
            
            if ($pdo) {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error_message = "An account with this email already exists.";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $role]);
                    
                    $user_id = $pdo->lastInsertId();
                    
                    // Log the registration
                    log_activity($user_id, 'registration', 'New user registered');
                    
                    $success_message = "Registration successful! You can now log in.";
                    
                    // Clear form data
                    $first_name = $last_name = $email = '';
                }
            } else {
                $error_message = "Database connection error. Please try again later.";
            }
        } catch (PDOException $e) {
            $error_message = "Registration failed. Please try again later.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.2">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }
        
        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #5a67d8, #667eea);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .register-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        
        .register-header p {
            color: #718096;
            font-size: 1.1rem;
            font-weight: 400;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #5a67d8;
            box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.1);
            transform: translateY(-1px);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .strength-weak {
            color: #e53e3e;
        }
        
        .strength-medium {
            color: #d69e2e;
        }
        
        .strength-strong {
            color: #38a169;
        }
        
        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #5a67d8 0%, #4c51bf 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(90, 103, 216, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(90, 103, 216, 0.4);
            background: linear-gradient(135deg, #4c51bf 0%, #434190 100%);
        }
        
        .register-btn:hover::before {
            left: 100%;
        }
        
        .register-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            color: #c53030;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #feb2b2;
            font-weight: 500;
        }
        
        .success-message {
            background: linear-gradient(135deg, #c6f6d5, #9ae6b4);
            color: #22543d;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #9ae6b4;
            font-weight: 500;
        }
        
        .register-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-footer a {
            color: #5a67d8;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-footer a:hover {
            color: #4c51bf;
        }
        
        @media (max-width: 480px) {
            .register-card {
                padding: 2rem;
                margin: 10px;
            }
            
            .register-header h1 {
                font-size: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>📚 RuralEdu</h1>
                <p>Create your account to get started</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                               placeholder="Enter your first name">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required 
                               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                               placeholder="Enter your last name">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="Enter your email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Create a strong password">
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="register-btn">
                    Create Account
                </button>
            </form>
            
            <div class="register-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const registerBtn = document.querySelector('.register-btn');
            
            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = [];
                
                if (password.length >= 8) strength++;
                else feedback.push('At least 8 characters');
                
                if (/[a-z]/.test(password)) strength++;
                else feedback.push('One lowercase letter');
                
                if (/[A-Z]/.test(password)) strength++;
                else feedback.push('One uppercase letter');
                
                if (/\d/.test(password)) strength++;
                else feedback.push('One number');
                
                if (/[@$!%*?&]/.test(password)) strength++;
                else feedback.push('One special character (@$!%*?&)');
                
                return { strength, feedback };
            }
            
            passwordInput.addEventListener('input', function() {
                const result = checkPasswordStrength(this.value);
                let strengthText = '';
                let strengthClass = '';
                
                if (this.value.length === 0) {
                    strengthText = '';
                } else if (result.strength <= 2) {
                    strengthText = 'Weak password';
                    strengthClass = 'strength-weak';
                } else if (result.strength <= 3) {
                    strengthText = 'Medium strength password';
                    strengthClass = 'strength-medium';
                } else {
                    strengthText = 'Strong password';
                    strengthClass = 'strength-strong';
                }
                
                passwordStrength.textContent = strengthText;
                passwordStrength.className = 'password-strength ' + strengthClass;
            });
            
            // Confirm password validation
            function validatePasswords() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordInput.style.borderColor = '#e53e3e';
                    return false;
                } else {
                    confirmPasswordInput.style.borderColor = '#e2e8f0';
                    return true;
                }
            }
            
            confirmPasswordInput.addEventListener('input', validatePasswords);
            
            // Form submission
            registerBtn.addEventListener('click', function() {
                if (!validatePasswords()) {
                    alert('Passwords do not match!');
                    return false;
                }
                
                this.innerHTML = 'Creating Account...';
                this.style.opacity = '0.8';
            });
            
            // Add focus effects
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html> 