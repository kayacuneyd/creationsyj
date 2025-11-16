-- Creations JY - Seed Data
-- Run this after importing schema.sql

-- Insert default languages
INSERT INTO languages (code, name, is_default) VALUES 
('fr', 'Français', TRUE),
('en', 'English', FALSE)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insert default admin user
-- Password: changeme123 (CHANGE THIS IN PRODUCTION!)
-- To generate a new password hash, use: php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES 
('admin', 'yasemin@creationsjy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yasemin Jemmely', 'super_admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Insert essential site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name_fr', 'Créations JY'),
('site_name_en', 'Creations JY'),
('whatsapp_number', '+41XXXXXXXXX'),
('instagram_url', 'https://instagram.com/creationsjy'),
('contact_email', 'contact@creationsjy.com'),
('products_per_page', '12')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

