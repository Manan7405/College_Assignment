-- ===============================================================
-- Question 1 : Shopping Cart database
-- Import with phpMyAdmin (Import tab) or:  mysql -u root < shopping_cart.sql
-- ===============================================================
CREATE DATABASE IF NOT EXISTS practical_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical_db;

-- Drop in child-first order so the file can be re-imported safely
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

-- ---------------- admins ----------------
CREATE TABLE admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,           -- bcrypt hash from password_hash()
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------- categories ----------------
CREATE TABLE categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)  NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------- products ----------------
CREATE TABLE products (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price       DECIMAL(10,2) NOT NULL,
    picture     VARCHAR(255) NULL,                  -- only the file NAME is stored
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_products_category (category_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories (id)
        ON DELETE RESTRICT          -- a category with products cannot be deleted
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------- users ----------------
CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    username      VARCHAR(30)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_email (email),
    UNIQUE KEY uq_user_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================== SAMPLE DATA =====================

-- Admin login ->  username: admin    password: admin123
-- The value below is the bcrypt hash of "admin123" (made with password_hash()).
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$12$Xbxa0kpbzI15V4HzCrrQG.VIjIon3Qz68jwfZpxKn.n.nMzFY55MO');

INSERT INTO categories (id, name) VALUES
(1, 'Garments'),
(2, 'Mobiles'),
(3, 'Laptops');

INSERT INTO products (category_id, name, description, price, picture) VALUES
(1, 'Cotton T-Shirt',   'Soft round-neck cotton t-shirt, available in many sizes.',   499.00,   'sample_tshirt.png'),
(1, 'Slim Fit Jeans',   'Comfortable stretch denim jeans with a slim fit.',           1299.00,  'sample_jeans.png'),
(1, 'Winter Jacket',    'Warm padded jacket with a water resistant outer layer.',     2499.00,  'sample_jacket.png'),
(2, 'Smartphone X',     '6.5 inch display, 128 GB storage, 50 MP camera.',            15999.00, 'sample_phone1.png'),
(2, 'Smartphone Pro',   '6.7 inch AMOLED display, 256 GB storage, fast charging.',    32999.00, 'sample_phone2.png'),
(2, 'Smartphone Lite',  'Budget friendly phone with a 5000 mAh long-lasting battery.', 8999.00, 'sample_phone3.png'),
(3, 'Laptop Air',       'Thin and light laptop, 8 GB RAM, 512 GB SSD.',               54999.00, 'sample_laptop1.png'),
(3, 'Laptop Gaming',    'Gaming laptop with 16 GB RAM and a dedicated graphics card.', 89999.00, 'sample_laptop2.png'),
(3, 'Laptop Office',    'Reliable everyday laptop for study and office work.',        38999.00, 'sample_laptop3.png');
