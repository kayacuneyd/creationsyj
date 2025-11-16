# Creations JY - Phase 2 Development Plan (DR_v2)

## Overview
Bu plan, mevcut projede roadmap'te belirtilen ancak henüz tamamlanmamış özellikleri 4 fazda tamamlamayı hedefliyor.

---

## **Faz 1: Kritik Eksikler (1-2 gün)**

### 1.1 Ürün Oluştururken Görsel Yükleme
- `admin/products/create.php` formuna görsel yükleme alanı ekle
- Multiple file upload desteği
- Drag & drop interface (opsiyonel, basit file input da yeterli)
- Upload edilen görselleri göster (preview)

### 1.2 SEO Alanları Admin Formlarına Ekleme
- `admin/products/create.php` ve `edit.php`'ye meta_title ve meta_description alanları ekle (FR/EN)
- Bu alanları database'e kaydet
- Product sayfalarında bu meta bilgileri kullan

### 1.3 Database Seed Data Scripti
- `database/seed.sql` dosyası oluştur
- Default languages (fr, en) insert
- Default admin user insert (password: changeme123)
- Essential site settings insert

### 1.4 Structured Data (JSON-LD) Product Sayfalarına Ekleme
- `includes/meta-tags.php`'de product için JSON-LD structured data ekle
- Product sayfalarında (`fr/produit.php`, `en/product.php`) structured data kullan

### 1.5 Language Switcher Header'a Ekleme
- `includes/header.php`'ye language switcher ekle
- FR/EN arasında geçiş yapabilme

---

## **Faz 2: UX İyileştirmeleri (2-3 gün)**

### 2.1 Lightbox/Modal Galeri (Product Sayfası)
- Product sayfasında görseller için lightbox/modal implementasyonu
- Full-screen görüntüleme
- Keyboard navigation (arrow keys)
- Close button

### 2.2 WhatsApp Inquiry Form (Product Sayfası)
- Roadmap'teki gibi form ekle (customer_name, message alanları)
- Form submit edildiğinde WhatsApp link oluştur ve aç
- Inquiry logging'i form submit ile entegre et

### 2.3 Ürün Sıralama Seçenekleri (Products Listing)
- `fr/produits.php` ve `en/products.php`'ye sort dropdown ekle
- Seçenekler: Newest, Oldest, A-Z (title'a göre)
- Sort parametresini query'ye ekle

### 2.4 Görsel Sıralama (Drag-Drop) Admin Panelde
- `admin/products/edit.php`'de mevcut görselleri listele
- Display order'ı değiştirebilme (basit input veya drag-drop)
- Primary image seçimi

---

## **Faz 3: Admin Panel Geliştirmeleri (1-2 gün)**

### 3.1 Rate Limiting (Login)
- `admin/login.php`'de rate limiting implementasyonu
- IP bazlı veya username bazlı
- Başarısız login denemelerini logla
- Belirli sayıda denemeden sonra geçici bloklama

### 3.2 Activity Logs Kaydı
- Admin actions için activity_logs tablosuna kayıt
- Product create/edit/delete
- Category create/edit/delete
- Settings değişiklikleri
- Admin dashboard'da son aktiviteleri göster

### 3.3 SEO Settings Tab
- `admin/settings/` altına `seo.php` ekle
- Google Analytics ID
- Default meta tags (FR/EN)
- Site description

### 3.4 Site Settings'ten Dinamik Değer Kullanımı
- Contact sayfasında WhatsApp number, Instagram URL gibi değerleri site_settings'ten çek
- Hardcoded değerleri kaldır

---

## **Faz 4: İyileştirmeler (1-2 gün)**

### 4.1 WebP Format Desteği
- Image upload handler'da WebP conversion
- `<picture>` element kullanımı (WebP + fallback)
- Product sayfalarında WebP desteği

### 4.2 Instagram Feed Entegrasyonu (Opsiyonel)
- Homepage'e Instagram feed bölümü ekle
- Basit embed veya API kullanımı
- Settings'ten Instagram URL kontrolü

### 4.3 Admin Panel için Ayrı CSS
- `assets/css/admin.css` oluştur
- Admin panel sayfalarında admin.css kullan
- Main.css'den ayrı tut

---

## Implementation Order

1. **Faz 1** - Kritik eksikler (öncelikli)
2. **Faz 2** - UX iyileştirmeleri (kullanıcı deneyimi)
3. **Faz 3** - Admin panel geliştirmeleri (güvenlik ve yönetim)
4. **Faz 4** - İyileştirmeler (optimizasyon)

Her faz tamamlandığında test edilecek ve bir sonraki faza geçilecek.

---

## Progress Tracking

### Faz 1: Kritik Eksikler
- [ ] 1.1 Ürün Oluştururken Görsel Yükleme
- [ ] 1.2 SEO Alanları Admin Formlarına Ekleme
- [ ] 1.3 Database Seed Data Scripti
- [ ] 1.4 Structured Data (JSON-LD) Product Sayfalarına Ekleme
- [ ] 1.5 Language Switcher Header'a Ekleme

### Faz 2: UX İyileştirmeleri
- [ ] 2.1 Lightbox/Modal Galeri (Product Sayfası)
- [ ] 2.2 WhatsApp Inquiry Form (Product Sayfası)
- [ ] 2.3 Ürün Sıralama Seçenekleri (Products Listing)
- [ ] 2.4 Görsel Sıralama (Drag-Drop) Admin Panelde

### Faz 3: Admin Panel Geliştirmeleri
- [ ] 3.1 Rate Limiting (Login)
- [ ] 3.2 Activity Logs Kaydı
- [ ] 3.3 SEO Settings Tab
- [ ] 3.4 Site Settings'ten Dinamik Değer Kullanımı

### Faz 4: İyileştirmeler
- [ ] 4.1 WebP Format Desteği
- [ ] 4.2 Instagram Feed Entegrasyonu (Opsiyonel)
- [ ] 4.3 Admin Panel için Ayrı CSS

---

**Last Updated**: January 2025  
**Status**: Ready for Implementation 🚀

