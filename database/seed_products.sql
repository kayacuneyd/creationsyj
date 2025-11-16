-- Sample Products for Demo/Testing
-- Run this after importing schema.sql and seed.sql
-- These are example products with placeholder images

-- Create categories if they don't exist
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
FROM categories WHERE slug = 'upcycled-furniture' LIMIT 1;

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'fr', 'Chaise Vintage Upcyclée', 
'Une magnifique chaise vintage restaurée et transformée avec une touche moderne. Cette pièce unique allie le charme d''antan à un design contemporain.',
'Bois de récupération, tissu écologique, peinture à l''eau',
'45 x 50 x 90 cm',
'Chaise Vintage Upcyclée | Créations JY',
'Découvrez cette chaise vintage upcyclée unique, restaurée avec soin et transformée en pièce design moderne.';

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'en', 'Upcycled Vintage Chair',
'A beautiful vintage chair restored and transformed with a modern touch. This unique piece combines old-world charm with contemporary design.',
'Reclaimed wood, eco-friendly fabric, water-based paint',
'45 x 50 x 90 cm',
'Upcycled Vintage Chair | Creations JY',
'Discover this unique upcycled vintage chair, carefully restored and transformed into a modern design piece.';

-- Product 2: Decorative Mirror
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'decorative-mirror-handmade', 'MIR-001', 'available', 1
FROM categories WHERE slug = 'decorative-items' LIMIT 1;

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'fr', 'Miroir Décoratif Fait Main',
'Un miroir décoratif unique créé à partir de matériaux recyclés. Le cadre est orné de détails artisanaux qui lui confèrent un caractère exceptionnel.',
'Bois de palette, miroir recyclé, finition naturelle',
'60 x 80 cm',
'Miroir Décoratif Fait Main | Créations JY',
'Miroir décoratif unique créé à partir de matériaux recyclés avec un cadre artisanal exceptionnel.';

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'en', 'Handmade Decorative Mirror',
'A unique decorative mirror created from recycled materials. The frame is adorned with artisanal details that give it exceptional character.',
'Pallet wood, recycled mirror, natural finish',
'60 x 80 cm',
'Handmade Decorative Mirror | Creations JY',
'Unique decorative mirror created from recycled materials with an exceptional artisanal frame.';

-- Product 3: Wall Art Piece
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'wall-art-recycled', 'ART-001', 'available', 0
FROM categories WHERE slug = 'art-pieces' LIMIT 1;

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'fr', 'Œuvre Murale Recyclée',
'Une création artistique unique réalisée à partir de matériaux recyclés. Cette pièce apporte une touche d''originalité à votre intérieur.',
'Bois de récupération, métal recyclé, peinture écologique',
'50 x 70 cm',
'Œuvre Murale Recyclée | Créations JY',
'Création artistique unique réalisée à partir de matériaux recyclés pour apporter originalité à votre intérieur.';

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'en', 'Recycled Wall Art',
'A unique artistic creation made from recycled materials. This piece adds a touch of originality to your interior.',
'Reclaimed wood, recycled metal, eco-friendly paint',
'50 x 70 cm',
'Recycled Wall Art | Creations JY',
'Unique artistic creation made from recycled materials to add originality to your interior.';

-- Product 4: Side Table
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'side-table-upcycled', 'TBL-001', 'reserved', 0
FROM categories WHERE slug = 'upcycled-furniture' LIMIT 1;

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'fr', 'Table d''Appoint Upcyclée',
'Une table d''appoint élégante créée à partir de matériaux de récupération. Parfaite pour ajouter une touche de caractère à votre salon.',
'Bois de palette, pieds métalliques recyclés',
'40 x 40 x 55 cm',
'Table d''Appoint Upcyclée | Créations JY',
'Table d''appoint élégante créée à partir de matériaux de récupération pour votre salon.';

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'en', 'Upcycled Side Table',
'An elegant side table created from reclaimed materials. Perfect for adding a touch of character to your living room.',
'Pallet wood, recycled metal legs',
'40 x 40 x 55 cm',
'Upcycled Side Table | Creations JY',
'Elegant side table created from reclaimed materials for your living room.';

-- Product 5: Decorative Vase
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'decorative-vase-handmade', 'VAS-001', 'available', 0
FROM categories WHERE slug = 'decorative-items' LIMIT 1;

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'fr', 'Vase Décoratif Fait Main',
'Un vase décoratif unique créé à partir de matériaux recyclés. Cette pièce artisanale apporte une touche naturelle à votre décoration.',
'Verre recyclé, finition naturelle',
'Hauteur: 30 cm, Diamètre: 15 cm',
'Vase Décoratif Fait Main | Créations JY',
'Vase décoratif unique créé à partir de matériaux recyclés pour votre décoration intérieure.';

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'en', 'Handmade Decorative Vase',
'A unique decorative vase created from recycled materials. This artisanal piece adds a natural touch to your decoration.',
'Recycled glass, natural finish',
'Height: 30 cm, Diameter: 15 cm',
'Handmade Decorative Vase | Creations JY',
'Unique decorative vase created from recycled materials for your interior decoration.';

-- Product 6: Sold Item (Example)
INSERT INTO products (category_id, slug, sku, status, featured) 
SELECT id, 'sold-art-piece', 'ART-002', 'sold', 0
FROM categories WHERE slug = 'art-pieces' LIMIT 1;

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'fr', 'Pièce Artistique (Vendue)',
'Cette pièce artistique unique a été vendue. Découvrez nos autres créations disponibles.',
'Bois de récupération, métal recyclé',
'40 x 60 cm',
'Pièce Artistique Vendue | Créations JY',
'Cette pièce artistique unique a été vendue. Découvrez nos autres créations disponibles.';

INSERT INTO product_translations (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description) 
SELECT LAST_INSERT_ID(), 'en', 'Art Piece (Sold)',
'This unique art piece has been sold. Discover our other available creations.',
'Reclaimed wood, recycled metal',
'40 x 60 cm',
'Art Piece Sold | Creations JY',
'This unique art piece has been sold. Discover our other available creations.';

-- Note: These products will use placeholder.jpg as no images are uploaded
-- To add images, use the admin panel after running this script

