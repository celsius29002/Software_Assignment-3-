<?php
/**
 * Security Monitor
 * This script monitors security events and provides a dashboard for security analysis
 * Access restricted to admin users only
 */

require_once 'config.php';

// Check if user is admin
if (!is_admin()) {
    header("Location: error.php?code=403");
    exit();
}

// Get security statistics
function get_security_stats() {
    $pdo = get_db_connection();
    $stats = [];
    
    if ($pdo) {
        try {
            // Get recent security events
            $stmt = $pdo->prepare("
                SELECT action, COUNT(*) as count, MAX(created_at) as last_occurrence 
                FROM activity_logs 
                WHERE action IN ('login', 'logout', 'failed_login', 'suspicious_activity')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY action
            ");
            $stmt->execute();
            $stats['recent_events'] = $stmt->fetchAll();
            
            // Get failed login attempts
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as failed_attempts 
                FROM login_attempts 
                WHERE success = 0 
                AND attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute();
            $stats['failed_logins'] = $stmt->fetchColumn();
            
            // Get unique IP addresses
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT ip_address) as unique_ips 
                FROM activity_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute();
            $stats['unique_ips'] = $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Security monitor error: " . $e->getMessage());
        }
    }
    
    return $stats;
}

$security_stats = get_security_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Monitor - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.4">
    <style>
        .security-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .security-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .security-header h1 {
            margin: 0;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .security-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #718096;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .events-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .events-header {
            background: #f7fafc;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .events-header h3 {
            margin: 0;
            color: #2d3748;
            font-size: 1.5rem;
        }
        
        .events-content {
            padding: 20px;
        }
        
        .event-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .event-item:last-child {
            border-bottom: none;
        }
        
        .event-action {
            font-weight: 600;
            color: #2d3748;
        }
        
        .event-count {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .event-time {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .security-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
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
        
        .alert {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #feb2b2;
        }
        
        .success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
    </style>
</head>
<body>
    <div class="security-container">
        <div class="security-header">
            <h1>🔒 Security Monitor</h1>
            <p>Real-time security monitoring and threat detection</p>
        </div>
        
        <?php if ($security_stats['failed_logins'] > 10): ?>
        <div class="alert">
            <strong>⚠️ Security Alert:</strong> High number of failed login attempts detected in the last 24 hours.
        </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $security_stats['failed_logins'] ?? 0; ?></div>
                <div class="stat-label">Failed Login Attempts (24h)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $security_stats['unique_ips'] ?? 0; ?></div>
                <div class="stat-label">Unique IP Addresses (24h)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($security_stats['recent_events'] ?? []); ?></div>
                <div class="stat-label">Security Events (24h)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">🟢</div>
                <div class="stat-label">System Status</div>
            </div>
        </div>
        
        <div class="events-table">
            <div class="events-header">
                <h3>📊 Recent Security Events</h3>
            </div>
            <div class="events-content">
                <?php if (!empty($security_stats['recent_events'])): ?>
                    <?php foreach ($security_stats['recent_events'] as $event): ?>
                    <div class="event-item">
                        <div>
                            <div class="event-action"><?php echo ucfirst(str_replace('_', ' ', $event['action'])); ?></div>
                            <div class="event-time">Last: <?php echo date('M j, Y H:i', strtotime($event['last_occurrence'])); ?></div>
                        </div>
                        <div class="event-count"><?php echo $event['count']; ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #718096; padding: 20px;">No recent security events to display.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="security-actions">
            <a href="dashboard.php" class="btn btn-primary">📊 Back to Dashboard</a>
            <a href="logout.php" class="btn btn-secondary">🔓 Logout</a>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 