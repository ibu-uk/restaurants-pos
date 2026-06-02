-- ============================================
-- Migration for existing databases
-- Run this in phpMyAdmin on your pos_salhiya DB
-- Adds company_settings table and performance indexes
-- Safe to run multiple times
-- ============================================
USE pos_salhiya;

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
-- PERFORMANCE INDEXES
-- ============================================
ALTER TABLE invoices      ADD INDEX IF NOT EXISTS idx_invoices_created_at  (created_at);
ALTER TABLE invoices      ADD INDEX IF NOT EXISTS idx_invoices_user_id     (user_id);
ALTER TABLE invoices      ADD INDEX IF NOT EXISTS idx_invoices_payment     (payment_mode);
ALTER TABLE invoice_items ADD INDEX IF NOT EXISTS idx_invoice_items_inv_id (invoice_id);
ALTER TABLE items         ADD INDEX IF NOT EXISTS idx_items_category       (category_id);
ALTER TABLE items         ADD INDEX IF NOT EXISTS idx_items_active         (is_active);
