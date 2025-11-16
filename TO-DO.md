# Production Deployment - TO-DO List

## Ön Hazırlık

### 1. Database Migration Scripti Oluştur
- [ ] `database/migration_v2.sql` dosyası oluştur
- [ ] Mevcut production database'deki değişiklikleri uygula:
  - [ ] `whatsapp_inquiries` tablosunu güncelle (user_phone → customer_name, message)
  - [ ] `login_attempts` tablosunu ekle
  - [ ] `product_translations` tablosuna meta_title ve meta_description kolonlarını ekle (eğer yoksa)

### 2. Backup Al
- [ ] Production database'in tam backup'ını al
- [ ] Mevcut dosyaların backup'ını al (opsiyonel ama önerilir)

## Deployment Adımları

### 3. Local'de Git Commit ve Push
- [ ] Tüm değişiklikleri kontrol et: `git status`
- [ ] Değişiklikleri stage'e al: `git add .`
- [ ] Commit yap: `git commit -m "Faz 1-4: Tüm geliştirmeler tamamlandı"`
- [ ] Remote'a push et: `git push origin main` (veya `git push origin master`)

### 4. Production Server'da İşlemler

#### 4.1 SSH ile Server'a Bağlan
- [ ] SSH ile server'a bağlan: `ssh kullanici@server-ip` veya `ssh kullanici@domain.com`

#### 4.2 Proje Dizinine Git
- [ ] Proje dizinine git: `cd /path/to/creationsyj`
- [ ] (Hostinger genellikle: `cd ~/domains/domain.com/public_html`)

#### 4.3 Git Pull
- [ ] Git pull yap: `git pull origin main` (veya `git pull origin master`)

#### 4.4 Database Migration Çalıştır
- [ ] Migration scriptini çalıştır:
  ```bash
  mysql -u database_user -p database_name < database/migration_v2.sql
  ```
- [ ] VEYA phpMyAdmin üzerinden `migration_v2.sql` dosyasını import et

#### 4.5 WebP Dizinlerini Oluştur
- [ ] WebP klasörlerini oluştur:
  ```bash
  mkdir -p uploads/products/thumbnail/webp
  mkdir -p uploads/products/medium/webp
  mkdir -p uploads/products/large/webp
  ```
- [ ] İzinleri ayarla:
  ```bash
  chmod 755 uploads/products/thumbnail/webp
  chmod 755 uploads/products/medium/webp
  chmod 755 uploads/products/large/webp
  ```

#### 4.6 Dosya İzinlerini Kontrol Et
- [ ] Uploads klasörü yazılabilir olmalı: `chmod -R 755 uploads/`
- [ ] PHP dosyaları okunabilir olmalı:
  ```bash
  chmod 644 *.php
  chmod 644 includes/*.php
  chmod 644 admin/**/*.php
  ```

### 5. Kontrol ve Test

#### 5.1 Admin Panel Kontrolü
- [ ] `/admin/login.php` - Rate limiting çalışıyor mu?
- [ ] `/admin/index.php` - Activity logs görünüyor mu?
- [ ] `/admin/settings/seo.php` - Yeni SEO settings sayfası açılıyor mu?
- [ ] `/admin/products/edit.php` - Görsel yönetimi çalışıyor mu?

#### 5.2 Frontend Kontrolü
- [ ] Product sayfalarında WebP görseller yükleniyor mu?
- [ ] Lightbox çalışıyor mu? (klavye navigasyonu, önceki/sonraki butonları)
- [ ] WhatsApp inquiry formu çalışıyor mu?
- [ ] Language switcher çalışıyor mu?
- [ ] Product listing'de sıralama çalışıyor mu?

#### 5.3 Database Kontrolü
- [ ] `login_attempts` tablosu var mı?
- [ ] `whatsapp_inquiries` tablosunda yeni kolonlar (customer_name, message) var mı?
- [ ] `activity_logs` tablosunda kayıtlar oluşuyor mu?
- [ ] `product_translations` tablosunda meta_title ve meta_description kolonları var mı?

## Önemli Notlar

- **Database Backup**: Migration öncesi mutlaka backup alın
- **WebP Desteği**: PHP'de `imagewebp()` fonksiyonu aktif olmalı (genellikle GD extension ile gelir)
- **Cache Temizleme**: Eğer cache kullanıyorsanız, cache'i temizleyin
- **Hata Logları**: Deployment sonrası error loglarını kontrol edin

## Rollback Planı (Gerekirse)

Eğer bir sorun çıkarsa:
1. Git ile önceki commit'e dön: `git reset --hard HEAD~1`
2. Database backup'ından geri yükle
3. Hataları düzelt ve tekrar dene

## Migration Script İçeriği (migration_v2.sql)

Aşağıdaki SQL komutlarını `database/migration_v2.sql` dosyasına ekleyin:

```sql
-- Migration v2: Faz 1-4 Güncellemeleri

-- 1. whatsapp_inquiries tablosunu güncelle
-- Önce mevcut verileri yedekle (opsiyonel)
-- ALTER TABLE whatsapp_inquiries ADD COLUMN customer_name VARCHAR(255) AFTER product_id;
-- ALTER TABLE whatsapp_inquiries ADD COLUMN message TEXT AFTER customer_name;
-- UPDATE whatsapp_inquiries SET customer_name = user_phone WHERE customer_name IS NULL;
-- ALTER TABLE whatsapp_inquiries DROP COLUMN user_phone;

-- Eğer tablo zaten yeni yapıdaysa, sadece kolonları ekle:
ALTER TABLE whatsapp_inquiries 
  ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255) AFTER product_id,
  ADD COLUMN IF NOT EXISTS message TEXT AFTER customer_name;

-- Eğer user_phone kolonu varsa, verileri taşı ve sil:
-- (MySQL'de IF EXISTS kontrolü için stored procedure gerekebilir, 
--  bu yüzden phpMyAdmin'de manuel kontrol edin)

-- 2. login_attempts tablosunu ekle
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    attempt_type ENUM('ip', 'username') NOT NULL,
    attempts INT DEFAULT 1,
    blocked_until TIMESTAMP NULL,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier, attempt_type),
    INDEX idx_blocked (blocked_until)
);

-- 3. product_translations tablosuna meta kolonları ekle (eğer yoksa)
ALTER TABLE product_translations 
  ADD COLUMN IF NOT EXISTS meta_title VARCHAR(70) AFTER dimensions,
  ADD COLUMN IF NOT EXISTS meta_description VARCHAR(160) AFTER meta_title;

-- Not: MySQL'de IF NOT EXISTS direkt desteklenmez, 
-- bu yüzden phpMyAdmin'de manuel kontrol edin veya hata alırsanız görmezden gelin
```

## Hızlı Kontrol Komutları

```bash
# Database bağlantısını test et
mysql -u database_user -p database_name -e "SHOW TABLES;"

# Yeni tabloları kontrol et
mysql -u database_user -p database_name -e "SHOW TABLES LIKE 'login_attempts';"
mysql -u database_user -p database_name -e "DESCRIBE whatsapp_inquiries;"
mysql -u database_user -p database_name -e "DESCRIBE product_translations;"

# PHP WebP desteğini kontrol et
php -r "var_dump(function_exists('imagewebp'));"
```

