<?php
require_once 'config.php';

$error_message = '';
$success_message = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_id = null;

// Validate token
if (!empty($token)) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo) {
            $stmt = $pdo->prepare("
                SELECT prt.user_id, prt.expires_at, prt.used, u.email 
                FROM password_reset_tokens prt 
                JOIN users u ON prt.user_id = u.id 
                WHERE prt.token = ? AND prt.used = 0
            ");
            $stmt->execute([$token]);
            $token_data = $stmt->fetch();
            
            if ($token_data && strtotime($token_data['expires_at']) > time()) {
                $valid_token = true;
                $user_id = $token_data['user_id'];
            } else {
                $error_message = 'Invalid or expired reset token.';
                if ($token_data && $token_data['used']) {
                    $error_message = 'This reset token has already been used.';
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Password reset validation error: " . $e->getMessage());
        $error_message = 'System error. Please try again later.';
    }
} else {
    $error_message = 'No reset token provided.';
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
        log_security_event('csrf_violation', 'Password reset form CSRF token mismatch', 'warning');
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirm_password)) {
            $error_message = 'Please fill in all fields.';
        } elseif (!validate_password($password)) {
            $error_message = 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        } else {
            try {
                $pdo = get_db_connection();
                
                if ($pdo) {
                    // Hash new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update user password
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    
                    // Mark token as used
                    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                    $stmt->execute([$token]);
                    
                    // Log the password reset
                    log_activity($user_id, 'password_reset', 'Password successfully reset');
                    log_security_event('password_reset_successful', "Password reset successful for user ID: $user_id", 'info');
                    
                    $success_message = 'Password has been successfully reset. You can now log in with your new password.';
                } else {
                    $error_message = 'System error. Please try again later.';
                }
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error_message = 'System error. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.4">
    <style>
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .reset-header {
            margin-bottom: 30px;
        }
        
        .reset-header h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .reset-header p {
            color: #718096;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        
        .reset-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .reset-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h1>🔐 Reset Password</h1>
                <p>Enter your new password below.</p>
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
            
            <?php if ($valid_token && empty($success_message)): ?>
            <form method="POST" action="" id="resetForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your new password">
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm your new password">
                </div>
                
                <button type="submit" class="reset-btn" id="resetBtn">🔒 Reset Password</button>
            </form>
            <?php endif; ?>
            
            <div>
                <a href="login.php" class="back-link">⬅️ Back to Login</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const resetBtn = document.getElementById('resetBtn');
            
            if (passwordInput && confirmPasswordInput) {
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
                if (resetBtn) {
                    resetBtn.addEventListener('click', function(e) {
                        if (!validatePasswords()) {
                            e.preventDefault();
                            alert('Passwords do not match!');
                            return false;
                        }
                        
                        this.innerHTML = 'Resetting Password...';
                        this.disabled = true;
                    });
                }
            }
        });
    </script>
</body>
</html> 