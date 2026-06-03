-- ==================================================================
-- POS APPLICATION - FULL DATABASE STRUCTURE (per restaurant / tenant)
-- ==================================================================
-- HOW TO USE (for each new restaurant):
--   1. In phpMyAdmin, create a NEW database named after the restaurant
--      (e.g. pos_burj, pos_marina, pos_downtown). Use utf8 / utf8_general_ci.
--   2. Select that database, open the SQL tab, paste this whole file, and Run.
--   3. This creates all tables + a default admin login + default settings.
--   4. The restaurant then adds its OWN categories/items via the Settings page.
--
-- Default login created by this script:
--   username: admin
--   password: (SAME admin password you already use on your current system,
--              because this uses the same stored password hash)
--   >> Change it immediately from the Users page after first login.
-- ==================================================================

-- ------------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Items (supports single price OR size-based S/M/L)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    name_ar VARCHAR(150) NOT NULL,
    price_small DECIMAL(6,3) DEFAULT NULL,
    price_medium DECIMAL(6,3) DEFAULT NULL,
    price_large DECIMAL(6,3) DEFAULT NULL,
    price DECIMAL(6,3) DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Users
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Restaurant Tables (dine-in table management)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    status ENUM('available','occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Invoices (includes table + status columns for held/open orders)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) NOT NULL,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT NULL,
    table_id INT DEFAULT NULL,
    table_name VARCHAR(50) DEFAULT NULL,
    status ENUM('open','paid') DEFAULT 'paid',
    payment_mode VARCHAR(20) NOT NULL DEFAULT 'Cash',
    payment_reference VARCHAR(100) DEFAULT NULL,
    total DECIMAL(10,3) NOT NULL,
    cash_paid DECIMAL(10,3) NOT NULL,
    change_due DECIMAL(10,3) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Invoice Items
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    item_name_ar VARCHAR(150) DEFAULT NULL,
    size VARCHAR(20) DEFAULT NULL,
    price DECIMAL(6,3) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,3) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Company Settings (logo, name, contact shown on receipts)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name_en VARCHAR(200) NOT NULL DEFAULT 'My Restaurant',
    company_name_ar VARCHAR(200) NOT NULL DEFAULT 'مطعمي',
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(100),
    logo_path VARCHAR(255),
    invoice_footer TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ------------------------------------------------------------------
-- Performance indexes
-- ------------------------------------------------------------------
CREATE INDEX idx_invoices_created_at  ON invoices (created_at);
CREATE INDEX idx_invoices_user_id     ON invoices (user_id);
CREATE INDEX idx_invoices_payment     ON invoices (payment_mode);
CREATE INDEX idx_invoices_status      ON invoices (status);
CREATE INDEX idx_invoices_table_id    ON invoices (table_id);
CREATE INDEX idx_invoice_items_inv_id ON invoice_items (invoice_id);
CREATE INDEX idx_items_category       ON items (category_id);
CREATE INDEX idx_items_active         ON items (is_active);

-- ------------------------------------------------------------------
-- Default admin user (same admin password as your existing system)
-- ------------------------------------------------------------------
INSERT INTO users (username, full_name, password_hash, role, is_active)
SELECT 'admin', 'Administrator', '$2y$10$rbA/3ZNK3L/jsjITLynK2OCwuBOTGoQ7JNmix1q1SZk50763U8mm6', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

-- ------------------------------------------------------------------
-- Default company settings row
-- ------------------------------------------------------------------
INSERT INTO company_settings (company_name_en, company_name_ar, address, phone, email, invoice_footer)
SELECT 'My Restaurant', 'مطعمي', 'Kuwait', '+965 XXXX XXXX', 'info@example.com', 'Thank you for your visit!'
WHERE NOT EXISTS (SELECT 1 FROM company_settings);

-- ------------------------------------------------------------------
-- Default dine-in tables (adjust/rename later from the app)
-- ------------------------------------------------------------------
INSERT INTO restaurant_tables (name, status) VALUES
('Table 1', 'available'),
('Table 2', 'available'),
('Table 3', 'available'),
('Table 4', 'available'),
('Table 5', 'available'),
('Takeaway', 'available'),
('Delivery', 'available');
