<?php
// ============================================
// API: Process Refund
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../refund_errors.log');
header('Content-Type: application/json');

try {
    session_start();

    if (!isset($_SESSION['user'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    require_once '../db/connect.php';

    $user = $_SESSION['user'];
    $user_id = intval($user['id'] ?? 0);
    $user_role = $user['role'] ?? '';

    if (!$user_id || $user_role !== 'admin') {
        echo json_encode(['error' => 'Access denied. Admin only.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || empty($data['original_invoice_id']) || empty($data['refund_items'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $original_invoice_id = intval($data['original_invoice_id']);
    $original_invoice_number = $data['original_invoice_number'];
    $refund_items = $data['refund_items'];
    $refund_total = floatval($data['refund_total']);
    $refund_reason = $data['refund_reason'] ?? 'other';

    $user_name = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['username']) ? $user['username'] : 'Unknown');

    $check = $conn->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'paid' LIMIT 1");
    $check->bind_param('i', $original_invoice_id);
    $check->execute();
    $original_invoice = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$original_invoice) {
        echo json_encode(['error' => 'Original invoice not found or not paid']);
        exit;
    }

    $refund_invoice_number = 'REF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, user_id, user_name, payment_mode, payment_reference, status, subtotal, discount, total, cash_paid, change_due, refund_invoice_id) VALUES (?, ?, ?, 'Refund', ?, 'paid', ?, ?, ?, ?, ?, ?)");
        $payment_reference = 'Refund for ' . $original_invoice_number . ' (' . $refund_reason . ')';
        $subtotal = abs($refund_total);
        $discount = 0;
        $total = -$refund_total;
        $cash_paid = 0;
        $change_due = 0;

        $stmt->bind_param('sisssddddd', $refund_invoice_number, $user_id, $user_name, $payment_reference, $subtotal, $discount, $total, $cash_paid, $change_due, $original_invoice_id);
        $stmt->execute();
        $refund_invoice_id = $conn->insert_id;
        $stmt->close();

        $stmt2 = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, item_name_ar, size, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($refund_items as $item) {
            $item_name = $item['item_name'];
            $item_name_ar = isset($item['item_name_ar']) ? $item['item_name_ar'] : '';
            $size = isset($item['size']) ? $item['size'] : null;
            $price = floatval($item['price']);
            $qty = intval($item['quantity']);
            $subtotal = $price * $qty;

            $stmt2->bind_param('isssdid', $refund_invoice_id, $item_name, $item_name_ar, $size, $price, $qty, $subtotal);
            $stmt2->execute();
        }
        $stmt2->close();

        $conn->commit();
        echo json_encode(['success' => true, 'refund_invoice_id' => $refund_invoice_id, 'refund_invoice_number' => $refund_invoice_number]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }

    $conn->close();
} catch (Throwable $e) {
    error_log('Refund API error: ' . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
