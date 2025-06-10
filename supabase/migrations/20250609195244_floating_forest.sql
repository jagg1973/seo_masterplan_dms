-- SEO Masterplan DMS Database Schema
-- Run this script to create the necessary database tables

CREATE DATABASE IF NOT EXISTS seo_masterplan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE seo_masterplan_db;

-- Users table (for admin users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL
);

-- Clients table (for client portal users)
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    website VARCHAR(255),
    company VARCHAR(100),
    access_level ENUM('basic', 'premium', 'enterprise') DEFAULT 'premium',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL
);

-- Document categories
CREATE TABLE IF NOT EXISTS document_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Documents table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    filename_orig VARCHAR(255) NOT NULL,
    filename_sys VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    filetype VARCHAR(100) NOT NULL,
    filesize INT NOT NULL,
    version VARCHAR(50),
    language VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_user (user_id),
    INDEX idx_title (title),
    FULLTEXT idx_search (title, description)
);

-- Branding settings
CREATE TABLE IF NOT EXISTS branding_settings (
    setting_name VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- Transaction log for payment verification
CREATE TABLE IF NOT EXISTS transaction_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paypal_transaction_id VARCHAR(100) UNIQUE NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    username_created VARCHAR(50),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'EUR',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction (paypal_transaction_id),
    INDEX idx_email (user_email)
);

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'super_admin');

-- Insert default document categories
INSERT IGNORE INTO document_categories (name, description) VALUES 
('C-Level Documents', 'Strategic documents and reports for executive review'),
('Management Level', 'Resources and guidelines for managers'),
('SEO Expert Levels', 'Technical documentation, tools, and advanced SEO strategies'),
('Supporting Files', 'General resources, templates, and other supporting materials');

-- Insert default branding settings
INSERT IGNORE INTO branding_settings (setting_name, setting_value) VALUES 
('primary_color', '#007bff'),
('logo_path', '');