-- Add image_path column to items table
-- Run this SQL to enable item images
-- If you get an error "Duplicate column name", it means the column already exists

ALTER TABLE items ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER price;
