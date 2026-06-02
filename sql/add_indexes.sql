-- ============================================
-- Run this in phpMyAdmin on your pos_salhiya DB
-- Adds indexes for fast queries with 10,000+ records
-- Compatible with MySQL 5.7+
-- ============================================
USE pos_salhiya;

ALTER TABLE invoices      ADD INDEX idx_invoices_created_at  (created_at);
ALTER TABLE invoices      ADD INDEX idx_invoices_user_id     (user_id);
ALTER TABLE invoices      ADD INDEX idx_invoices_payment     (payment_mode);
ALTER TABLE invoice_items ADD INDEX idx_invoice_items_inv_id (invoice_id);
ALTER TABLE items         ADD INDEX idx_items_category       (category_id);
ALTER TABLE items         ADD INDEX idx_items_active         (is_active);
