<?php
// Manage restaurant tables (add / rename / delete) - admin only
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

if (!is_admin()) {
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

if ($action === 'add_table') {
    $name = trim($data['name'] ?? '');
    if ($name === '') { echo json_encode(['error' => 'Table name is required']); exit; }

    $stmt = $conn->prepare("SELECT id FROM restaurant_tables WHERE name = ? LIMIT 1");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        echo json_encode(['error' => 'A table with this name already exists']); exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO restaurant_tables (name, status) VALUES (?, 'available')");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'id' => $newId]);
    exit;
}

if ($action === 'rename_table') {
    $id = intval($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    if ($id <= 0 || $name === '') { echo json_encode(['error' => 'Invalid table data']); exit; }

    $stmt = $conn->prepare("SELECT id FROM restaurant_tables WHERE name = ? AND id <> ? LIMIT 1");
    $stmt->bind_param('si', $name, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        echo json_encode(['error' => 'Another table already has this name']); exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE restaurant_tables SET name = ? WHERE id = ?");
    $stmt->bind_param('si', $name, $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete_table') {
    $id = intval($data['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['error' => 'Invalid table']); exit; }

    // Block deletion if the table has an open (unpaid) order
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM invoices WHERE table_id = ? AND status = 'open'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $open = intval($stmt->get_result()->fetch_assoc()['c']);
    $stmt->close();
    if ($open > 0) {
        echo json_encode(['error' => 'This table has an open order. Clear or pay it first.']); exit;
    }

    $stmt = $conn->prepare("DELETE FROM restaurant_tables WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>
