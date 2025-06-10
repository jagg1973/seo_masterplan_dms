# SEO Masterplan Document Management System

A PHP-based document management system for SEO professionals.

## Development Environment

**Note**: This project is designed to run with PHP and MySQL. The current WebContainer environment provides a static file server for development preview only.

### For Full PHP Development:

1. Install PHP 7.4 or higher
2. Install Composer
3. Install MySQL/MariaDB
4. Run `composer install` to install PHP dependencies
5. Configure database settings in `config/database.php`
6. Start PHP development server: `php -S localhost:8000`

### Current WebContainer Preview:

- Uses Node.js http-server for static file serving
- PHP files will be served as static content (no server-side processing)
- Database functionality will not work in this environment

## Features

- Document upload and management
- User authentication (admin and client)
- Category management
- Search functionality
- Branding customization
- Payment verification integration

## File Structure

- `/admin/` - Admin panel files
- `/config/` - Configuration files
- `/core/` - Core helper functions
- `/uploads/` - Document storage
- `/assets/` - CSS and JavaScript files
- `/api/` - API endpoints

## Requirements

- PHP >= 7.4
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

## Installation

1. Clone the repository
2. Run `composer install`
3. Configure database connection
4. Set up web server to point to project root
5. Access admin panel at `/admin/`

## License

MIT License