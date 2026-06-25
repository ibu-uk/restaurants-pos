<?php
// Run this once on the droplet to fix missing schema
require_once 'db/connect.php';
header('Content-Type: text/plain');

$fixes = [];

// 1. order_type column
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'order_type'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE invoices ADD COLUMN order_type VARCHAR(20) DEFAULT NULL AFTER status");
    $fixes[] = 'Added order_type column';
}

// 2. customer_id column (for linking customers)
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'customer_id'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE invoices ADD COLUMN customer_id INT DEFAULT NULL AFTER customer_phone");
    $fixes[] = 'Added customer_id column';
}

// 3. user_name column
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'user_name'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE invoices ADD COLUMN user_name VARCHAR(100) DEFAULT NULL AFTER user_id");
    $fixes[] = 'Added user_name column';
}

// 4. customers table address column
$res = $conn->query("SHOW COLUMNS FROM customers LIKE 'address'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE customers ADD COLUMN address TEXT DEFAULT NULL AFTER phone");
    $fixes[] = 'Added customers.address column';
}

// 5. Index on customers.name
$res = $conn->query("SHOW INDEX FROM customers WHERE Key_name = 'idx_name'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE customers ADD INDEX idx_name (name)");
    $fixes[] = 'Added idx_name on customers.name';
}

// 6. Index on customers.phone
$res = $conn->query("SHOW INDEX FROM customers WHERE Key_name = 'idx_phone'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE customers ADD INDEX idx_phone (phone)");
    $fixes[] = 'Added idx_phone on customers.phone';
}

// 7. status enum must include 'cancelled' (required to cancel pre-orders)
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'status'");
$col = $res->fetch_assoc();
if ($col && strpos($col['Type'], "'cancelled'") === false) {
    $conn->query("ALTER TABLE invoices MODIFY COLUMN status ENUM('open','paid','cancelled') DEFAULT 'open'");
    $fixes[] = "Added 'cancelled' to invoices.status enum";
}

// 8. logo_on_receipt column in company_settings (for receipt logo toggle)
$res = $conn->query("SHOW COLUMNS FROM company_settings LIKE 'logo_on_receipt'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE company_settings ADD COLUMN logo_on_receipt TINYINT(1) DEFAULT 1 AFTER invoice_footer");
    $fixes[] = "Added logo_on_receipt column to company_settings";
}

// 9. subtotal column in invoices (for discount calculation)
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'subtotal'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE invoices ADD COLUMN subtotal DECIMAL(10,3) DEFAULT 0 AFTER payment_reference");
    if ($conn->error) {
        $fixes[] = "Error adding subtotal: " . $conn->error;
    } else {
        $fixes[] = "Added subtotal column to invoices";
    }
} else {
    $fixes[] = "subtotal column already exists";
}

// 10. discount column in invoices (for discount amount)
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'discount'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE invoices ADD COLUMN discount DECIMAL(10,3) DEFAULT 0 AFTER subtotal");
    if ($conn->error) {
        $fixes[] = "Error adding discount: " . $conn->error;
    } else {
        $fixes[] = "Added discount column to invoices";
    }
} else {
    $fixes[] = "discount column already exists";
}

// 11. refund_invoice_id column in invoices (to link refund to original invoice)
$res = $conn->query("SHOW COLUMNS FROM invoices LIKE 'refund_invoice_id'");
if ($res->num_rows === 0) {
    $conn->query("ALTER TABLE invoices ADD COLUMN refund_invoice_id INT DEFAULT NULL AFTER discount");
    if ($conn->error) {
        $fixes[] = "Error adding refund_invoice_id: " . $conn->error;
    } else {
        $fixes[] = "Added refund_invoice_id column to invoices";
    }
} else {
    $fixes[] = "refund_invoice_id column already exists";
}

echo count($fixes) ? implode("\n", $fixes) : 'No schema changes needed — everything is up to date.';

$conn->close();
?>
