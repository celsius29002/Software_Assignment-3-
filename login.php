<?php
require_once 'config.php';

// Check if user wants to force show login page (for testing)
$force_login = isset($_GET['force']) && $_GET['force'] === 'true';

// If already logged in and not forcing login, redirect to dashboard
if (isset($_SESSION['user_id']) && !$force_login) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
        log_security_event('csrf_violation', 'Login form CSRF token mismatch', 'warning');
    } elseif (!check_rate_limit('login', 5, 300)) { // 5 attempts per 5 minutes
        $error_message = 'Too many login attempts. Please try again later.';
        log_security_event('rate_limit_exceeded', 'Login rate limit exceeded', 'warning');
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate email format
        if (!validate_email($email)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            try {
                $pdo = get_db_connection();
                
                if ($pdo) {
                    // Get user from database
                    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, role, is_active FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
                        // Successful login
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['login_time'] = time();
                        
                        // Update last login
                        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        
                        // Log successful login
                        log_activity($user['id'], 'login', 'Successful login');
                        log_security_event('successful_login', "User {$user['email']} logged in successfully", 'info');
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        // Log failed login attempt
                        log_security_event('failed_login', "Failed login attempt for email: $email", 'warning');
                        $error_message = 'Invalid email or password. Please try again.';
                    }
                } else {
                    // Fallback to demo credentials for testing
                    if ($email === 'student@ruraledu.com' && $password === 'Student123!') {
                        $_SESSION['user_id'] = 1;
                        $_SESSION['first_name'] = 'Xavier';
                        $_SESSION['last_name'] = 'Student';
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = 'student';
                        $_SESSION['login_time'] = time();
                        
                        header("Location: dashboard.php");
                        exit();
                    } elseif ($email === 'teacher@ruraledu.com' && $password === 'Teacher123!') {
                        $_SESSION['user_id'] = 2;
                        $_SESSION['first_name'] = 'Jane';
                        $_SESSION['last_name'] = 'Teacher';
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = 'teacher';
                        $_SESSION['login_time'] = time();
                        
                        header("Location: dashboard.php");
                        exit();
                    } elseif ($email === 'admin@ruraledu.com' && $password === 'Admin123!') {
                        $_SESSION['user_id'] = 3;
                        $_SESSION['first_name'] = 'Admin';
                        $_SESSION['last_name'] = 'User';
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = 'admin';
                        $_SESSION['login_time'] = time();
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error_message = 'Invalid email or password. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = 'Login error. Please try again later.';
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
    <title>Login - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.4">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .login-header p {
            color: #718096;
            font-size: 1rem;
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
        
        .login-btn {
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
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .demo-credentials {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .demo-credentials h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1rem;
        }
        
        .credential-item {
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .credential-item strong {
            color: #667eea;
        }
        
        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .logout-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .logout-link:hover {
            text-decoration: underline;
        }
        
        .session-info {
            background: #e6fffa;
            border: 1px solid #81e6d9;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .session-info strong {
            color: #2c7a7b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>🔐 RuralEdu Login</h1>
                <p>Welcome back! Please sign in to continue.</p>
            </div>
            
            <?php if (isset($_SESSION['user_id']) && $force_login): ?>
            <div class="session-info">
                <strong>Current Session:</strong> You are logged in as <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?> 
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="login-btn">🚀 Sign In</button>
            </form>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="forgot-password.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">🔑 Forgot Password?</a>
            </div>
            
            <div class="demo-credentials">
                <h3>📋 Demo Credentials</h3>
                <div class="credential-item">
                    <strong>Student:</strong> student@ruraledu.com / Student123!
                </div>
                <div class="credential-item">
                    <strong>Teacher:</strong> teacher@ruraledu.com / Teacher123!
                </div>
                <div class="credential-item">
                    <strong>Admin:</strong> admin@ruraledu.com / Admin123!
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="logout.php" class="logout-link">🔓 Clear Session / Logout</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <br><a href="dashboard.php" class="logout-link">📊 Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 