# 📚 EduConnect - Educational Platform for Rural Students

A modern, responsive educational website designed specifically for rural students, focusing on NESA (NSW Education Standards Authority) subjects. Built with HTML5, CSS3, JavaScript, and PHP with MySQL backend.

## 🌟 Features

### Frontend Features
- **Modern UI/UX**: Clean, responsive design with glassmorphism effects
- **Interactive Elements**: Hover effects, animations, and smooth transitions
- **Mobile-First Design**: Fully responsive across all devices
- **Subject Coverage**: Five core NESA subjects:
  - English Standard
  - Mathematics Standard 2
  - Design and Technology
  - Software Engineering
  - PDHPE

### Backend Features
- **User Authentication**: Secure login/registration system
- **Session Management**: PHP sessions with security features
- **Database Integration**: MySQL database with proper relationships
- **Security Features**:
  - Password hashing with bcrypt
  - CSRF protection
  - Rate limiting
  - Input sanitization
  - SQL injection prevention
- **Activity Logging**: Track user actions and system events
- **Password Reset**: Secure password recovery system

### Pages
- **Dashboard** (`index.html`): Overview of progress and upcoming assignments
- **Lessons** (`lessons.html`): Browse and filter available courses
- **Assignments** (`assignments.html`): Manage coursework and deadlines
- **Progress** (`progress.html`): Track learning progress and achievements
- **Profile** (`profile.html`): User profile and settings
- **Login** (`login.php`): User authentication
- **Registration** (`register.php`): New user registration
- **Password Reset** (`forgot-password.php`): Password recovery

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Step 1: Database Setup
1. Create a MySQL database
2. Import the database schema:
   ```bash
   mysql -u your_username -p your_database < database_setup.sql
   ```

### Step 2: Configuration
1. Edit `config.php` with your database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'educonnect_db';
   $username = 'your_db_username';
   $password_db = 'your_db_password';
   ```

2. Update the site URL in `config.php`:
   ```php
   define('SITE_URL', 'http://your-domain.com/educonnect');
   ```

### Step 3: File Permissions
Ensure your web server has read/write permissions for the application directory.

### Step 4: Web Server Configuration
1. Place all files in your web server's document root
2. Ensure PHP is enabled and configured
3. Configure your web server to handle PHP files

## 👥 Default Users

The system comes with three default users for testing:

| Email | Password | Role |
|-------|----------|------|
| admin@educonnect.com | Admin123! | Admin |
| student@educonnect.com | Student123! | Student |
| teacher@educonnect.com | Teacher123! | Teacher |

## 🔧 Database Schema

### Core Tables
- **users**: User accounts and authentication
- **user_profiles**: Extended user information
- **user_progress**: Learning progress tracking
- **user_assignments**: Assignment management
- **activity_logs**: System activity tracking
- **password_reset_tokens**: Password recovery tokens

### Security Tables
- **login_attempts**: Failed login tracking
- **user_sessions**: Session management

## 🛡️ Security Features

### Authentication & Authorization
- **Strong Password Requirements**: Minimum 8 characters with uppercase, lowercase, numbers, and special characters
- **Secure Password Hashing**: All passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT`
- **Session Management**: Secure session handling with automatic timeout and regeneration
- **Role-Based Access Control**: Student, Teacher, and Admin roles with appropriate permissions
- **Password Reset**: Secure token-based password reset system with expiration

### Protection Against Common Attacks
- **CSRF Protection**: All forms include cryptographically secure CSRF tokens
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Prevention**: Input sanitization and output encoding
- **Clickjacking Protection**: X-Frame-Options headers prevent clickjacking
- **Content Security Policy**: CSP headers restrict resource loading to trusted sources

### Rate Limiting & Monitoring
- **Login Protection**: Rate limiting prevents brute force attacks (5 attempts per 5 minutes)
- **Security Monitoring**: Real-time security event monitoring and alerting
- **Audit Logging**: Comprehensive logging of all security events
- **Suspicious Activity Detection**: Automated detection of suspicious behavior

### Data Protection
- **Input Validation**: All user inputs are validated and sanitized
- **Secure Headers**: HTTP security headers protect against various attacks
- **File Security**: Sensitive files are protected from direct access
- **Error Handling**: Secure error handling without information disclosure

### Security Monitoring Dashboard
- **Admin Access**: Security monitoring dashboard available at `security_monitor.php`
- **Real-time Statistics**: Failed login attempts, unique IPs, security events
- **Alert System**: Automatic alerts for suspicious activities
- **Audit Trail**: Complete user activity logging

For detailed security information, see [SECURITY.md](SECURITY.md).

## 🎨 Design Features

### Visual Design
- **Glassmorphism**: Modern frosted glass effects
- **Gradient Backgrounds**: Beautiful color transitions
- **Smooth Animations**: CSS transitions and transforms
- **Interactive Elements**: Hover effects and micro-interactions

### Typography
- **Inter Font**: Modern, readable typography
- **Responsive Sizing**: Scales appropriately across devices
- **Color Contrast**: WCAG compliant color combinations

### Components
- **Cards**: Elevated content containers
- **Buttons**: Gradient backgrounds with hover effects
- **Forms**: Clean, accessible form design
- **Progress Bars**: Visual progress indicators

## 📱 Responsive Design

The application is fully responsive and optimized for:
- Desktop computers (1200px+)
- Tablets (768px - 1199px)
- Mobile phones (320px - 767px)

## 🔄 Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 📊 NESA Subject Integration

The platform is specifically designed for NSW HSC students with:

### English Standard
- Module-based learning structure
- Text analysis and essay writing
- NESA syllabus alignment

### Mathematics Standard 2
- Real-world applications focus
- Financial mathematics
- Statistics and networks

### Design and Technology
- Project-based learning
- Design thinking methodology
- Major project support

### Software Engineering
- Programming fundamentals
- Project development lifecycle
- Extended project work

### PDHPE
- Health priorities
- Movement skills
- Critical thinking development

## 🚀 Performance Optimizations

- **CSS Optimization**: Minified and optimized stylesheets
- **Image Optimization**: WebP format support
- **Caching**: Browser cache busting with version parameters
- **Database Indexing**: Optimized database queries
- **Lazy Loading**: Progressive content loading

## 🔧 Customization

### Adding New Subjects
1. Update the lessons grid in `lessons.html`
2. Add subject data to the database
3. Update navigation and filtering

### Modifying Styles
- Main stylesheet: `styles.css`
- Component-specific styles in individual files
- CSS variables for consistent theming

### Database Modifications
- All database changes should be made through migrations
- Update the schema file for new installations
- Maintain data integrity with foreign keys

## 🐛 Troubleshooting

### Common Issues

**Login not working:**
- Check database connection in `config.php`
- Verify user exists in database
- Check PHP error logs

**Styles not loading:**
- Clear browser cache
- Check file permissions
- Verify CSS file paths

**Database errors:**
- Verify MySQL credentials
- Check database exists
- Ensure tables are created

### Debug Mode
Enable debug mode in `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📝 License

This project is created for educational purposes. Please ensure compliance with local educational regulations and data protection laws.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support or questions:
- Check the troubleshooting section
- Review the code comments
- Contact the development team

## 🔮 Future Enhancements

- Email integration for notifications
- File upload system for assignments
- Real-time chat functionality
- Advanced analytics dashboard
- Mobile app development
- Integration with learning management systems

---

**Built with ❤️ for rural education** 