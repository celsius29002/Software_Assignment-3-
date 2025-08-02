<?php
require_once 'config.php';

$error_code = $_GET['code'] ?? '404';
$error_messages = [
    '403' => [
        'title' => 'Access Forbidden',
        'message' => 'You do not have permission to access this resource.',
        'description' => 'The server understood the request but refuses to authorize it.'
    ],
    '404' => [
        'title' => 'Page Not Found',
        'message' => 'The page you are looking for could not be found.',
        'description' => 'The requested resource was not found on this server.'
    ],
    '500' => [
        'title' => 'Internal Server Error',
        'message' => 'Something went wrong on our end.',
        'description' => 'The server encountered an internal error and was unable to complete your request.'
    ]
];

$error = $error_messages[$error_code] ?? $error_messages['404'];

// Log the error
log_security_event('error_page', "Error $error_code accessed", 'info');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error['title']; ?> - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.4">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #e53e3e;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .error-message {
            color: #718096;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-description {
            color: #a0aec0;
            font-size: 0.9rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        @media (max-width: 480px) {
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-code"><?php echo $error_code; ?></div>
            <h1 class="error-title"><?php echo $error['title']; ?></h1>
            <p class="error-message"><?php echo $error['message']; ?></p>
            <p class="error-description"><?php echo $error['description']; ?></p>
            
            <div class="error-actions">
                <a href="index.html" class="btn btn-primary">🏠 Go Home</a>
                <a href="javascript:history.back()" class="btn btn-secondary">⬅️ Go Back</a>
            </div>
        </div>
    </div>
</body>
</html> 