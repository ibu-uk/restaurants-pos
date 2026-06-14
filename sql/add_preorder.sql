-- ============================================
-- Add Pre-Order / Customer Support
-- Works on MySQL AND MariaDB. Safe to re-run.
-- Run this in phpMyAdmin (SQL tab) on your pos database.
-- ============================================

-- 1. Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2 + 3. Add columns and indexes only if they do not already exist.
-- (MySQL does not support ADD COLUMN IF NOT EXISTS / DROP INDEX IF EXISTS,
--  so we check information_schema inside a stored procedure instead.)
DROP PROCEDURE IF EXISTS add_preorder_schema;
DELIMITER $$
CREATE PROCEDURE add_preorder_schema()
BEGIN
    -- ----- Columns on invoices -----
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'customer_id') THEN
        ALTER TABLE invoices ADD COLUMN customer_id INT DEFAULT NULL AFTER user_name;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'customer_name') THEN
        ALTER TABLE invoices ADD COLUMN customer_name VARCHAR(100) DEFAULT NULL AFTER customer_id;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'customer_phone') THEN
        ALTER TABLE invoices ADD COLUMN customer_phone VARCHAR(20) DEFAULT NULL AFTER customer_name;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'order_type') THEN
        ALTER TABLE invoices ADD COLUMN order_type ENUM('dine_in','takeaway','delivery','pre_order') DEFAULT 'dine_in' AFTER customer_phone;
    END IF;

    -- ----- Indexes on invoices -----
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND INDEX_NAME = 'idx_invoices_customer_id') THEN
        ALTER TABLE invoices ADD INDEX idx_invoices_customer_id (customer_id);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND INDEX_NAME = 'idx_invoices_order_type') THEN
        ALTER TABLE invoices ADD INDEX idx_invoices_order_type (order_type);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND INDEX_NAME = 'idx_invoices_status') THEN
        ALTER TABLE invoices ADD INDEX idx_invoices_status (status);
    END IF;

    -- ----- Indexes on customers -----
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND INDEX_NAME = 'idx_customers_phone') THEN
        ALTER TABLE customers ADD INDEX idx_customers_phone (phone);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND INDEX_NAME = 'idx_customers_name') THEN
        ALTER TABLE customers ADD INDEX idx_customers_name (name);
    END IF;
END$$
DELIMITER ;

CALL add_preorder_schema();
DROP PROCEDURE IF EXISTS add_preorder_schema;
