<?php
require_once 'db/connect.php';
require_once 'auth.php';

// Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$company = get_company_settings();

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, full_name, password_hash, role, is_active FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($user && intval($user['is_active']) === 1 && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => intval($user['id']),
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
        $_SESSION['just_logged_in'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username/password or inactive user.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - <?php echo htmlspecialchars($company['company_name_en']); ?> POS</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f5f7fa; color:#2c3e50; font-family:Tahoma,Arial,sans-serif; }
.login-box { width:360px; background:#fff; border:1px solid #dee2e6; border-radius:14px; padding:26px; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
h1 { text-align:center; color:#2c3e50; font-size:22px; margin-bottom:4px; }
.subtitle { text-align:center; color:#e67e22; margin-bottom:22px; font-size:13px; }
label { display:block; color:#495057; font-size:12px; margin-bottom:6px; }
input { width:100%; padding:12px; border-radius:8px; border:1px solid #ced4da; background:#fff; color:#2c3e50; font-size:15px; margin-bottom:14px; }
input:focus { outline:none; border-color:#8ab4f8; }
button { width:100%; padding:13px; border:none; border-radius:8px; background:linear-gradient(135deg,#8ab4f8,#7aa0e8); color:#fff; font-weight:bold; font-size:16px; cursor:pointer; }
button:hover { background:linear-gradient(135deg,#7aa0e8,#6a90d8); }
.error { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:10px; border-radius:8px; margin-bottom:14px; font-size:13px; }
.hint { margin-top:14px; color:#7f8c8d; font-size:11px; text-align:center; }
</style>
</head>
<body>
<form class="login-box" method="post">
  <h1><?php echo htmlspecialchars($company['company_name_en']); ?></h1>
  <div class="subtitle">Staff Login / دخول الموظفين</div>
  <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <label>Username</label>
  <input type="text" name="username" autocomplete="username" required autofocus>
  <label>Password</label>
  <input type="password" name="password" autocomplete="current-password" required>
  <button type="submit">Login</button>
</form>
</body>
</html>
