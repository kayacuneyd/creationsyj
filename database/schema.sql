-- Creations JY - Database Schema

-- Language/Localization Table
CREATE TABLE languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(2) NOT NULL UNIQUE, -- 'fr', 'en'
    name VARCHAR(50) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table (Multilingual)
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug (slug)
);

CREATE TABLE category_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    language_code VARCHAR(2) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(code),
    UNIQUE KEY unique_translation (category_id, language_code)
);

-- Products Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    slug VARCHAR(150) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
    featured BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_category (category_id)
);

CREATE TABLE product_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    language_code VARCHAR(2) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    materials TEXT, -- Materials used in upcycling
    dimensions VARCHAR(100), -- Size info
    meta_title VARCHAR(70),
    meta_description VARCHAR(160),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(code),
    UNIQUE KEY unique_translation (product_id, language_code),
    FULLTEXT KEY fulltext_search (title, description)
);

-- Product Images Table
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    display_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_order (product_id, display_order)
);

-- Admin Users Table
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'editor') DEFAULT 'editor',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
);

-- WhatsApp Inquiries Log (Optional - for analytics)
CREATE TABLE whatsapp_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    customer_name VARCHAR(255),
    message TEXT,
    inquiry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_date (inquiry_date)
);

-- Site Settings Table
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity Log (Admin actions tracking)
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_admin (admin_id),
    INDEX idx_date (created_at)
);

-- Login Attempts (Rate limiting)
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL, -- IP address or username
    attempt_type ENUM('ip', 'username') NOT NULL,
    attempts INT DEFAULT 1,
    blocked_until TIMESTAMP NULL,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier, attempt_type),
    INDEX idx_blocked (blocked_until)
);

-- Base data inserts

-- Insert default languages
INSERT INTO languages (code, name, is_default) VALUES 
('fr', 'Français', TRUE),
('en', 'English', FALSE);

-- Insert default admin user (password: changeme123)
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES 
('admin', 'yasemin@creationsjy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yasemin Jemmely', 'super_admin');

-- Insert essential site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name_fr', 'Créations JY'),
('site_name_en', 'Creations JY'),
('whatsapp_number', '+41XXXXXXXXX'),
('instagram_url', 'https://instagram.com/creationsjy'),
('contact_email', 'contact@creationsjy.com'),
('products_per_page', '12');



