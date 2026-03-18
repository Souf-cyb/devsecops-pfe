CREATE DATABASE IF NOT EXISTS vulnshop;
USE vulnshop;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255) DEFAULT 'default.jpg',
    is_admin TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50)
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    stock INT DEFAULT 0,
    sku VARCHAR(50),
    image VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    is_featured TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10,2),
    shipping DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2),
    shipping_address TEXT,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(255),
    comment TEXT,
    is_verified TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    discount_percent INT,
    is_active TINYINT DEFAULT 1
);

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    subject VARCHAR(255),
    message TEXT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
INSERT INTO categories (id, name, slug, description, icon) VALUES
(1, 'Électronique', 'electronique', 'Smartphones, laptops, accessoires', 'laptop'),
(2, 'Mode & Vêtements', 'mode', 'Vêtements, chaussures, accessoires de mode', 'shopping-bag'),
(3, 'Maison & Jardin', 'maison', 'Meubles, décoration, jardinage', 'home'),
(4, 'Sport & Fitness', 'sport', 'Équipements sportifs, fitness', 'activity'),
(5, 'Beauté & Santé', 'beaute', 'Cosmétiques, soins, santé', 'heart'),
(6, 'Livres & Médias', 'livres', 'Livres, films, musique', 'book');

-- Users (passwords en plaintext — volontairement vulnérable)
INSERT INTO users (id, username, email, password, full_name, phone, address, is_admin) VALUES
(1, 'admin', 'admin@vulnshop.com', 'Admin@2024!', 'Administrateur VulnShop', '+33 1 23 45 67 89', '1 rue de la Paix, 75001 Paris', 1),
(2, 'alice.martin', 'alice@example.com', 'password123', 'Alice Martin', '+33 6 12 34 56 78', '15 avenue des Fleurs, 69001 Lyon', 0),
(3, 'bob.dupont', 'bob@example.com', 'bob2024', 'Bob Dupont', '+33 7 98 76 54 32', '8 boulevard Victor Hugo, 13001 Marseille', 0),
(4, 'charlie.leclerc', 'charlie@example.com', 'charlie99', 'Charlie Leclerc', '+33 6 55 44 33 22', '22 rue Nationale, 59000 Lille', 0);

-- Products
INSERT INTO products (category_id, name, slug, description, price, original_price, stock, sku, image, rating, review_count, is_featured) VALUES
(1, 'MacBook Pro 14" M3', 'macbook-pro-14-m3', 'Le MacBook Pro 14 pouces avec la puce M3 offre des performances exceptionnelles pour les professionnels. Écran Liquid Retina XDR, batterie 18h, 16GB RAM, 512GB SSD.', 2199.99, 2499.99, 15, 'APPL-MBP-M3-14', 'macbook.jpg', 4.8, 127, 1),
(1, 'iPhone 15 Pro Max', 'iphone-15-pro-max', 'iPhone 15 Pro Max avec puce A17 Pro, appareil photo 48MP, Dynamic Island, USB-C. Disponible en Titane Naturel.', 1329.99, NULL, 42, 'APPL-IP15-PM-256', 'iphone15.jpg', 4.7, 89, 1),
(1, 'Samsung Galaxy S24 Ultra', 'samsung-s24-ultra', 'Galaxy S24 Ultra avec S Pen intégré, Galaxy AI, écran Dynamic AMOLED 2X 6.8 pouces, appareil photo 200MP.', 1349.99, 1499.99, 28, 'SAMS-S24U-256', 's24ultra.jpg', 4.6, 74, 0),
(1, 'Sony WH-1000XM5', 'sony-wh-1000xm5', 'Casque sans fil avec réduction de bruit leader du marché. 30h dautonomie, charge rapide, qualité audio Hi-Res.', 279.99, 379.99, 65, 'SONY-WH1000XM5', 'sony-headphones.jpg', 4.9, 203, 1),
(1, 'iPad Pro 12.9" M2', 'ipad-pro-129-m2', 'iPad Pro avec puce M2, écran Liquid Retina XDR 12.9 pouces, WiFi 6E, compatible Apple Pencil Pro.', 1099.99, NULL, 20, 'APPL-IPADPRO-M2', 'ipadpro.jpg', 4.7, 56, 0),
(2, 'Nike Air Max 270', 'nike-air-max-270', 'Chaussure lifestyle avec la plus grande unité Air de Nike à ce jour. Tige mesh respirante, semelle intermédiaire mousse.', 149.99, 179.99, 120, 'NIKE-AM270-42', 'nike-am270.jpg', 4.5, 312, 1),
(2, 'Levis 501 Original', 'levis-501-original', 'Le jean iconique 501 de Levis. Coupe droite classique, denim 100% coton, fermeture à boutons.', 89.99, NULL, 200, 'LEVIS-501-32-32', 'levis501.jpg', 4.4, 445, 0),
(2, 'Veste Cuir Femme', 'veste-cuir-femme', 'Veste en cuir véritable noir, coupe cintrée, col moto, fermetures éclairs asymétriques. Tailles XS-XL.', 299.99, 399.99, 35, 'VEST-CUIR-F-M', 'veste-cuir.jpg', 4.6, 67, 0),
(3, 'Canapé Scandinave 3 Places', 'canape-scandinave', 'Canapé style scandinave en tissu bouclette gris clair. Structure bois massif, pieds chêne. Dimensions: 220x85x75cm.', 799.99, 999.99, 8, 'CANA-SCAN-3P-GR', 'canape.jpg', 4.3, 28, 1),
(3, 'Robot Cuiseur Thermomix TM6', 'thermomix-tm6', 'Le robot cuiseur de référence. 22 fonctions de cuisson, balance intégrée, Wi-Fi, écran tactile 6.8 pouces.', 1399.99, NULL, 12, 'VOER-TM6', 'thermomix.jpg', 4.8, 156, 0),
(4, 'Vélo Elliptique ProForm', 'velo-elliptique-proform', 'Vélo elliptique connecté avec 24 niveaux de résistance, écran HD 10 pouces, compatible iFIT. Pliable.', 599.99, 799.99, 18, 'PROF-ELIP-22', 'elliptique.jpg', 4.2, 43, 0),
(5, 'Parfum Chanel N°5 EDP', 'chanel-no5-edp', 'Liconique N°5 de Chanel en Eau de Parfum. Floral aldéhydé intemporel. 100ml.', 149.99, NULL, 50, 'CHAN-N5-EDP-100', 'chanel.jpg', 4.9, 589, 1);

-- Orders
INSERT INTO orders (user_id, order_number, status, subtotal, shipping, total, shipping_address, payment_method) VALUES
(2, 'VS-2024-001', 'delivered', 1329.99, 0, 1329.99, '15 avenue des Fleurs, 69001 Lyon', 'credit_card'),
(3, 'VS-2024-002', 'shipped', 279.99, 5.99, 285.98, '8 boulevard Victor Hugo, 13001 Marseille', 'paypal'),
(2, 'VS-2024-003', 'processing', 2199.99, 0, 2199.99, '15 avenue des Fleurs, 69001 Lyon', 'credit_card'),
(4, 'VS-2024-004', 'pending', 149.99, 5.99, 155.98, '22 rue Nationale, 59000 Lille', 'credit_card');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 2, 1, 1329.99),
(2, 4, 1, 279.99),
(3, 1, 1, 2199.99),
(4, 12, 1, 149.99);

-- Reviews
INSERT INTO reviews (product_id, user_id, rating, title, comment, is_verified) VALUES
(1, 2, 5, 'Excellent produit !', 'Le MacBook Pro M3 est vraiment impressionnant. Les performances sont bluffantes et lautonomie est fantastique.', 1),
(2, 3, 4, 'Très bon smartphone', 'LiPhone 15 Pro Max est excellent, mais le prix reste élevé. Lappareil photo est exceptionnel.', 1),
(4, 2, 5, 'Meilleur casque du marché', 'La réduction de bruit est incroyable. Je travaille en open space et cest un game changer !', 1),
(6, 4, 4, 'Confortable et stylé', 'Les Nike Air Max 270 sont très confortables pour une utilisation quotidienne.', 0);

-- Coupons
INSERT INTO coupons (code, discount_percent) VALUES
('WELCOME10', 10),
('PROMO20', 20),
('VIP30', 30);

-- Cart (alice)
INSERT INTO cart (user_id, product_id, quantity) VALUES
(2, 4, 1),
(2, 6, 2);