# Creations JY - Development Roadmap
**Project**: Upcycling Products Website  
**Client**: Yasemin Jemmely (Gruyer, Switzerland)  
**Developer**: Cüneyt Kaya (Kornwestheim)  
**Domain**: https://creationsjy.com  
**Start Date**: November 2025  

---

## 📋 Project Overview

### Business Model
Yasemin Jemmely transforms second-hand, antique, and unused materials into unique, reusable decorative items. The website serves as a digital showcase where customers can:
- Browse products with detailed photos
- Request price quotes via WhatsApp Business API
- Place orders through WhatsApp integration
- Switch between French (primary) and English languages

### Target Audience
- Eco-conscious consumers
- Vintage/shabby chic enthusiasts
- Swiss/European market (primarily French-speaking)
- Mobile-first users (expected 70%+ mobile traffic)

---

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **Tailwind CSS** (CDN) - Utility-first styling
- **Vanilla JavaScript** - Lightweight interactions
- **Alpine.js** (optional) - For reactive UI components

### Backend
- **PHP 8.1+** - Server-side logic
- **MySQL 8.0+** - Database management
- **PDO** - Secure database connections

### Hosting
- **Hostinger Shared Hosting**
  - PHP/MySQL support
  - SSL certificate (Let's Encrypt)
  - FTP/cPanel access
  - .htaccess support for URL rewriting

### Third-Party APIs
- **WhatsApp Business API** (via Meta/Facebook)
- **TinyPNG API** (optional) - Image optimization
- **reCAPTCHA v3** (optional) - Spam protection

---

## 🎨 Design System

### Color Palette (Vintage Shabby Chic Pink Tones)

```css
/* Primary Colors */
--dusty-rose: #D4A5A5;        /* Main brand color */
--blush-pink: #F7D5D0;        /* Soft backgrounds */
--antique-pink: #E8B4B8;      /* Accents */

/* Secondary Colors */
--cream: #FAF6F0;             /* Light backgrounds */
--sage-green: #B8C5B4;        /* Complementary accent */
--warm-gray: #8B7F7F;         /* Text & borders */

/* Neutral Tones */
--off-white: #FFFCF8;         /* Card backgrounds */
--charcoal: #3A3232;          /* Primary text */
--light-gray: #E8E4DD;        /* Dividers */

/* Status Colors */
--available: #B8C5B4;         /* In stock */
--sold: #8B7F7F;              /* Sold out */
--reserved: #E8B4B8;          /* Reserved */
```

### Typography
```css
/* Google Fonts CDN */
font-family: 'Cormorant Garamond', serif; /* Headings */
font-family: 'Lato', sans-serif;          /* Body text */
```

### Design Principles
- **Vintage Aesthetics**: Soft shadows, rounded corners, texture overlays
- **Whitespace**: Generous padding for elegant feel
- **Mobile-First**: Touch-friendly buttons (min 44x44px)
- **Image-Centric**: Large product photos with lazy loading

---

## 🗄️ Database Schema

```sql
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
    user_phone VARCHAR(20),
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
```

---

## 📁 File Structure

```
creationsjy.com/
├── index.php                      # Language detection & redirect
├── .htaccess                      # URL rewriting rules
├── robots.txt                     # SEO directives
├── sitemap.xml                    # Auto-generated sitemap
│
├── fr/                            # French version
│   ├── index.php                  # Homepage FR
│   ├── a-propos.php              # About page FR
│   ├── produits.php              # Products listing FR
│   ├── produit.php               # Single product FR
│   └── contact.php               # Contact page FR
│
├── en/                            # English version
│   ├── index.php                  # Homepage EN
│   ├── about.php                 # About page EN
│   ├── products.php              # Products listing EN
│   ├── product.php               # Single product EN
│   └── contact.php               # Contact page EN
│
├── admin/                         # Admin panel
│   ├── index.php                 # Dashboard
│   ├── login.php                 # Authentication
│   ├── logout.php                # Session destroy
│   ├── products/                 # Product management
│   │   ├── index.php            # Product list
│   │   ├── create.php           # Add new product
│   │   ├── edit.php             # Edit product
│   │   └── delete.php           # Delete handler
│   ├── categories/               # Category management
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   ├── settings/                 # Site settings
│   │   ├── general.php          # General settings
│   │   ├── whatsapp.php         # WhatsApp config
│   │   └── users.php            # User management
│   └── assets/                   # Admin-specific assets
│       ├── css/
│       ├── js/
│       └── images/
│
├── includes/                      # PHP includes
│   ├── config.php                # Database & constants
│   ├── functions.php             # Helper functions
│   ├── db.php                    # Database connection
│   ├── language.php              # Translation handler
│   ├── auth.php                  # Authentication logic
│   └── whatsapp.php              # WhatsApp API handler
│
├── assets/                        # Public assets
│   ├── css/
│   │   ├── main.css             # Custom styles
│   │   └── admin.css            # Admin panel styles
│   ├── js/
│   │   ├── main.js              # Frontend interactions
│   │   ├── lazy-load.js         # Image lazy loading
│   │   └── admin.js             # Admin panel JS
│   └── images/
│       ├── logo.svg
│       ├── placeholder.jpg
│       └── textures/            # Vintage textures/overlays
│
├── uploads/                       # User-uploaded content
│   ├── products/                 # Product images
│   │   ├── original/
│   │   ├── thumbnail/           # 400x400px
│   │   └── medium/              # 800x800px
│   └── categories/               # Category images
│
├── languages/                     # Translation files (optional JSON)
│   ├── fr.json
│   └── en.json
│
└── vendor/                        # Third-party libraries (if using Composer)
    └── autoload.php
```

---

## 🚀 Development Phases

### **Phase 1: Foundation Setup (Week 1)**

#### 1.1 Environment Setup
- [ ] Set up local development environment (XAMPP/MAMP/Laragon)
- [ ] Create database: `creationsjy_db`
- [ ] Import schema from above SQL
- [ ] Configure `.htaccess` for URL rewriting
- [ ] Set up Git repository for version control

#### 1.2 Core Files
```php
// includes/config.php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'creationsjy_db');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('SITE_URL', 'https://creationsjy.com');
define('DEFAULT_LANG', 'fr');
define('WHATSAPP_NUMBER', '+41XXXXXXXXX'); // Yasemin's WhatsApp Business number
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
?>
```

```php
// includes/db.php
<?php
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
```

```apache
# .htaccess (Root level)
RewriteEngine On
RewriteBase /

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Language detection for root access
RewriteCond %{REQUEST_URI} ^/$
RewriteCond %{HTTP:Accept-Language} ^fr [NC]
RewriteRule ^$ /fr/ [R=302,L]

RewriteCond %{REQUEST_URI} ^/$
RewriteRule ^$ /en/ [R=302,L]

# Remove trailing slashes (except for directories)
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [L,R=301]

# Block direct access to includes
RewriteRule ^includes/ - [F,L]
RewriteRule ^vendor/ - [F,L]

# Protect admin login attempts (rate limiting via fail2ban later)
RewriteCond %{REQUEST_URI} ^/admin/login\.php$
RewriteRule ^ - [E=rate_limit:1]
```

#### 1.3 Insert Base Data
```sql
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
```

---

### **Phase 2: Frontend Development (Week 1-2)**

#### 2.1 Homepage (`/fr/index.php` & `/en/index.php`)
**Sections:**
1. **Hero Section**
   - Full-width image/video background
   - Tagline in FR: "Donnez une seconde vie à vos objets"
   - Tagline in EN: "Give a Second Life to Your Objects"
   - CTA button to products page

2. **Featured Products** (4-6 products)
   - Grid layout (2 cols mobile, 3-4 cols desktop)
   - Lazy-loaded images
   - Quick "Contact via WhatsApp" button

3. **About Preview**
   - Short bio with photo of Yasemin
   - Link to full about page

4. **Instagram Feed** (Optional)
   - Embedded or API-fetched latest posts

#### 2.2 Products Listing Page
**Features:**
- Filter by category (sidebar on desktop, accordion on mobile)
- Status filter (Available/Sold/Reserved)
- Sort options: Newest, Oldest, A-Z
- Pagination (12 products per page)
- Search functionality (AJAX-powered)

**Product Card Design:**
```html
<div class="product-card bg-off-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    <div class="relative">
        <img src="thumbnail.jpg" alt="Product" class="w-full h-64 object-cover lazy">
        <span class="absolute top-2 right-2 bg-available text-white px-3 py-1 rounded-full text-sm">
            Disponible
        </span>
    </div>
    <div class="p-4">
        <h3 class="text-xl font-serif text-charcoal mb-2">Vintage Chair</h3>
        <p class="text-warm-gray text-sm mb-4 line-clamp-2">
            Transformed from reclaimed wood...
        </p>
        <button class="w-full bg-dusty-rose text-white py-2 rounded-md hover:bg-antique-pink transition">
            Demander un prix
        </button>
    </div>
</div>
```

#### 2.3 Single Product Page
**Layout:**
- **Left**: Image gallery (main image + thumbnails)
  - Lightbox/modal for full-screen view
  - Swipe gestures on mobile
- **Right**: Product details
  - Title, SKU, Status badge
  - Description (collapsible on mobile)
  - Materials used
  - Dimensions
  - WhatsApp inquiry form

**WhatsApp Inquiry Form:**
```html
<form id="whatsapp-inquiry-form" class="space-y-4">
    <input type="text" name="customer_name" placeholder="Votre nom" required 
           class="w-full px-4 py-2 border border-light-gray rounded-md">
    
    <textarea name="message" placeholder="Votre message (optionnel)" rows="3"
              class="w-full px-4 py-2 border border-light-gray rounded-md"></textarea>
    
    <button type="submit" class="w-full bg-dusty-rose text-white py-3 rounded-md hover:bg-antique-pink transition flex items-center justify-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><!-- WhatsApp icon --></svg>
        Contacter via WhatsApp
    </button>
</form>

<script>
document.getElementById('whatsapp-inquiry-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = this.customer_name.value;
    const message = this.message.value;
    const productTitle = "<?php echo $product['title']; ?>";
    const productUrl = window.location.href;
    
    const whatsappMessage = `Bonjour, je m'appelle ${name}. Je suis intéressé(e) par ce produit: ${productTitle}. ${message ? message + ' ' : ''}Lien: ${productUrl}`;
    
    const whatsappUrl = `https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=${encodeURIComponent(whatsappMessage)}`;
    
    window.open(whatsappUrl, '_blank');
    
    // Log inquiry to database (optional analytics)
    fetch('/api/log-inquiry.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: <?php echo $product['id']; ?>,
            phone: '' // Can add phone field if needed
        })
    });
});
</script>
```

#### 2.4 About Page
**Content Structure:**
1. Hero image of Yasemin in her workshop
2. Story section (2-3 paragraphs)
3. Philosophy about upcycling
4. Process explanation (with icons/illustrations)
5. Location info (Gruyer, Switzerland)
6. CTA to products or contact

#### 2.5 Contact Page
**Elements:**
- Contact form (sends email via PHP `mail()` or SMTP)
- WhatsApp direct link
- Email address
- Instagram link
- Optional: Embedded Google Maps (if physical visits allowed)

---

### **Phase 3: Admin Panel Development (Week 2-3)**

#### 3.1 Authentication System
**Features:**
- Secure login with rate limiting
- Password hashing (bcrypt)
- Session management
- "Remember me" functionality
- Password reset via email

```php
// admin/login.php (simplified)
<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_role'] = $user['role'];
        
        // Update last login
        $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")
            ->execute([$user['id']]);
        
        header('Location: /admin/');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
```

#### 3.2 Dashboard Overview
**Widgets:**
- Total products count
- Available/Sold/Reserved breakdown
- Recent WhatsApp inquiries (last 10)
- Most viewed products (top 5)
- Quick actions (Add Product, Add Category)

#### 3.3 Product Management (CRUD)

**Create Product Flow:**
1. Basic info (Title FR/EN, Category, SKU)
2. Description & details (Description FR/EN, Materials, Dimensions)
3. Image upload (Multiple files, drag-drop, reorder)
4. SEO fields (Meta title/description FR/EN)
5. Status selection (Available/Sold/Reserved)
6. Preview before saving

**Image Upload Handler:**
```php
// admin/products/upload-image.php
<?php
if (isset($_FILES['images'])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $filename = uniqid() . '_' . $_FILES['images']['name'][$key];
        $upload_path = UPLOAD_DIR . 'products/original/' . $filename;
        
        if (move_uploaded_file($tmp_name, $upload_path)) {
            // Create thumbnail
            createThumbnail($upload_path, UPLOAD_DIR . 'products/thumbnail/' . $filename, 400, 400);
            
            // Create medium size
            createThumbnail($upload_path, UPLOAD_DIR . 'products/medium/' . $filename, 800, 800);
            
            // Insert to database
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, filename, display_order) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $filename, $key]);
        }
    }
}

function createThumbnail($source, $destination, $width, $height) {
    list($orig_width, $orig_height, $type) = getimagesize($source);
    
    $ratio = min($width / $orig_width, $height / $orig_height);
    $new_width = intval($orig_width * $ratio);
    $new_height = intval($orig_height * $ratio);
    
    $thumb = imagecreatetruecolor($new_width, $new_height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($source);
            break;
    }
    
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    imagejpeg($thumb, $destination, 85);
    
    imagedestroy($thumb);
    imagedestroy($image);
}
?>
```

#### 3.4 Category Management
- Create/Edit/Delete categories
- Multilingual names & descriptions
- Slug auto-generation
- Product count per category
- Reorder categories (drag-drop)

#### 3.5 Settings Panel
**Tabs:**
1. **General**: Site name, contact email, social links
2. **WhatsApp**: Phone number configuration
3. **SEO**: Default meta tags, Google Analytics ID
4. **Users**: Add/remove admin users, change passwords

---

### **Phase 4: WhatsApp Business API Integration (Week 3)**

#### 4.1 WhatsApp Business API Setup Options

**Option A: WhatsApp Business API (Official - Recommended)**
- Requires Facebook Business Manager account
- Monthly cost: ~€0-50 depending on message volume
- Features: Automated messages, rich media, message templates
- Setup: https://developers.facebook.com/docs/whatsapp/

**Option B: wa.me Links (Simpler Alternative - FREE)**
- No API needed, uses web links
- Opens WhatsApp app/web with pre-filled message
- Format: `https://wa.me/41XXXXXXXXX?text=Hello`
- **Recommended for this project due to simplicity**

#### 4.2 Implementation (wa.me Method)
```php
// includes/whatsapp.php
<?php
function getWhatsAppLink($productId = null, $customMessage = '') {
    $phone = str_replace(['+', ' ', '-'], '', WHATSAPP_NUMBER);
    
    $message = "Bonjour, je suis intéressé(e) par vos créations.";
    
    if ($productId) {
        $product = getProductById($productId);
        $productUrl = SITE_URL . '/' . getCurrentLanguage() . '/produit?id=' . $productId;
        $message = "Bonjour, je suis intéressé(e) par ce produit: " . $product['title'] . " - " . $productUrl;
    }
    
    if ($customMessage) {
        $message .= " " . $customMessage;
    }
    
    return "https://wa.me/" . $phone . "?text=" . urlencode($message);
}
?>
```

#### 4.3 Tracking (Optional Analytics)
```php
// api/log-inquiry.php
<?php
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_id'])) {
    $stmt = $pdo->prepare("INSERT INTO whatsapp_inquiries (product_id) VALUES (?)");
    $stmt->execute([$data['product_id']]);
    
    // Increment product view count
    $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?")
        ->execute([$data['product_id']]);
    
    echo json_encode(['success' => true]);
}
?>
```

---

### **Phase 5: SEO & Multilingual Implementation (Week 3-4)**

#### 5.1 URL Structure (SEO-Optimized)
```
✅ GOOD (Recommended):
https://creationsjy.com/fr/
https://creationsjy.com/fr/produits
https://creationsjy.com/fr/produit/chaise-vintage-123
https://creationsjy.com/en/products
https://creationsjy.com/en/product/vintage-chair-123

❌ AVOID:
https://creationsjy.com/?lang=fr
https://creationsjy.com/products.php?lang=en
```

#### 5.2 Hreflang Implementation
```php
// In <head> section of every page
<?php
$currentLang = getCurrentLanguage(); // 'fr' or 'en'
$currentUrl = getCurrentUrl(); // e.g., '/produits' or '/products'

$langUrls = [
    'fr' => SITE_URL . '/fr' . translateUrl($currentUrl, 'fr'),
    'en' => SITE_URL . '/en' . translateUrl($currentUrl, 'en')
];
?>

<link rel="alternate" hreflang="fr" href="<?php echo $langUrls['fr']; ?>" />
<link rel="alternate" hreflang="en" href="<?php echo $langUrls['en']; ?>" />
<link rel="alternate" hreflang="x-default" href="<?php echo $langUrls['fr']; ?>" />
```

#### 5.3 Translation Helper Functions
```php
// includes/language.php
<?php
function getCurrentLanguage() {
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '/fr/') === 0 || $uri === '/fr') return 'fr';
    if (strpos($uri, '/en/') === 0 || $uri === '/en') return 'en';
    return DEFAULT_LANG;
}

function t($key, $lang = null) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = [
            'fr' => [
                'home' => 'Accueil',
                'about' => 'À propos',
                'products' => 'Produits',
                'contact' => 'Contact',
                'whatsapp_cta' => 'Contacter via WhatsApp',
                'available' => 'Disponible',
                'sold' => 'Vendu',
                'reserved' => 'Réservé',
                // ... more translations
            ],
            'en' => [
                'home' => 'Home',
                'about' => 'About',
                'products' => 'Products',
                'contact' => 'Contact',
                'whatsapp_cta' => 'Contact via WhatsApp',
                'available' => 'Available',
                'sold' => 'Sold',
                'reserved' => 'Reserved',
                // ... more translations
            ]
        ];
    }
    
    $lang = $lang ?: getCurrentLanguage();
    return $translations[$lang][$key] ?? $key;
}

function translateUrl($url, $targetLang) {
    $urlMap = [
        'fr' => [
            '/produits' => '/products',
            '/produit' => '/product',
            '/a-propos' => '/about',
            '/contact' => '/contact'
        ],
        'en' => [
            '/products' => '/produits',
            '/product' => '/produit',
            '/about' => '/a-propos',
            '/contact' => '/contact'
        ]
    ];
    
    $currentLang = getCurrentLanguage();
    
    if ($currentLang === $targetLang) {
        return $url;
    }
    
    foreach ($urlMap[$currentLang] as $from => $to) {
        if (strpos($url, $from) === 0) {
            return str_replace($from, $to, $url);
        }
    }
    
    return $url;
}
?>
```

#### 5.4 Sitemap Generation
```php
// sitemap.xml.php (rename to .xml after first generation)
<?php
header('Content-Type: application/xml; charset=utf-8');
require_once 'includes/db.php';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

// Static pages
$staticPages = [
    ['fr' => '/', 'en' => '/', 'priority' => '1.0'],
    ['fr' => '/fr/produits', 'en' => '/en/products', 'priority' => '0.9'],
    ['fr' => '/fr/a-propos', 'en' => '/en/about', 'priority' => '0.7'],
    ['fr' => '/fr/contact', 'en' => '/en/contact', 'priority' => '0.6']
];

foreach ($staticPages as $page) {
    echo '<url>';
    echo '<loc>' . SITE_URL . $page['fr'] . '</loc>';
    echo '<xhtml:link rel="alternate" hreflang="en" href="' . SITE_URL . $page['en'] . '"/>';
    echo '<xhtml:link rel="alternate" hreflang="fr" href="' . SITE_URL . $page['fr'] . '"/>';
    echo '<priority>' . $page['priority'] . '</priority>';
    echo '</url>';
}

// Dynamic product pages
$stmt = $pdo->query("
    SELECT p.id, p.slug, p.updated_at,
           pt_fr.title as title_fr,
           pt_en.title as title_en
    FROM products p
    JOIN product_translations pt_fr ON p.id = pt_fr.product_id AND pt_fr.language_code = 'fr'
    JOIN product_translations pt_en ON p.id = pt_en.product_id AND pt_en.language_code = 'en'
    WHERE p.status = 'available'
");

while ($product = $stmt->fetch()) {
    echo '<url>';
    echo '<loc>' . SITE_URL . '/fr/produit/' . $product['slug'] . '</loc>';
    echo '<xhtml:link rel="alternate" hreflang="en" href="' . SITE_URL . '/en/product/' . $product['slug'] . '"/>';
    echo '<xhtml:link rel="alternate" hreflang="fr" href="' . SITE_URL . '/fr/produit/' . $product['slug'] . '"/>';
    echo '<lastmod>' . date('Y-m-d', strtotime($product['updated_at'])) . '</lastmod>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

echo '</urlset>';
?>
```

#### 5.5 Meta Tags Template
```php
// includes/meta-tags.php
<?php
function generateMetaTags($pageType, $data = []) {
    $lang = getCurrentLanguage();
    $siteName = $lang === 'fr' ? 'Créations JY' : 'Creations JY';
    
    $defaults = [
        'title' => $siteName . ' | Upcycling Artisanal',
        'description' => $lang === 'fr' 
            ? 'Découvrez des créations uniques issues de matériaux recyclés par Yasemin Jemmely en Suisse.'
            : 'Discover unique creations made from recycled materials by Yasemin Jemmely in Switzerland.',
        'image' => SITE_URL . '/assets/images/og-default.jpg'
    ];
    
    $meta = array_merge($defaults, $data);
    
    ?>
    <title><?php echo htmlspecialchars($meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta['description']); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="<?php echo $pageType === 'product' ? 'product' : 'website'; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($meta['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta['description']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($meta['image']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars(getCurrentFullUrl()); ?>">
    <meta property="og:locale" content="<?php echo $lang === 'fr' ? 'fr_FR' : 'en_US'; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($meta['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta['description']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($meta['image']); ?>">
    
    <!-- Structured Data (JSON-LD) -->
    <?php if ($pageType === 'product' && isset($data['product'])): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "<?php echo htmlspecialchars($data['product']['title']); ?>",
        "description": "<?php echo htmlspecialchars($data['product']['description']); ?>",
        "image": "<?php echo htmlspecialchars($meta['image']); ?>",
        "offers": {
            "@type": "Offer",
            "availability": "<?php echo $data['product']['status'] === 'available' ? 'InStock' : 'OutOfStock'; ?>",
            "price": "0",
            "priceCurrency": "CHF"
        }
    }
    </script>
    <?php endif; ?>
    <?php
}
?>
```

---

### **Phase 6: Responsive Design & Performance (Week 4)**

#### 6.1 Mobile-First CSS Architecture
```css
/* assets/css/main.css */

/* Base (Mobile) Styles */
body {
    font-family: 'Lato', sans-serif;
    color: var(--charcoal);
    background-color: var(--cream);
    line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Cormorant Garamond', serif;
    color: var(--charcoal);
}

/* Tablet (768px+) */
@media (min-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop (1024px+) */
@media (min-width: 1024px) {
    .product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .container {
        max-width: 1200px;
    }
}

/* Large Desktop (1280px+) */
@media (min-width: 1280px) {
    .product-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
```

#### 6.2 Image Optimization Strategy
1. **Lazy Loading**: Use native `loading="lazy"` attribute
2. **WebP Format**: Convert JPEG/PNG to WebP (fallback support)
3. **Responsive Images**: Use `<picture>` element with srcset
4. **CDN**: Consider Cloudflare for static assets (if budget allows)

```html
<picture>
    <source srcset="/uploads/products/thumbnail/image.webp" type="image/webp">
    <source srcset="/uploads/products/thumbnail/image.jpg" type="image/jpeg">
    <img src="/uploads/products/thumbnail/image.jpg" 
         alt="Product" 
         loading="lazy"
         width="400" 
         height="400">
</picture>
```

#### 6.3 Performance Checklist
- [ ] Enable Gzip compression (.htaccess)
- [ ] Minify CSS/JS (online tools or build process)
- [ ] Set browser caching headers (.htaccess)
- [ ] Use Tailwind CDN only in development, compile for production
- [ ] Defer non-critical JavaScript
- [ ] Preload critical assets (fonts, hero image)
- [ ] Optimize database queries (add indexes)

```apache
# .htaccess (Performance optimizations)
# Enable Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Leverage browser caching
<IfModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif|webp|svg)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>
</IfModule>
```

---

### **Phase 7: Security Implementation (Week 4)**

#### 7.1 Security Checklist
- [ ] SQL Injection protection (PDO prepared statements ✅)
- [ ] XSS prevention (`htmlspecialchars()` on outputs)
- [ ] CSRF tokens for forms
- [ ] Password hashing (bcrypt ✅)
- [ ] Rate limiting for login attempts
- [ ] File upload validation (type, size, extension)
- [ ] Secure session configuration
- [ ] HTTPS enforcement (.htaccess ✅)
- [ ] Directory listing disabled
- [ ] Hide PHP version in headers

#### 7.2 CSRF Protection
```php
// includes/functions.php
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>

<!-- In forms -->
<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

<!-- In form handlers -->
<?php
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF token validation failed');
}
?>
```

#### 7.3 File Upload Security
```php
// admin/products/upload-image.php (enhanced)
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_types)) {
        continue; // Skip invalid file
    }
    
    // Validate extension
    $ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        continue;
    }
    
    // Validate size (5MB max)
    if ($_FILES['images']['size'][$key] > MAX_UPLOAD_SIZE) {
        continue;
    }
    
    // Generate secure filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    
    // Rest of upload logic...
}
```

#### 7.4 Security Headers (.htaccess)
```apache
# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header unset X-Powered-By
</IfModule>

# Block access to sensitive files
<FilesMatch "(^\.htaccess|^\.git|config\.php|composer\.json)">
    Order deny,allow
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# Hide PHP version
ServerSignature Off
```

---

### **Phase 8: Testing & Quality Assurance (Week 5)**

#### 8.1 Functional Testing Checklist
**Frontend:**
- [ ] All pages load correctly in both languages
- [ ] Language switcher works properly
- [ ] Product filtering and sorting function
- [ ] Search returns accurate results
- [ ] WhatsApp links generate with correct data
- [ ] Forms validate and submit properly
- [ ] Image galleries work (lightbox, swipe)
- [ ] Navigation is intuitive on mobile

**Admin Panel:**
- [ ] Login/logout works securely
- [ ] Products can be created/edited/deleted
- [ ] Multiple images upload correctly
- [ ] Image thumbnails generate properly
- [ ] Categories can be managed
- [ ] Settings save correctly
- [ ] Activity logs record actions

#### 8.2 Cross-Browser Testing
Test on:
- [ ] Chrome (desktop & mobile)
- [ ] Firefox (desktop & mobile)
- [ ] Safari (desktop & iOS)
- [ ] Edge (desktop)
- [ ] Samsung Internet (Android)

#### 8.3 Performance Testing
**Tools:**
- Google PageSpeed Insights (target: 90+ score)
- GTmetrix
- WebPageTest

**Targets:**
- First Contentful Paint: < 1.5s
- Largest Contentful Paint: < 2.5s
- Time to Interactive: < 3.5s
- Total page size: < 2MB

#### 8.4 Security Testing
- [ ] SQL injection attempts blocked
- [ ] XSS attempts sanitized
- [ ] CSRF tokens validated
- [ ] File upload restrictions enforced
- [ ] Admin panel requires authentication
- [ ] Sessions expire properly
- [ ] HTTPS enforced sitewide

#### 8.5 SEO Audit
- [ ] All pages have unique meta titles/descriptions
- [ ] Hreflang tags implemented correctly
- [ ] Sitemap.xml accessible and up-to-date
- [ ] Robots.txt configured properly
- [ ] Images have alt text
- [ ] Heading hierarchy correct (H1 → H6)
- [ ] URLs are clean and descriptive
- [ ] Mobile-friendly test passes

---

### **Phase 9: Deployment to Hostinger (Week 5)**

#### 9.1 Pre-Deployment Checklist
- [ ] Backup local database
- [ ] Export database to .sql file
- [ ] Test on staging environment (if available)
- [ ] Update config.php with production credentials
- [ ] Change default admin password
- [ ] Remove development comments/console.logs
- [ ] Minify CSS/JS files
- [ ] Generate final sitemap

#### 9.2 Hostinger Setup Steps
1. **Access cPanel**
   - Login to Hostinger control panel
   - Navigate to File Manager

2. **Create Database**
   - MySQL Databases section
   - Create database: `u123456789_creationsjy`
   - Create user with strong password
   - Grant all privileges

3. **Upload Files**
   - Compress local files to .zip
   - Upload via File Manager or FTP (FileZilla)
   - Extract in public_html directory
   - Set correct permissions (755 for folders, 644 for files)

4. **Import Database**
   - phpMyAdmin → Import
   - Select .sql file
   - Execute import

5. **Update Configuration**
   ```php
   // includes/config.php (production)
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'u123456789_creationsjy');
   define('DB_USER', 'u123456789_cjy_user');
   define('DB_PASS', 'STRONG_PASSWORD_HERE');
   define('SITE_URL', 'https://creationsjy.com');
   ```

6. **SSL Certificate**
   - Hostinger → SSL section
   - Enable Let's Encrypt (free)
   - Force HTTPS redirect

7. **Email Setup**
   - Create email: contact@creationsjy.com
   - Configure SMTP settings in contact form

#### 9.3 Post-Deployment Verification
- [ ] Visit site, check all pages load
- [ ] Test admin login
- [ ] Upload a test product
- [ ] Send test WhatsApp message
- [ ] Switch languages, verify translations
- [ ] Submit contact form
- [ ] Check mobile responsiveness
- [ ] Monitor error logs (cPanel → Error Log)

#### 9.4 DNS Configuration (if needed)
```
# If domain is elsewhere, point to Hostinger nameservers:
ns1.dns-parking.com
ns2.dns-parking.com

# Or update A record:
Type: A
Host: @
Points to: [Hostinger IP address]
TTL: 14400
```

---

### **Phase 10: Post-Launch & Maintenance (Ongoing)**

#### 10.1 Week 1 Post-Launch Tasks
- [ ] Submit sitemap to Google Search Console
- [ ] Set up Google Analytics 4
- [ ] Monitor server error logs daily
- [ ] Test all contact forms
- [ ] Check WhatsApp integration analytics
- [ ] Ask Yasemin for feedback
- [ ] Make minor adjustments based on feedback

#### 10.2 Monthly Maintenance
- [ ] Backup database (automated via cPanel)
- [ ] Update any PHP dependencies
- [ ] Review and clear old activity logs
- [ ] Check broken links (use Screaming Frog)
- [ ] Monitor Google Search Console for issues
- [ ] Review product performance (most viewed)
- [ ] Optimize slow-loading pages

#### 10.3 Content Strategy for Yasemin
**Training Documentation:**
Create a simple PDF/video guide covering:
1. How to add a new product
2. How to upload images
3. How to edit product details
4. How to mark products as sold
5. How to add a new category
6. How to change WhatsApp number

**Content Calendar Suggestions:**
- Weekly: Add 2-3 new products
- Monthly: Blog post about upcycling tips (if blog added later)
- Seasonal: Update homepage hero image
- Quarterly: Review and archive sold products

#### 10.4 Future Feature Ideas
**Phase 2 Enhancements (3-6 months):**
- Blog/News section for upcycling stories
- Customer testimonials/reviews
- Newsletter signup with Mailchimp integration
- Instagram feed integration (official API)
- Advanced search filters (price range, color, size)
- Wishlist functionality
- Multi-admin roles with granular permissions

**Phase 3 Enhancements (6-12 months):**
- E-commerce integration (Stripe/PayPal)
- Inventory management system
- Order tracking
- Customer accounts
- Automated email notifications
- Advanced analytics dashboard

---

## 🎯 Success Metrics (KPIs)

### Technical Metrics
- **Page Load Time**: < 3 seconds on 4G
- **Uptime**: 99.9% availability
- **Mobile Traffic**: Target 70%+ mobile visitors
- **Bounce Rate**: < 50%
- **Google PageSpeed Score**: 90+ (Mobile & Desktop)

### Business Metrics
- **Product Views**: Track most popular items
- **WhatsApp Inquiries**: Track conversion from view → inquiry
- **Language Distribution**: Monitor FR vs EN traffic
- **Return Visitors**: Target 30% return rate within 3 months

---

## 📊 Project Timeline Summary

| Phase | Duration | Status |
|-------|----------|--------|
| 1. Foundation Setup | 3-4 days | ⏳ Pending |
| 2. Frontend Development | 5-7 days | ⏳ Pending |
| 3. Admin Panel | 6-8 days | ⏳ Pending |
| 4. WhatsApp Integration | 2-3 days | ⏳ Pending |
| 5. SEO & Multilingual | 3-4 days | ⏳ Pending |
| 6. Responsive & Performance | 2-3 days | ⏳ Pending |
| 7. Security | 2-3 days | ⏳ Pending |
| 8. Testing & QA | 3-5 days | ⏳ Pending |
| 9. Deployment | 1-2 days | ⏳ Pending |
| 10. Post-Launch | Ongoing | ⏳ Pending |
| **TOTAL** | **4-5 weeks** | |

---

## 🔗 Useful Resources

### Documentation
- PHP Official: https://www.php.net/docs.php
- MySQL: https://dev.mysql.com/doc/
- Tailwind CSS: https://tailwindcss.com/docs
- WhatsApp Business API: https://developers.facebook.com/docs/whatsapp

### Tools
- **Local Development**: XAMPP, Laragon, MAMP
- **FTP Client**: FileZilla
- **Database Management**: phpMyAdmin, TablePlus
- **Image Optimization**: TinyPNG, Squoosh
- **SEO Testing**: Google Search Console, SEMrush
- **Performance**: Lighthouse, GTmetrix

### Design Resources
- **Fonts**: Google Fonts (Cormorant Garamond, Lato)
- **Icons**: Heroicons, Font Awesome
- **Stock Images**: Unsplash, Pexels (for placeholders)
- **Color Palette**: Coolors.co

---

## 📝 Developer Notes

### Git Workflow
```bash
# Initialize repository
git init
git add .
git commit -m "Initial commit: Project structure"

# Create branches for major features
git checkout -b feature/admin-panel
git checkout -b feature/whatsapp-integration
git checkout -b feature/multilingual

# Deploy to production
git checkout main
git pull origin main
# Upload to Hostinger via FTP/Git
```

### Database Backup Command
```bash
# Export database (run on local/server terminal)
mysqldump -u root -p creationsjy_db > backup_$(date +%Y%m%d).sql

# Import database
mysql -u root -p creationsjy_db < backup_20250116.sql
```

### Deployment Script (Optional)
```bash
#!/bin/bash
# deploy.sh - Automated deployment to Hostinger

echo "Starting deployment..."

# Backup database
mysqldump -u u123456789_cjy_user -p u123456789_creationsjy > backup_$(date +%Y%m%d).sql

# Upload files via FTP
lftp -u username,password ftp.hostinger.com << EOF
cd public_html
mirror -R --exclude .git --exclude node_modules ./
bye
EOF

echo "Deployment complete!"
```

---

## ✅ Final Checklist Before Launch

- [ ] All pages tested in FR and EN
- [ ] WhatsApp links tested with real phone
- [ ] Admin panel secured with strong password
- [ ] SSL certificate active (HTTPS)
- [ ] Google Analytics installed
- [ ] Google Search Console verified
- [ ] Sitemap submitted to Google
- [ ] Robots.txt configured
- [ ] Contact form sends emails
- [ ] Mobile responsiveness verified
- [ ] Cross-browser testing complete
- [ ] Performance optimized (90+ PageSpeed)
- [ ] Security audit passed
- [ ] Backup system configured
- [ ] Client training documentation delivered
- [ ] Footer credit added: "Built by Cüneyt Kaya in Kornwestheim"

---

## 📞 Contact & Support

**Developer**: Cüneyt Kaya  
**Website**: [Your Portfolio URL]  
**Email**: [Your Email]  
**Location**: Kornwestheim, Germany  

**Client**: Yasemin Jemmely  
**Location**: Gruyer, Switzerland  
**Website**: https://creationsjy.com  

---

## 📄 License & Credits

**Developed by**: Cüneyt Kaya (2025)  
**Built in**: Kornwestheim, Baden-Württemberg, Germany  
**Technologies**: PHP 8, MySQL 8, Tailwind CSS, Vanilla JavaScript  
**Hosting**: Hostinger Shared Hosting  

**Third-Party Credits**:
- Tailwind CSS - MIT License
- Google Fonts - Open Font License
- Heroicons - MIT License

---

**Last Updated**: November 16, 2025  
**Version**: 1.0.0  
**Status**: Ready for Development 🚀
