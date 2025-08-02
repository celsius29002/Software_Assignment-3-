<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once 'config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
        log_security_event('csrf_violation', 'Password reset form CSRF token mismatch', 'warning');
    } elseif (!check_rate_limit('password_reset', 3, 3600)) { // 3 attempts per hour
        $error_message = 'Too many password reset attempts. Please try again later.';
        log_security_event('rate_limit_exceeded', 'Password reset rate limit exceeded', 'warning');
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        
        if (!validate_email($email)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            try {
                $pdo = get_db_connection();
                
                if ($pdo) {
                    // Check if user exists
                    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email = ? AND is_active = 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        // Generate secure reset token
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        
                        // Store reset token
                        $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$user['id'], $token, $expires]);
                        
                        // In a real application, you would send an email here
                        // For demo purposes, we'll show the reset link
                        $reset_link = "reset-password.php?token=" . $token;
                        
                        $success_message = "Password reset instructions have been sent to your email address.";
                        
                        // Log the password reset request
                        log_security_event('password_reset_requested', "Password reset requested for user: $email", 'info');
                        
                        // For demo purposes, show the reset link
                        $success_message .= "<br><br><strong>Demo Reset Link:</strong> <a href='$reset_link'>$reset_link</a>";
                    } else {
                        // Don't reveal if email exists or not for security
                        $success_message = "If an account with that email exists, password reset instructions have been sent.";
                    }
                } else {
                    $error_message = "System error. Please try again later.";
                }
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error_message = "System error. Please try again later.";
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
    <title>Forgot Password - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.4">
    <style>
        .forgot-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .forgot-header {
            margin-bottom: 30px;
        }
        
        .forgot-header h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .forgot-header p {
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
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <h1>🔐 Forgot Password</h1>
                <p>Enter your email address and we'll send you instructions to reset your password.</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <button type="submit" class="reset-btn">📧 Send Reset Link</button>
            </form>
            
            <div>
                <a href="login.php" class="back-link">⬅️ Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html> 