<?php
// ============================================
// API: Get Pending Pre-Orders
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

$whereUser = is_admin() ? '' : ' AND i.user_id = ' . intval(current_user()['id']);

$res = $conn->query("SELECT i.id, i.invoice_number, i.customer_name, i.customer_phone, i.total, i.created_at, i.user_name
    FROM invoices i
    WHERE i.order_type = 'pre_order' AND i.status = 'open' $whereUser
    ORDER BY i.created_at DESC");

$orders = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
}
$conn->close();
echo json_encode($orders);
?>
