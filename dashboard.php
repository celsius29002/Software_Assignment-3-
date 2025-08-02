<?php
require_once 'config.php';
require_login();

// Get user data
$user_id = $_SESSION['user_id'];
$pdo = get_db_connection();

// Initialize default data
$progress_data = [];
$assignments = [];
$profile = [
    'school' => 'Rural High School',
    'grade_level' => 'Year 12',
    'subjects_of_interest' => 'English Standard, Mathematics Standard 2, Design and Technology, Software Engineering, PDHPE'
];

// Only try to get data from database if connection is successful
if ($pdo !== false) {
    // Get user progress - handle case where table might not exist or be empty
    try {
        $stmt = $pdo->prepare("SELECT subject, progress_percentage FROM user_progress WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $progress_data = $stmt->fetchAll();
    } catch (PDOException $e) {
        // If table doesn't exist or other error, use empty array
        $progress_data = [];
    }

    // Get upcoming assignments - handle case where table might not exist or be empty
    try {
        $stmt = $pdo->prepare("SELECT assignment_title, subject, due_date, status, priority, marks FROM user_assignments WHERE user_id = ? AND status IN ('pending', 'in_progress') ORDER BY due_date ASC LIMIT 5");
        $stmt->execute([$user_id]);
        $assignments = $stmt->fetchAll();
    } catch (PDOException $e) {
        // If table doesn't exist or other error, use empty array
        $assignments = [];
    }

    // Get user profile - handle case where table might not exist or be empty
    try {
        $stmt = $pdo->prepare("SELECT school, grade_level, subjects_of_interest FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile_result = $stmt->fetch();
        if ($profile_result) {
            $profile = $profile_result;
        }
    } catch (PDOException $e) {
        // If table doesn't exist or other error, use default values
        // $profile already has default values
    }
}

// If no progress data exists, create some default data
if (empty($progress_data)) {
    $default_subjects = [
        'English Standard' => 25,
        'Mathematics Standard 2' => 40,
        'Design and Technology' => 65,
        'Software Engineering' => 45,
        'PDHPE' => 30
    ];
    
    foreach ($default_subjects as $subject => $progress) {
        $progress_data[] = [
            'subject' => $subject,
            'progress_percentage' => $progress
        ];
    }
}

// If no assignments exist, create some default assignments
if (empty($assignments)) {
    $assignments = [
        [
            'assignment_title' => 'English Standard: Trial Examination',
            'subject' => 'English Standard',
            'due_date' => '2024-08-04',
            'status' => 'pending',
            'priority' => 'high',
            'marks' => 100
        ],
        [
            'assignment_title' => 'Mathematics Standard 2: Trial Examination',
            'subject' => 'Mathematics Standard 2',
            'due_date' => '2024-08-11',
            'status' => 'pending',
            'priority' => 'high',
            'marks' => 100
        ],
        [
            'assignment_title' => 'PDHPE: Trial Examination',
            'subject' => 'PDHPE',
            'due_date' => '2024-08-08',
            'status' => 'pending',
            'priority' => 'high',
            'marks' => 100
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - RuralEdu</title>
    <link rel="stylesheet" href="styles.css?v=2.4" />
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#5a67d8" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="RuralEdu" />
    <meta name="application-name" content="RuralEdu" />
    <meta name="description" content="Offline-capable educational platform for rural students" />
  </head>
  <body>
    <!-- Header Navigation -->
    <header class="header">
      <div class="container">
        <div class="nav-brand">
          <h1>📚 RuralEdu</h1>
          <span class="tagline">Learning Without Limits</span>
        </div>
        <nav class="nav-menu">
          <ul>
            <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
            <li><a href="lessons.html" class="nav-link">Lessons</a></li>
            <li><a href="assignments.html" class="nav-link">Assignments</a></li>
            <li><a href="progress.html" class="nav-link">Progress</a></li>
            <li><a href="profile.html" class="nav-link">Profile</a></li>
          </ul>
        </nav>
        <div class="offline-indicator">
          <span id="connection-status" class="online" style="cursor: pointer;">🟢 Online</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <section class="section active">
        <div class="container">
          <div class="welcome-section">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>
            <p>Continue your learning journey</p>
          </div>
          
          <div class="dashboard-grid">
            <!-- Quick Stats -->
            <div class="stats-card">
              <h3>📊 Your Progress</h3>
              <div class="stat-item">
                <span class="stat-number">12</span>
                <span class="stat-label">Lessons Completed</span>
              </div>
              <div class="stat-item">
                <span class="stat-number"><?php echo count($assignments); ?></span>
                <span class="stat-label">Assignments Due</span>
              </div>
              <div class="stat-item">
                <span class="stat-number">85%</span>
                <span class="stat-label">Overall Progress</span>
              </div>
            </div>

            <!-- Recent Lessons -->
            <div class="recent-lessons">
              <h3>📖 Continue Learning</h3>
              <div class="lesson-list">
                <?php foreach ($progress_data as $progress): ?>
                <div class="lesson-item">
                  <div class="lesson-info">
                    <h4>
                      <?php
                      $icons = [
                          'English Standard' => '📖',
                          'Mathematics Standard 2' => '➗',
                          'Design and Technology' => '🛠️',
                          'Software Engineering' => '💻',
                          'PDHPE' => '🏃‍♂️'
                      ];
                      echo $icons[$progress['subject']] ?? '📚';
                      ?>
                      <?php echo htmlspecialchars($progress['subject']); ?>
                    </h4>
                    <p>Module: <?php echo htmlspecialchars($progress['subject']); ?> Progress</p>
                    <div class="progress-bar">
                      <div class="progress-fill" style="width: <?php echo $progress['progress_percentage']; ?>%"></div>
                    </div>
                  </div>
                  <a href="lessons.html" class="btn-continue">Continue</a>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Upcoming Assignments -->
            <div class="assignments-preview">
              <h3>📝 Upcoming Assignments</h3>
              <div class="assignment-list">
                <?php if (empty($assignments)): ?>
                <div class="assignment-item">
                  <h4>No Assignments Due</h4>
                  <p>Great job! You're all caught up.</p>
                  <span class="due-badge">Complete</span>
                </div>
                <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                <div class="assignment-item <?php echo $assignment['priority'] === 'high' ? 'urgent' : ''; ?>">
                  <h4><?php echo htmlspecialchars($assignment['assignment_title']); ?></h4>
                  <p>Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></p>
                  <span class="due-badge <?php echo $assignment['priority'] === 'high' ? 'urgent' : ''; ?>"><?php echo ucfirst($assignment['priority']); ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <div class="view-all-btn">
                <a href="assignments.html" class="btn-primary">View All Assignments</a>
              </div>
            </div>
          </div>

          <!-- Quick Action Cards -->
          <div class="quick-actions">
            <h3>🚀 Quick Actions</h3>
            <div class="action-grid">
              <a href="lessons.html" class="action-card">
                <div class="action-icon">📚</div>
                <h4>Browse Lessons</h4>
                <p>Explore available courses and start learning</p>
              </a>
              <a href="assignments.html" class="action-card">
                <div class="action-icon">📝</div>
                <h4>Complete Assignments</h4>
                <p>Work on pending assignments and quizzes</p>
              </a>
              <a href="progress.html" class="action-card">
                <div class="action-icon">📊</div>
                <h4>View Progress</h4>
                <p>Track your learning progress and achievements</p>
              </a>
              <a href="profile.html" class="action-card">
                <div class="action-icon">👤</div>
                <h4>Update Profile</h4>
                <p>Manage your account and preferences</p>
              </a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <p>&copy; 2025 RuralEdu - Empowering Rural Education</p>
        <div class="footer-links">
          <a href="#help">Help</a>
          <a href="#about">About</a>
          <a href="#contact">Contact</a>
        </div>
      </div>
    </footer>

    <script src="script.js"></script>
  </body>
</html> 
 