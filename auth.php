<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function get_company_settings() {
    global $conn;
    static $company = null;
    if ($company === null) {
        if ($conn) {
            $result = $conn->query("SELECT * FROM company_settings WHERE id = 1");
            $company = $result ? $result->fetch_assoc() : [
                'company_name_en' => 'BURGE AL SALHIYA',
                'company_name_ar' => 'برج الصالحية',
                'address' => '',
                'phone' => '',
                'email' => '',
                'logo_path' => '',
                'invoice_footer' => 'Thank you for your visit!'
            ];
        } else {
            $company = [
                'company_name_en' => 'BURGE AL SALHIYA',
                'company_name_ar' => 'برج الصالحية',
                'address' => '',
                'phone' => '',
                'email' => '',
                'logo_path' => '',
                'invoice_footer' => 'Thank you for your visit!'
            ];
        }
    }
    return $company;
}

function is_logged_in() {
    return current_user() !== null;
}

function is_admin() {
    $user = current_user();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function is_staff() {
    $user = current_user();
    return $user && isset($user['role']) && $user['role'] === 'staff';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    $user = current_user();
    if (intval($user['id'] ?? 0) <= 0 || empty($user['username'] ?? '')) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        echo 'Access denied. Admin only.';
        exit;
    }
}

function require_api_login() {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => 'Login required']);
        exit;
    }
}

function require_api_admin() {
    require_api_login();
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin only']);
        exit;
    }
}
?>
