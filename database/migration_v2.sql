-- Migration v2: Faz 1-4 Güncellemeleri
-- Production database'e uygulanacak değişiklikler
-- ÖNEMLİ: Bu scripti çalıştırmadan önce mutlaka database backup alın!

-- ============================================
-- 1. whatsapp_inquiries tablosunu güncelle
-- ============================================

-- Önce mevcut yapıyı kontrol edin:
-- Eğer user_phone kolonu varsa, verileri taşıyın ve kolonu değiştirin
-- Eğer zaten customer_name ve message kolonları varsa, bu bölümü atlayın

-- Adım 1: Yeni kolonları ekle (eğer yoksa)
ALTER TABLE whatsapp_inquiries 
  ADD COLUMN customer_name VARCHAR(255) AFTER product_id,
  ADD COLUMN message TEXT AFTER customer_name;

-- Adım 2: Eğer user_phone kolonu varsa ve veri taşınması gerekiyorsa:
-- UPDATE whatsapp_inquiries SET customer_name = user_phone WHERE customer_name IS NULL AND user_phone IS NOT NULL;

-- Adım 3: user_phone kolonunu sil (eğer varsa)
-- ALTER TABLE whatsapp_inquiries DROP COLUMN user_phone;

-- Not: MySQL'de kolon varlığını kontrol etmek için stored procedure gerekir.
-- Bu yüzden phpMyAdmin'de manuel olarak kontrol edin:
-- - Eğer user_phone kolonu varsa: UPDATE ve DROP komutlarını çalıştırın
-- - Eğer customer_name ve message zaten varsa: Bu bölümü atlayın

-- ============================================
-- 2. login_attempts tablosunu ekle
-- ============================================

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
-- 3. product_translations tablosuna meta kolonları ekle
-- ============================================

-- Eğer kolonlar zaten varsa, hata alırsınız - görmezden gelebilirsiniz
ALTER TABLE product_translations 
  ADD COLUMN meta_title VARCHAR(70) AFTER dimensions,
  ADD COLUMN meta_description VARCHAR(160) AFTER meta_title;

-- ============================================
-- Kontrol Sorguları (Çalıştırmadan önce test edin)
-- ============================================

-- Tabloları kontrol et:
-- SHOW TABLES;

-- whatsapp_inquiries yapısını kontrol et:
-- DESCRIBE whatsapp_inquiries;

-- login_attempts tablosunu kontrol et:
-- DESCRIBE login_attempts;

-- product_translations yapısını kontrol et:
-- DESCRIBE product_translations;

