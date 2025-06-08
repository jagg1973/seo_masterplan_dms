<?php
// config/database.php

define('DB_HOST', 'localhost'); // Or your DB host, usually localhost for local dev
define('DB_NAME', 'seo_masterplan_db');
define('DB_USER', 'masterplan_user');      // Your MySQL username (default for XAMPP is root)
define('DB_PASS', 'Xx11422470@');          // Your MySQL password (default for XAMPP is empty)
define('DB_CHARSET', 'utf8mb4');

// Data Source Name (DSN)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In a real application, you'd log this error and show a generic message
    // For development, it's okay to show the error, but be careful in production
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?>