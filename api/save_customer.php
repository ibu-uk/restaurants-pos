<?php
// ============================================
// API: Save New Customer
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = isset($data['name']) ? trim($data['name']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$address = isset($data['address']) ? trim($data['address']) : '';

if ($name === '' || $phone === '') {
    echo json_encode(['error' => 'Name and phone are required']);
    exit;
}

// Check if phone already exists
$chk = $conn->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
$chk->bind_param('s', $phone);
$chk->execute();
$existing = $chk->get_result()->fetch_assoc();
$chk->close();

if ($existing) {
    echo json_encode(['error' => 'Customer with this phone already exists', 'id' => $existing['id']]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $name, $phone, $address);
$stmt->execute();
$id = $conn->insert_id;
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'id' => $id, 'name' => $name, 'phone' => $phone]);
?>
