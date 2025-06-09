<?php
// config/environment.php - Environment-specific configuration

// Detect environment
$environment = $_SERVER['APP_ENV'] ?? 'production';

switch ($environment) {
    case 'development':
    case 'dev':
        // Development settings
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        define('APP_DEBUG', true);
        break;
        
    case 'staging':
        // Staging settings
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);
        define('APP_DEBUG', false);
        break;
        
    case 'production':
    default:
        // Production settings
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        define('APP_DEBUG', false);
        break;
}

// Define environment
define('APP_ENV', $environment);

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('expose_php', 0);
?>