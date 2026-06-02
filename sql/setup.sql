-- ============================================
-- Burge Al Salhiya POS - Database Setup
-- Run this once in phpMyAdmin or MySQL console
-- ============================================

CREATE DATABASE IF NOT EXISTS pos_salhiya CHARACTER SET utf8 COLLATE utf8_general_ci;
USE pos_salhiya;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
);

-- Items Table
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    name_ar VARCHAR(150) NOT NULL,
    price_small DECIMAL(6,3) DEFAULT NULL,
    price_medium DECIMAL(6,3) DEFAULT NULL,
    price_large DECIMAL(6,3) DEFAULT NULL,
    price DECIMAL(6,3) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Invoices Table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) NOT NULL,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT NULL,
    payment_mode VARCHAR(20) NOT NULL DEFAULT 'Cash',
    total DECIMAL(10,3) NOT NULL,
    cash_paid DECIMAL(10,3) NOT NULL,
    change_due DECIMAL(10,3) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Invoice Items Table
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
);

ALTER TABLE invoices ADD COLUMN IF NOT EXISTS payment_mode VARCHAR(20) NOT NULL DEFAULT 'Cash' AFTER invoice_number;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER invoice_number;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS user_name VARCHAR(100) DEFAULT NULL AFTER user_id;
ALTER TABLE invoice_items ADD COLUMN IF NOT EXISTS item_name_ar VARCHAR(150) DEFAULT NULL AFTER item_name;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL AFTER id;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) DEFAULT NULL AFTER name_ar;
INSERT INTO users (username, full_name, password_hash, role, is_active)
SELECT 'admin', 'Administrator', '$2y$10$rbA/3ZNK3L/jsjITLynK2OCwuBOTGoQ7JNmix1q1SZk50763U8mm6', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
DELETE c FROM categories c LEFT JOIN items i ON i.category_id = c.id WHERE i.id IS NULL;

-- ============================================
-- PERFORMANCE INDEXES (run once)
-- ============================================
CREATE INDEX IF NOT EXISTS idx_invoices_created_at  ON invoices (created_at);
CREATE INDEX IF NOT EXISTS idx_invoices_user_id     ON invoices (user_id);
CREATE INDEX IF NOT EXISTS idx_invoices_payment     ON invoices (payment_mode);
CREATE INDEX IF NOT EXISTS idx_invoice_items_inv_id ON invoice_items (invoice_id);
CREATE INDEX IF NOT EXISTS idx_items_category       ON items (category_id);
CREATE INDEX IF NOT EXISTS idx_items_active         ON items (is_active);

-- ============================================
-- COMPANY SETTINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name_en VARCHAR(200) NOT NULL DEFAULT 'BURGE AL SALHIYA',
    company_name_ar VARCHAR(200) NOT NULL DEFAULT 'برج الصالحية',
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(100),
    logo_path VARCHAR(255),
    invoice_footer TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO company_settings (company_name_en, company_name_ar, address, phone, email, invoice_footer)
SELECT 'BURGE AL SALHIYA', 'برج الصالحية', 'Kuwait', '+965 XXXX XXXX', 'info@salhiya.com', 'Thank you for your visit!'
WHERE NOT EXISTS (SELECT 1 FROM company_settings WHERE id = 1);

-- ============================================
-- SEED DATA - Categories
-- ============================================
INSERT INTO categories (name_en, name_ar, sort_order) VALUES
('Fatayir', 'فطائر', 1),
('Sandwiches', 'ساندويشات', 2),
('Meal Dishes', 'المواعين', 3),
('Juices', 'عصائر', 4),
('Drinks', 'مشروبات', 5),
('Pizza', 'بيتزا', 6);

-- ============================================
-- SEED DATA - Fatayir (category_id = 1)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(1, 'Zaatar', 'زعتر', 0.250),
(1, 'Eggs', 'بيض', 0.250),
(1, 'Falafel', 'فلافل', 0.400),
(1, 'Spinach', 'سبانخ', 0.500),
(1, 'Mortadela', 'مرتديلا', 0.500),
(1, 'Kraft', 'كرافت', 0.500),
(1, 'Meat', 'لحم', 0.500),
(1, 'Hotdog', 'نقانق', 0.600),
(1, 'Halloum', 'حلوم', 0.500),
(1, 'Labna', 'لبنة', 0.500),
(1, 'Cheese', 'جبن', 0.500),
(1, 'Halloum Vegetable', 'حلوم كامل', 0.500),
(1, 'Hotdog Cheese', 'نقانق جبن', 0.600),
(1, 'Spinach Egg', 'سبانخ بيض', 0.600),
(1, 'Meat Egg', 'لحم بيض', 0.600),
(1, 'Meat Cheese', 'لحم جبن', 0.600),
(1, 'Hotdog Egg', 'نقانق بيض', 0.500),
(1, 'Monzerla', 'مونتزرلا', 0.600),
(1, 'Mortadella Egg', 'مرتديلا بيض', 0.500),
(1, 'Chicken Mosahab', 'دجاج مسحب', 0.600),
(1, 'Spinach Cheese', 'سبانخ جبنة', 0.500),
(1, 'Falafel Lebna', 'فلافل لبنة', 0.600),
(1, 'Mortadella Cheese', 'مرتديلا جبن', 0.600),
(1, 'Zaatar Cheese', 'زعتر جبن', 0.500),
(1, 'Egg Vegetable', 'بيض خضار', 0.500),
(1, 'Egg Cheese', 'بيض جبن', 0.600),
(1, 'Mix Cheese', 'جبنيات', 1.500),
(1, 'Sikaria', 'سكرية', 1.000);

-- Pizza Fatayir (special pricing S/M/B)
INSERT INTO items (category_id, name_en, name_ar, price_small, price_medium, price_large) VALUES
(1, 'Pizza Fatayir', 'بيتزا', 1.000, 1.250, 2.500);

-- ============================================
-- SEED DATA - Sandwiches (category_id = 2)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(2, 'Beans', 'فول', 0.100),
(2, 'Mushakal', 'مشكل', 0.100),
(2, 'Boiled Egg', 'بيض مسلوق', 0.150),
(2, 'Omelet', 'عجة', 0.150),
(2, 'Eggs With Tomatoes', 'بيض بلطماطم', 0.150),
(2, 'Burger', 'برجر', 0.500),
(2, 'Labana', 'لبنة', 0.250),
(2, 'Sausages', 'نقانق', 0.250),
(2, 'White Cheese', 'جبنة بيضة', 0.250),
(2, 'Roomi Cheese', 'جبنة رومي', 0.350),
(2, 'Eggs Cheese', 'بيض جبن', 0.250),
(2, 'Jam', 'مربى', 0.250),
(2, 'Shawarma Semon', 'شاورما صمون', 0.300),
(2, 'Shawarma Sheet', 'حلوم', 0.500),
(2, 'Haloum', 'شاورم صاح', 0.350);

-- ============================================
-- SEED DATA - Meal Dishes (category_id = 3)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(3, 'Hammus', 'معون حمص', 0.600),
(3, 'Beans', 'معون فول', 0.600),
(3, 'Chips', 'معون شيبس', 0.750),
(3, 'Egg Tomatos', 'معون بيض الطماطم', 0.750),
(3, 'Omelet', 'معون عجة', 0.750),
(3, 'Mix Plate', 'معون مشكل', 0.600),
(3, 'Liver', 'معون كبده', 1.100),
(3, 'Chicken Shawarma', 'معون شاورما دجاج', 1.250),
(3, 'Shawarma with Hammus', 'معون شاورما حمص', 1.750);

-- ============================================
-- SEED DATA - Juices (category_id = 4)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(4, 'Cocktail', 'كوكتيل', 0.500),
(4, 'Apple', 'تفاح', 0.750),
(4, 'Frozen Mango', 'مانجو منلج', 0.500),
(4, 'Frozen Strawberry', 'فروله منلج', 0.600),
(4, 'Carrot', 'جزر', 0.750),
(4, 'Lemon', 'ليمون', 0.600),
(4, 'Lemon Nana', 'ليمون نعناع', 0.750),
(4, 'Orange', 'برتقال', 0.500);

-- ============================================
-- SEED DATA - Drinks (category_id = 5)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price) VALUES
(5, 'Pepsi', 'بيبسي', 0.150),
(5, 'Milk Tea', 'شاي حليب', 0.150),
(5, 'Plain Tea', 'شاي ساده', 0.150),
(5, 'Green Tea', 'شاي أخضر', 0.200),
(5, 'Halbah', 'حلبة', 0.250),
(5, 'French Coffee', 'قهوة فرنسية', 0.350),
(5, 'Turkish Coffee', 'قهوة تركي', 0.350);

-- ============================================
-- SEED DATA - Pizza (category_id = 6)
-- ============================================
INSERT INTO items (category_id, name_en, name_ar, price_small, price_medium, price_large) VALUES
(6, 'Pizza Chicken', 'بيتزا دجاج', 1.500, 2.000, 2.500),
(6, 'Pizza Meat', 'بيتزا لحم', 1.500, 2.000, 2.500),
(6, 'Pizza Vegetables', 'بيتزا خضار', 1.500, 2.000, 2.500),
(6, 'Pizza Barboni', 'بيتزا باربوني', 1.500, 2.000, 2.500),
(6, 'Pizza Margherita', 'بيتزا مارجريتا', 1.500, 2.000, 2.500),
(6, 'Pizza Hotdog', 'بيتزا هوت دوج', 1.500, 2.000, 2.500),
(6, 'Pizza Mortadala', 'بيتزا مرتديلا', 1.500, 2.000, 2.500),
(6, 'Pizza Mix', 'بيتزا مشكل', 1.500, 2.000, 2.500);
