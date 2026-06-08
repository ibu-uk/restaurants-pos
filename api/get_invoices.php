<?php
// ============================================
// API: Get Invoices
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
// Allow caller to request a larger page size (e.g. report export), capped at 10000 for safety.
$limit = isset($_GET['per_page']) ? min(10000, max(1, intval($_GET['per_page']))) : 50;
$offset = ($page - 1) * $limit;

$where = [];
if (isset($_GET['today']) && $_GET['today'] == '1') {
    $where[] = "DATE(created_at) = CURDATE()";
}
if (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) {
    $from = $conn->real_escape_string($_GET['from']);
    $where[] = "DATE(created_at) >= '$from'";
}
if (!empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])) {
    $to = $conn->real_escape_string($_GET['to']);
    $where[] = "DATE(created_at) <= '$to'";
}
if (!empty($_GET['user_id']) && is_admin()) {
    $user_id = intval($_GET['user_id']);
    if ($user_id > 0) $where[] = "user_id = $user_id";
}
if (!empty($_GET['payment'])) {
    $payment = $conn->real_escape_string($_GET['payment']);
    $where[] = "payment_mode = '$payment'";
}
if (!is_admin()) {
    $current_user = current_user();
    $where[] = "user_id = " . intval($current_user['id']);
}
// Only show paid invoices (exclude held/open table orders).
// Use COALESCE so old invoices without a status column value still appear.
$where[] = "COALESCE(status, 'paid') = 'paid'";
$where_sql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';

// Get invoice by ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $extra = is_admin() ? '' : ' AND user_id = ' . intval(current_user()['id']);
    $inv = $conn->query("SELECT * FROM invoices WHERE id = $id $extra")->fetch_assoc();
    if (!$inv) { echo json_encode(['error' => 'Not found']); exit; }
    $items = [];
    $res = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id");
    while ($row = $res->fetch_assoc()) $items[] = $row;
    $inv['items'] = $items;
    echo json_encode($inv);
    exit;
}

// Get invoice list
$summary = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(total), 0) as revenue FROM invoices $where_sql")->fetch_assoc();
$total_count = intval($summary['c']);
$result = $conn->query("SELECT * FROM invoices $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$invoices = [];
while ($row = $result->fetch_assoc()) $invoices[] = $row;

$today_summary = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(total), 0) as revenue FROM invoices WHERE DATE(created_at) = CURDATE()")->fetch_assoc();
$user_summary = $conn->query("SELECT COALESCE(user_name, 'Unknown') as user_name, COUNT(*) as invoice_count, COALESCE(SUM(total), 0) as total_sales FROM invoices $where_sql GROUP BY user_id, user_name ORDER BY total_sales DESC")->fetch_all(MYSQLI_ASSOC);
$users = [];
if (is_admin()) {
    $users_res = $conn->query("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name");
    while ($u = $users_res->fetch_assoc()) $users[] = $u;
}

echo json_encode([
    'invoices'    => $invoices,
    'total'       => $total_count,
    'revenue'     => floatval($summary['revenue']),
    'today_total' => intval($today_summary['c']),
    'today_revenue' => floatval($today_summary['revenue']),
    'user_summary' => $user_summary,
    'users'       => $users,
    'page'        => $page,
    'total_pages' => ceil($total_count / $limit)
]);
$conn->close();
?>
