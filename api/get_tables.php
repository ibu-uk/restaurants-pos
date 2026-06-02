<?php
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

$result = $conn->query("SELECT t.*, 
    (SELECT COUNT(*) FROM invoices i WHERE i.table_id = t.id AND i.status = 'open') as open_orders
    FROM restaurant_tables t ORDER BY t.id");

$tables = [];
while ($row = $result->fetch_assoc()) {
    $tables[] = $row;
}
echo json_encode($tables);
?>
