<?php
// ============================================
// API: Menu Items + Price Update
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';

// GET: Return categories + subcategories + items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_api_login();
    $include_empty = isset($_GET['settings']) && $_GET['settings'] == '1';
    $all = [];
    $cat_res = $conn->query("SELECT * FROM categories ORDER BY sort_order, id");
    while ($cat = $cat_res->fetch_assoc()) {
        $cat['items'] = [];
        $cat['children'] = [];
        $all[intval($cat['id'])] = $cat;
    }

    $item_res = $conn->query("SELECT * FROM items WHERE is_active = 1 ORDER BY id");
    while ($item = $item_res->fetch_assoc()) {
        $cid = intval($item['category_id']);
        if (isset($all[$cid])) $all[$cid]['items'][] = $item;
    }

    foreach ($all as $id => $cat) {
        $pid = intval($cat['parent_id']);
        if ($pid > 0 && isset($all[$pid])) {
            $all[$pid]['children'][] = &$all[$id];
        }
    }
    unset($cat);

    function category_has_content($cat) {
        if (count($cat['items']) > 0) return true;
        foreach ($cat['children'] as $child) {
            if (category_has_content($child)) return true;
        }
        return false;
    }

    $categories = [];
    foreach ($all as $id => $cat) {
        if (intval($cat['parent_id']) > 0) continue;
        $has_content = category_has_content($cat);
        if ($include_empty || $has_content) $categories[] = $cat;
    }
    echo json_encode($categories);
    exit;
}

// POST: Add/update item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_api_admin();
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'add_category') {
        $parent_id = isset($data['parent_id']) && intval($data['parent_id']) > 0 ? intval($data['parent_id']) : null;
        $name_en = trim($data['name_en']);
        $name_ar = trim($data['name_ar']);

        if ($name_en === '' || $name_ar === '') {
            echo json_encode(['error' => 'Invalid category data']); exit;
        }

        $sort_res = $conn->query("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_sort FROM categories");
        $sort_order = intval($sort_res->fetch_assoc()['next_sort']);
        $stmt = $conn->prepare("INSERT INTO categories (parent_id, name_en, name_ar, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('issi', $parent_id, $name_en, $name_ar, $sort_order);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'update_category') {
        $id = intval($data['id']);
        $parent_id = isset($data['parent_id']) && intval($data['parent_id']) > 0 ? intval($data['parent_id']) : null;
        $name_en = trim($data['name_en']);
        $name_ar = trim($data['name_ar']);

        if ($id <= 0 || $name_en === '' || $name_ar === '' || $parent_id === $id) {
            echo json_encode(['error' => 'Invalid category data']); exit;
        }

        $check_parent = $parent_id;
        while ($check_parent !== null) {
            $parent_res = $conn->query("SELECT parent_id FROM categories WHERE id = " . intval($check_parent));
            if (!$parent_res || $parent_res->num_rows === 0) break;
            $parent_row = $parent_res->fetch_assoc();
            $check_parent = $parent_row['parent_id'] !== null ? intval($parent_row['parent_id']) : null;
            if ($check_parent === $id) {
                echo json_encode(['error' => 'Cannot move category under its own subcategory']); exit;
            }
        }

        $stmt = $conn->prepare("UPDATE categories SET parent_id = ?, name_en = ?, name_ar = ? WHERE id = ?");
        $stmt->bind_param('issi', $parent_id, $name_en, $name_ar, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'add_item') {
        $category_id = intval($data['category_id']);
        $name_en = trim($data['name_en']);
        $name_ar = trim($data['name_ar']);
        $price = floatval($data['price']);

        if ($category_id <= 0 || $name_en === '' || $name_ar === '' || $price < 0) {
            echo json_encode(['error' => 'Invalid item data']); exit;
        }

        $stmt = $conn->prepare("INSERT INTO items (category_id, name_en, name_ar, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('issd', $category_id, $name_en, $name_ar, $price);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'add_size_item') {
        $category_id = intval($data['category_id']);
        $name_en = trim($data['name_en']);
        $name_ar = trim($data['name_ar']);
        $price_small = floatval($data['price_small']);
        $price_medium = floatval($data['price_medium']);
        $price_large = floatval($data['price_large']);

        if ($category_id <= 0 || $name_en === '' || $name_ar === '' || $price_small < 0 || $price_medium < 0 || $price_large < 0) {
            echo json_encode(['error' => 'Invalid item data']); exit;
        }

        $stmt = $conn->prepare("INSERT INTO items (category_id, name_en, name_ar, price_small, price_medium, price_large) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issddd', $category_id, $name_en, $name_ar, $price_small, $price_medium, $price_large);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'update_item') {
        $id = intval($data['id']);
        $category_id = isset($data['category_id']) ? intval($data['category_id']) : 0;
        $name_en = trim($data['name_en']);
        $name_ar = trim($data['name_ar']);
        $price = floatval($data['price']);

        if ($id <= 0 || $category_id <= 0 || $name_en === '' || $name_ar === '' || $price < 0) {
            echo json_encode(['error' => 'Invalid item data']); exit;
        }

        $stmt = $conn->prepare("UPDATE items SET category_id = ?, name_en = ?, name_ar = ?, price = ? WHERE id = ?");
        $stmt->bind_param('issdi', $category_id, $name_en, $name_ar, $price, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'update_size_item') {
        $id = intval($data['id']);
        $category_id = isset($data['category_id']) ? intval($data['category_id']) : 0;
        $name_en = trim($data['name_en']);
        $name_ar = trim($data['name_ar']);
        $price_small = floatval($data['price_small']);
        $price_medium = floatval($data['price_medium']);
        $price_large = floatval($data['price_large']);

        if ($id <= 0 || $category_id <= 0 || $name_en === '' || $name_ar === '' || $price_small < 0 || $price_medium < 0 || $price_large < 0) {
            echo json_encode(['error' => 'Invalid item data']); exit;
        }

        $stmt = $conn->prepare("UPDATE items SET category_id = ?, name_en = ?, name_ar = ?, price_small = ?, price_medium = ?, price_large = ? WHERE id = ?");
        $stmt->bind_param('issdddi', $category_id, $name_en, $name_ar, $price_small, $price_medium, $price_large, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'delete_item') {
        $id = intval($data['id']);
        if ($id <= 0) { echo json_encode(['error' => 'Invalid ID']); exit; }
        $conn->query("UPDATE items SET is_active = 0 WHERE id = $id");
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'delete_category') {
        $id = intval($data['id']);
        if ($id <= 0) { echo json_encode(['error' => 'Invalid ID']); exit; }
        // Get all child category IDs
        $child_ids = [$id];
        $child_res = $conn->query("SELECT id FROM categories WHERE parent_id = $id");
        while ($row = $child_res->fetch_assoc()) $child_ids[] = intval($row['id']);
        $ids_sql = implode(',', $child_ids);
        // Delete all items first (removes FK reference), then categories
        $conn->query("DELETE FROM items WHERE category_id IN ($ids_sql)");
        $conn->query("DELETE FROM categories WHERE parent_id = $id");
        $conn->query("DELETE FROM categories WHERE id = $id");
        echo json_encode(['success' => true]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'convert_to_size_based') {
        $id = intval($data['id']);
        $base_price = floatval($data['base_price']);

        if ($id <= 0 || $base_price < 0) {
            echo json_encode(['error' => 'Invalid data']); exit;
        }

        // Convert: use base_price for medium, calculate small and large
        $price_small = round($base_price * 0.85, 3);  // 15% less for small
        $price_medium = $base_price;                   // same as original
        $price_large = round($base_price * 1.15, 3);   // 15% more for large

        $stmt = $conn->prepare("UPDATE items SET price = NULL, price_small = ?, price_medium = ?, price_large = ? WHERE id = ?");
        $stmt->bind_param('dddi', $price_small, $price_medium, $price_large, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if (!isset($data['id'])) { echo json_encode(['error' => 'Missing ID']); exit; }

    $id    = intval($data['id']);
    $field = isset($data['field']) ? $data['field'] : 'price';

    $allowed_fields = ['price', 'price_small', 'price_medium', 'price_large'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['error' => 'Invalid field']); exit;
    }

    $value = floatval($data['value']);
    $stmt = $conn->prepare("UPDATE items SET $field = ? WHERE id = ?");
    $stmt->bind_param('di', $value, $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
$conn->close();
?>
