# Judge Scoreboard Application

A modern, responsive web application for managing and displaying competition scores in real-time. Built with PHP, MySQL, and Bootstrap 5.

## Features

### User Management
- Multi-role system (Admin, Judge)
- Secure authentication and authorization
- User registration and management
- Password protection and security measures

### Scoring System
- Real-time score submission
- Score validation (0-100 range)
- Average score calculation
- Historical score tracking
- Score filtering and sorting

### Participant Management
- Add/remove participants
- Unique participant identifiers
- Score history per participant
- Real-time ranking updates

### Modern UI/UX
- Responsive Bootstrap 5 design
- Mobile-friendly interface
- Real-time updates
- Interactive scoring controls
- Sortable tables with pagination
- Toast notifications for actions
- Dark mode support

## Technical Stack

### Backend
- PHP 7.2+ (compatible up to 8.2)
- MySQL/MariaDB
- Apache web server

### Frontend
- Bootstrap 5.3
- Font Awesome 6.4
- Custom CSS with modern design
- JavaScript for dynamic interactions

### Security Features
- SQL injection prevention
- XSS protection
- CSRF protection
- Password hashing
- Rate limiting
- Secure session management

## Installation

1. **Server Requirements**
   - PHP 7.2 or higher
   - MySQL 5.7 or higher
   - Apache web server with mod_rewrite enabled
   - SSL certificate (recommended)

2. **Database Setup**
   ```sql
   # Import the database schema
   mysql -u your_username -p your_database < sql/setup.sql
   ```

3. **Configuration**
   - Copy and configure database settings in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'your_database';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

4. **File Permissions**
   ```bash
   chmod 755 -R /path/to/application
   chmod 777 -R /path/to/application/logs
   ```

5. **Web Server Configuration**
   - Ensure .htaccess is properly configured
   - Enable mod_rewrite for Apache
   - Set document root to application directory

## Directory Structure

```
scoreboard/
├── api/                 # API endpoints
├── assets/             # Static assets
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── images/        # Image assets
├── config/            # Configuration files
├── includes/          # PHP includes
├── sql/              # Database schemas
├── .htaccess         # Apache configuration
├── admin.php         # Admin panel
├── index.php         # Main scoreboard
├── judge.php         # Judge scoring interface
└── login.php         # Authentication
```

## Usage

### Admin Panel
- Access `/admin.php` to manage users and participants
- Add/remove judges and participants
- View system logs and manage permissions

### Judge Interface
- Access `/judge.php` to submit scores
- View scoring history
- Filter and sort submissions

### Public Scoreboard
- Access `/index.php` to view live rankings
- Auto-updates every 5 seconds
- Sort by different criteria

## Security Considerations

1. **Database**
   - Use strong passwords
   - Limit database user privileges
   - Regular backups

2. **Authentication**
   - Change default admin credentials
   - Implement strong password policy
   - Enable rate limiting

3. **File Permissions**
   - Restrict direct access to includes/
   - Protect configuration files
   - Secure log files

## API Documentation

### Score Submission
```
POST /api/submit_score.php
{
    "judge_name": "string",
    "participant_id": "integer",
    "score": "float"
}
```

### User Management
```
POST /api/manage_users.php
{
    "action": "string",
    "username": "string",
    "password": "string",
    "role": "string"
}
```


## License

This project is licensed under the MIT License - see the LICENSE file for details.

