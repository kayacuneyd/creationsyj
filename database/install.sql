-- ============================================
-- Creations JY - Complete Database Installation
-- ============================================
-- This file contains everything needed for a fresh installation:
-- 1. All database tables (schema)
-- 2. Basic seed data (languages, admin user, site settings)
-- 3. Sample products (6 example products with categories)
--
-- Usage:
--   mysql -u username -p database_name < database/install.sql
--   OR import via phpMyAdmin
--
-- IMPORTANT: This will create a fresh database. 
-- If you already have data, make a backup first!
-- ============================================

-- ============================================
-- 1. DATABASE SCHEMA
-- ============================================

-- Language/Localization Table
CREATE TABLE IF NOT EXISTS languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(2) NOT NULL UNIQUE, -- 'fr', 'en'
    name VARCHAR(50) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table (Multilingual)
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS category_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    language_code VARCHAR(2) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(code),
    UNIQUE KEY unique_translation (category_id, language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_translations (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Images Table
CREATE TABLE IF NOT EXISTS product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    display_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_order (product_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'editor') DEFAULT 'editor',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp Inquiries Log
CREATE TABLE IF NOT EXISTS whatsapp_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    customer_name VARCHAR(255),
    message TEXT,
    inquiry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_date (inquiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log (Admin actions tracking)
CREATE TABLE IF NOT EXISTS activity_logs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts (Rate limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL COMMENT 'IP address or username',
    attempt_type ENUM('ip', 'username') NOT NULL,
    attempts INT DEFAULT 1,
    blocked_until TIMESTAMP NULL,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier, attempt_type),
    INDEX idx_blocked (blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. BASIC SEED DATA
-- ============================================

-- Insert default languages
INSERT INTO languages (code, name, is_default) VALUES 
('fr', 'Français', TRUE),
('en', 'English', FALSE)
ON DUPLICATE KEY UPDATE name = VALUES(name), is_default = VALUES(is_default);

-- Insert default admin user
-- Password: changeme123 (CHANGE THIS IN PRODUCTION!)
-- To generate a new password hash, use: php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES 
('admin', 'yasemin@creationsjy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yasemin Jemmely', 'super_admin')
ON DUPLICATE KEY UPDATE email = VALUES(email), password_hash = VALUES(password_hash), full_name = VALUES(full_name), role = VALUES(role);

-- Insert essential site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name_fr', 'Créations JY'),
('site_name_en', 'Creations JY'),
('whatsapp_number', '+41XXXXXXXXX'),
('instagram_url', 'https://instagram.com/creationsjy'),
('contact_email', 'contact@creationsjy.com'),
('products_per_page', '12')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================
-- 3. SAMPLE PRODUCTS (Demo/Testing)
-- ============================================

-- Create sample categories
INSERT INTO categories (slug) VALUES 
('upcycled-furniture'),
('decorative-items'),
('art-pieces')
ON DUPLICATE KEY UPDATE slug = VALUES(slug);

-- Category translations
INSERT INTO category_translations (category_id, language_code, name, description) 
SELECT c.id, 'fr', 'Mobilier Upcyclé', 'Meubles uniques créés à partir de matériaux recyclés'
FROM categories c WHERE c.slug = 'upcycled-furniture'
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO category_translations (category_id, language_code, name, description) 
SELECT c.id, 'en', 'Upcycled Furniture', 'Unique furniture created from recycled materials'
FROM categories c WHERE c.slug = 'upcycled-furniture'
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO category_translations (category_id, language_code, name, description) 
SELECT c.id, 'fr', 'Objets Décoratifs', 'Pièces décoratives faites main'
FROM categories c WHERE c.slug = 'decorative-items'
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO category_translations (category_id, language_code, name, description) 
SELECT c.id, 'en', 'Decorative Items', 'Handmade decorative pieces'
FROM categories c WHERE c.slug = 'decorative-items'
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO category_translations (category_id, language_code, name, description) 
SELECT c.id, 'fr', 'Pièces Artistiques', 'Créations artistiques originales'
FROM categories c WHERE c.slug = 'art-pieces'
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO category_translations (category_id, language_code, name, description) 
SELECT c.id, 'en', 'Art Pieces', 'Original artistic creations'
FROM categories c WHERE c.slug = 'art-pieces'
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Product 1: Vintage Chair
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'vintage-chair-upcycled', 'CHR-001', 'available', 1
FROM categories WHERE slug = 'upcycled-furniture' LIMIT 1
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), slug = VALUES(slug), status = VALUES(status), featured = VALUES(featured);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'CHR-001' LIMIT 1), 'fr', 'Chaise Vintage Upcyclée', 
'Une magnifique chaise vintage restaurée et transformée avec une touche moderne. Cette pièce unique allie le charme d''antan à un design contemporain.',
'Bois de récupération, tissu écologique, peinture à l''eau',
'45 x 50 x 90 cm',
'Chaise Vintage Upcyclée | Créations JY',
'Découvrez cette chaise vintage upcyclée unique, restaurée avec soin et transformée en pièce design moderne.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'CHR-001' LIMIT 1), 'en', 'Upcycled Vintage Chair',
'A beautiful vintage chair restored and transformed with a modern touch. This unique piece combines old-world charm with contemporary design.',
'Reclaimed wood, eco-friendly fabric, water-based paint',
'45 x 50 x 90 cm',
'Upcycled Vintage Chair | Creations JY',
'Discover this unique upcycled vintage chair, carefully restored and transformed into a modern design piece.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

-- Product 2: Decorative Mirror
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'decorative-mirror-handmade', 'MIR-001', 'available', 1
FROM categories WHERE slug = 'decorative-items' LIMIT 1
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), slug = VALUES(slug), status = VALUES(status), featured = VALUES(featured);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'MIR-001' LIMIT 1), 'fr', 'Miroir Décoratif Fait Main',
'Un miroir décoratif unique créé à partir de matériaux recyclés. Le cadre est orné de détails artisanaux qui lui confèrent un caractère exceptionnel.',
'Bois de palette, miroir recyclé, finition naturelle',
'60 x 80 cm',
'Miroir Décoratif Fait Main | Créations JY',
'Miroir décoratif unique créé à partir de matériaux recyclés avec un cadre artisanal exceptionnel.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'MIR-001' LIMIT 1), 'en', 'Handmade Decorative Mirror',
'A unique decorative mirror created from recycled materials. The frame is adorned with artisanal details that give it exceptional character.',
'Pallet wood, recycled mirror, natural finish',
'60 x 80 cm',
'Handmade Decorative Mirror | Creations JY',
'Unique decorative mirror created from recycled materials with an exceptional artisanal frame.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

-- Product 3: Wall Art Piece
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'wall-art-recycled', 'ART-001', 'available', 0
FROM categories WHERE slug = 'art-pieces' LIMIT 1
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), slug = VALUES(slug), status = VALUES(status), featured = VALUES(featured);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'ART-001' LIMIT 1), 'fr', 'Œuvre Murale Recyclée',
'Une création artistique unique réalisée à partir de matériaux recyclés. Cette pièce apporte une touche d''originalité à votre intérieur.',
'Bois de récupération, métal recyclé, peinture écologique',
'50 x 70 cm',
'Œuvre Murale Recyclée | Créations JY',
'Création artistique unique réalisée à partir de matériaux recyclés pour apporter originalité à votre intérieur.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'ART-001' LIMIT 1), 'en', 'Recycled Wall Art',
'A unique artistic creation made from recycled materials. This piece adds a touch of originality to your interior.',
'Reclaimed wood, recycled metal, eco-friendly paint',
'50 x 70 cm',
'Recycled Wall Art | Creations JY',
'Unique artistic creation made from recycled materials to add originality to your interior.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

-- Product 4: Side Table
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'side-table-upcycled', 'TBL-001', 'reserved', 0
FROM categories WHERE slug = 'upcycled-furniture' LIMIT 1
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), slug = VALUES(slug), status = VALUES(status), featured = VALUES(featured);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'TBL-001' LIMIT 1), 'fr', 'Table d''Appoint Upcyclée',
'Une table d''appoint élégante créée à partir de matériaux de récupération. Parfaite pour ajouter une touche de caractère à votre salon.',
'Bois de palette, pieds métalliques recyclés',
'40 x 40 x 55 cm',
'Table d''Appoint Upcyclée | Créations JY',
'Table d''appoint élégante créée à partir de matériaux de récupération pour votre salon.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'TBL-001' LIMIT 1), 'en', 'Upcycled Side Table',
'An elegant side table created from reclaimed materials. Perfect for adding a touch of character to your living room.',
'Pallet wood, recycled metal legs',
'40 x 40 x 55 cm',
'Upcycled Side Table | Creations JY',
'Elegant side table created from reclaimed materials for your living room.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

-- Product 5: Decorative Vase
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'decorative-vase-handmade', 'VAS-001', 'available', 0
FROM categories WHERE slug = 'decorative-items' LIMIT 1
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), slug = VALUES(slug), status = VALUES(status), featured = VALUES(featured);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'VAS-001' LIMIT 1), 'fr', 'Vase Décoratif Fait Main',
'Un vase décoratif unique créé à partir de matériaux recyclés. Cette pièce artisanale apporte une touche naturelle à votre décoration.',
'Verre recyclé, finition naturelle',
'Hauteur: 30 cm, Diamètre: 15 cm',
'Vase Décoratif Fait Main | Créations JY',
'Vase décoratif unique créé à partir de matériaux recyclés pour votre décoration intérieure.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'VAS-001' LIMIT 1), 'en', 'Handmade Decorative Vase',
'A unique decorative vase created from recycled materials. This artisanal piece adds a natural touch to your decoration.',
'Recycled glass, natural finish',
'Height: 30 cm, Diameter: 15 cm',
'Handmade Decorative Vase | Creations JY',
'Unique decorative vase created from recycled materials for your interior decoration.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

-- Product 6: Sold Item (Example)
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'sold-art-piece', 'ART-002', 'sold', 0
FROM categories WHERE slug = 'art-pieces' LIMIT 1
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), slug = VALUES(slug), status = VALUES(status), featured = VALUES(featured);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'ART-002' LIMIT 1), 'fr', 'Pièce Artistique (Vendue)',
'Cette pièce artistique unique a été vendue. Découvrez nos autres créations disponibles.',
'Bois de récupération, métal recyclé',
'40 x 60 cm',
'Pièce Artistique Vendue | Créations JY',
'Cette pièce artistique unique a été vendue. Découvrez nos autres créations disponibles.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT (SELECT id FROM products WHERE sku = 'ART-002' LIMIT 1), 'en', 'Art Piece (Sold)',
'This unique art piece has been sold. Discover our other available creations.',
'Reclaimed wood, recycled metal',
'40 x 60 cm',
'Art Piece Sold | Creations JY',
'This unique art piece has been sold. Discover our other available creations.'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), materials = VALUES(materials), dimensions = VALUES(dimensions), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description);

-- ============================================
-- Installation Complete!
-- ============================================
-- 
-- What was installed:
-- - All database tables (schema)
-- - Default languages (FR, EN)
-- - Admin user (username: admin, password: changeme123)
-- - Site settings
-- - 3 sample categories
-- - 6 sample products (with FR/EN translations)
--
-- Next steps:
-- 1. Change the admin password in production!
-- 2. Update site settings (WhatsApp number, Instagram URL, etc.)
-- 3. Add real product images via admin panel
-- 4. Customize product content
--
-- Note: Sample products will use placeholder.jpg as no images are uploaded.
-- To add images, use the admin panel after installation.
-- ============================================

