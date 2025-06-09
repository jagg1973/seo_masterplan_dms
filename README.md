# SEO Masterplan Document Management System

A comprehensive PHP-based document management system with separate admin and client portals.

## Features

- **Admin Portal**: Document and category management, branding customization
- **Client Portal**: Document browsing, search functionality, secure access
- **File Management**: Support for PDF, DOC, XLS, PPT, and other document formats
- **Search**: Full-text search across document titles and descriptions
- **Branding**: Customizable logo and color scheme
- **Security**: Role-based access control, secure file uploads
- **Payment Integration**: PayPal integration for client account creation

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for PHP dependencies)
- Node.js (for frontend dependencies)

### PHP Extensions Required

- PDO
- PDO MySQL
- FileInfo
- cURL
- JSON
- mbstring

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd seo-masterplan-dms
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Database Setup**
   - Create a MySQL database
   - Import the schema: `mysql -u username -p database_name < database/schema.sql`
   - Update database credentials in `config/database.php`

5. **Configuration**
   - Update `config/config.php` with your domain and paths
   - Set proper file permissions for the `uploads/` directory (755 or 775)
   - Configure your web server to point to the project root

6. **Security Setup**
   - Change default admin credentials after first login
   - Update database passwords
   - Configure SSL/HTTPS for production

## Default Credentials

- **Admin Login**: username: `admin`, password: `admin123`
- **Client accounts**: Created through PayPal payment verification

## File Structure

```
├── admin/                  # Admin portal files
├── api/                   # API endpoints
├── assets/                # CSS, JS, images
├── config/                # Configuration files
├── core/                  # Helper functions
├── database/              # Database schema
├── uploads/               # File storage
├── client_*.php           # Client portal files
├── login.php              # Unified login page
└── index.php              # Main entry point
```

## Usage

### Admin Portal
- Access: `/admin/` or `/login.php` (Admin tab)
- Manage document categories
- Upload and organize documents
- Customize branding (logo, colors)
- View system statistics

### Client Portal
- Access: `/login.php` (Client tab)
- Browse documents by category
- Search documents
- Download files
- View document previews

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- File upload validation and restrictions
- Session management
- CSRF protection ready
- Secure file storage outside web root option

## API Endpoints

- `/api/verify-payment.php` - PayPal payment verification

## Development

### Running Locally
```bash
# Start PHP development server
npm run dev
# or
php -S localhost:8000
```

### Code Quality
```bash
# Check PHP syntax
npm run lint
```

## Production Deployment

1. Set `display_errors = Off` in PHP configuration
2. Configure proper error logging
3. Set up SSL certificates
4. Configure database backups
5. Set restrictive file permissions
6. Enable security headers in `.htaccess`

## Support

For support and documentation, contact the development team.

## License

This project is proprietary software. All rights reserved.