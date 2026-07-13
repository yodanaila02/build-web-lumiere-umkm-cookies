-- =====================================================================
-- FILE: database/schema.sql
-- PROJECT: Lumiere Cookies — Aplikasi Web UMKM (Pemrograman Web)
-- DESKRIPSI: DDL pembuatan basis data & seluruh tabel (min. 5 tabel berelasi)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS lumiere_cookies
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lumiere_cookies;

-- Drop berurutan (hormati foreign key) agar schema bisa dijalankan ulang
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS gallery;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS whatsapp_numbers;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS admins;

-- ------------------------------------------------------------------
-- TABEL 1: admins  (FRIZA - Authentication)
-- ------------------------------------------------------------------
CREATE TABLE admins (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  username   VARCHAR(60)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,          -- hasil password_hash()
  role       ENUM('admin','superadmin') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 2: categories  (NAYLA - Kategori Produk)
-- ------------------------------------------------------------------
CREATE TABLE categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,
  slug        VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 3: products  (NAYLA - Katalog Produk) -> FK ke categories
-- ------------------------------------------------------------------
CREATE TABLE products (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  category_id    INT NOT NULL,
  name           VARCHAR(150) NOT NULL,
  slug           VARCHAR(180) NOT NULL UNIQUE,
  description    TEXT,
  price          INT NOT NULL DEFAULT 0,      -- rupiah (tanpa desimal)
  stock          INT NOT NULL DEFAULT 0,
  image          VARCHAR(255) DEFAULT NULL,
  badge          ENUM('none','best_seller','new','limited','pre_order') NOT NULL DEFAULT 'none',
  is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
  is_active      TINYINT(1) NOT NULL DEFAULT 1,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 4: orders  (NAILA YODA - Pemesanan)
-- ------------------------------------------------------------------
CREATE TABLE orders (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  invoice_code    VARCHAR(30) NOT NULL UNIQUE,
  customer_name   VARCHAR(120) NOT NULL,
  customer_phone  VARCHAR(30)  NOT NULL,
  delivery_method ENUM('pickup','delivery','other') NOT NULL DEFAULT 'pickup',
  address         VARCHAR(255) DEFAULT NULL,
  maps_link       VARCHAR(500) DEFAULT NULL,
  notes           VARCHAR(255) DEFAULT NULL,
  subtotal        INT NOT NULL DEFAULT 0,
  shipping_cost   INT NOT NULL DEFAULT 0,
  total           INT NOT NULL DEFAULT 0,
  status          ENUM('pending','processing','done','cancelled') NOT NULL DEFAULT 'pending',
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 5: order_items  (NAILA YODA) -> FK ke orders & products
-- ------------------------------------------------------------------
CREATE TABLE order_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  order_id     INT NOT NULL,
  product_id   INT DEFAULT NULL,
  product_name VARCHAR(150) NOT NULL,  -- snapshot nama saat dipesan
  price        INT NOT NULL,
  qty          INT NOT NULL,
  subtotal     INT NOT NULL,
  CONSTRAINT fk_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 6: testimonials  (SEKAR - Testimonial Management)
-- ------------------------------------------------------------------
CREATE TABLE testimonials (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  content       VARCHAR(500) NOT NULL,
  rating        TINYINT NOT NULL DEFAULT 5,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 7: faqs  (SEKAR - FAQ Management)
-- ------------------------------------------------------------------
CREATE TABLE faqs (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  question   VARCHAR(255) NOT NULL,
  answer     VARCHAR(800) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 8: articles  (HYUGA - Blog / Artikel)
-- ------------------------------------------------------------------
CREATE TABLE articles (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(180) NOT NULL,
  slug         VARCHAR(200) NOT NULL UNIQUE,
  excerpt      VARCHAR(300) DEFAULT NULL,
  content      TEXT,
  image        VARCHAR(255) DEFAULT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 9: gallery  (HYUGA - Galeri Dokumentasi)
-- ------------------------------------------------------------------
CREATE TABLE gallery (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  title      VARCHAR(150) DEFAULT NULL,
  image      VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 10: contact_messages  (SEKAR - Contact Form)
-- ------------------------------------------------------------------
CREATE TABLE contact_messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(120) DEFAULT NULL,
  phone      VARCHAR(30)  DEFAULT NULL,
  message    VARCHAR(1000) NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 11: settings  (SEKAR - Website Settings)
-- ------------------------------------------------------------------
CREATE TABLE settings (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(80) NOT NULL UNIQUE,
  setting_value VARCHAR(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- TABEL 12: whatsapp_numbers  (FRIZA - Kontak WhatsApp, no hardcode)
-- ------------------------------------------------------------------
CREATE TABLE whatsapp_numbers (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  label     VARCHAR(60) NOT NULL,
  number    VARCHAR(30) NOT NULL,           -- format internasional tanpa +, mis. 62878...
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_primary TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index bantu pencarian/relasi
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active   ON products(is_active);
CREATE INDEX idx_items_order       ON order_items(order_id);
CREATE INDEX idx_orders_status     ON orders(status);
