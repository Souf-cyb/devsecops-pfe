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
    price DECIMAL(10,2)
);

INSERT INTO users VALUES
(1, 'admin', 'admin@vulnshop.com', 'admin123', 1),
(2, 'alice', 'alice@vulnshop.com', 'password123', 0),
(3, 'bob', 'bob@vulnshop.com', 'bob2024', 0),
(4, 'charlie', 'charlie@vulnshop.com', 'charlie99', 0);

INSERT INTO products VALUES
(1, 'Laptop Pro X', 'High performance laptop', 999.99),
(2, 'SmartPhone Z', 'Latest smartphone', 699.99),
(3, 'Gaming Console', 'Next gen gaming', 499.99),
(4, 'Smart Watch', 'Health tracking watch', 299.99),
(5, 'Pro Headphones', 'Crystal clear sound', 199.99),
(6, 'DSLR Camera', 'Professional photography', 1299.99);