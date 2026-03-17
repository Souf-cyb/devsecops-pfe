CREATE DATABASE IF NOT EXISTS vulnshop;
USE vulnshop;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    email VARCHAR(100),
    password VARCHAR(100),
    is_admin TINYINT DEFAULT 0
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    category VARCHAR(50)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    username VARCHAR(50),
    comment TEXT,
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users VALUES
(1,'admin','admin@vulnshop.com','admin123',1),
(2,'alice','alice@vulnshop.com','password123',0),
(3,'bob','bob@vulnshop.com','bob2024',0),
(4,'charlie','charlie@vulnshop.com','charlie99',0);

INSERT INTO products VALUES
(1,'Laptop Pro X','High performance laptop for professionals',999.99,'Electronique'),
(2,'SmartPhone Z','Latest smartphone with amazing features',699.99,'Electronique'),
(3,'Gaming Console','Next generation gaming experience',499.99,'Gaming'),
(4,'Smart Watch','Track your health and stay connected',299.99,'Mode'),
(5,'Pro Headphones','Crystal clear sound quality',199.99,'Electronique'),
(6,'DSLR Camera','Professional photography made easy',1299.99,'Photo');