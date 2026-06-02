-- ============================================
-- Add payment_reference column to invoices table
-- Run this in phpMyAdmin after selecting the pos_bawarchi database
-- ============================================

USE pos_bawarchi;

ALTER TABLE invoices ADD COLUMN payment_reference VARCHAR(100) DEFAULT NULL AFTER payment_mode;
