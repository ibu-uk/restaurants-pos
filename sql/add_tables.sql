-- Add table management support
-- Run this in phpMyAdmin

CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    status ENUM('available','occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default tables (adjust as needed)
INSERT IGNORE INTO restaurant_tables (id, name, status) VALUES
(1, 'Table 1', 'available'),
(2, 'Table 2', 'available'),
(3, 'Table 3', 'available'),
(4, 'Table 4', 'available'),
(5, 'Table 5', 'available'),
(6, 'Table 6', 'available'),
(7, 'Table 7', 'available'),
(8, 'Table 8', 'available'),
(9, 'Table 9', 'available'),
(10, 'Table 10', 'available'),
(11, 'Takeaway', 'available'),
(12, 'Delivery', 'available');

-- Add table columns to invoices
-- If you get "Duplicate column" error, the column already exists - skip that line
ALTER TABLE invoices ADD COLUMN table_id INT DEFAULT NULL AFTER user_name;
ALTER TABLE invoices ADD COLUMN table_name VARCHAR(50) DEFAULT NULL AFTER table_id;
ALTER TABLE invoices ADD COLUMN status ENUM('open','paid') DEFAULT 'paid' AFTER table_name;
