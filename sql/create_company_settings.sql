-- Create company_settings table if it doesn't exist
-- Run this in phpMyAdmin SQL tab

CREATE TABLE IF NOT EXISTS company_settings (
    id INT PRIMARY KEY,
    company_name_en VARCHAR(150) NOT NULL DEFAULT 'BURGE AL SALHIYA',
    company_name_ar VARCHAR(150) NOT NULL DEFAULT 'برج الصالحية',
    address VARCHAR(255) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    logo_path VARCHAR(255) DEFAULT '',
    invoice_footer VARCHAR(255) DEFAULT 'Thank you for your visit!'
);

-- Insert default company settings if row doesn't exist
INSERT IGNORE INTO company_settings (id, company_name_en, company_name_ar, address, phone, email, logo_path, invoice_footer)
VALUES (1, 'BURGE AL SALHIYA', 'برج الصالحية', '', '', '', '', 'Thank you for your visit!');
