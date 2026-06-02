<?php
require_once 'db/connect.php';
require_once 'auth.php';
require_admin();
$company = get_company_settings();
$currentUser = current_user();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] === 'admin' ? 'admin' : 'staff';
        if ($username === '' || $full_name === '' || strlen($password) < 4) {
            $error = 'Username, full name, and password minimum 4 characters are required.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, full_name, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param('ssss', $username, $full_name, $hash, $role);
            if ($stmt->execute()) $message = 'User added successfully.'; else $error = 'Could not add user. Username may already exist.';
            $stmt->close();
        }
    }
    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] === 'admin' ? 'admin' : 'staff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($id === intval($currentUser['id']) && $is_active === 0) {
            $error = 'You cannot deactivate your own account.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, role = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param('ssii', $full_name, $role, $is_active, $id);
            if ($stmt->execute()) $message = 'User updated.'; else $error = 'Could not update user.';
            $stmt->close();
        }
    }
    if ($action === 'password') {
        $id = intval($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 4) {
            $error = 'Password must be at least 4 characters.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $id);
            if ($stmt->execute()) $message = 'Password updated.'; else $error = 'Could not update password.';
            $stmt->close();
        }
    }
}

$users = $conn->query("SELECT id, username, full_name, role, is_active, created_at FROM users ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users - Burge Al Salhiya</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body { font-family:Tahoma,Arial,sans-serif; background:#f5f7fa; color:#2c3e50; font-size:14px; min-height:100%; }
#header { background:linear-gradient(135deg, #8ab4f8, #7aa0e8); padding:10px 20px; display:flex; justify-content:space-between; align-items:center; }
#header h1 { font-size:18px; color:#fff; }
#header a { color:#fff; text-decoration:none; background:rgba(255,255,255,0.2); padding:6px 14px; border-radius:4px; margin-left:6px; }
#content { padding:20px; max-width:1050px; margin:0 auto; }
.box { background:#fff; border:1px solid #dee2e6; border-radius:10px; padding:16px; margin-bottom:18px; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.grid { display:grid; grid-template-columns:1fr 1fr 1fr 130px 120px; gap:8px; align-items:end; }
label { display:block; color:#495057; font-size:11px; margin-bottom:4px; }
input, select { width:100%; padding:8px; border-radius:5px; border:1px solid #ced4da; background:#fff; color:#2c3e50; }
input:focus, select:focus { outline:none; border-color:#8ab4f8; }
button { padding:8px 12px; border:none; border-radius:5px; background:#27ae60; color:#fff; font-weight:bold; cursor:pointer; }
button.secondary { background:#8ab4f8; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
th { text-align:left; background:#e9ecef; color:#495057; padding:9px; font-size:12px; }
td { padding:8px; border-bottom:1px solid #e9ecef; }
.badge { padding:3px 8px; border-radius:12px; font-size:11px; }
.admin { background:#6f2c91; color:#fff; }
.staff { background:#174d78; color:#dff1ff; }
.active { color:#27ae60; font-weight:bold; }
.inactive { color:#e74c3c; font-weight:bold; }
.msg { padding:10px; border-radius:8px; margin-bottom:14px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.err { padding:10px; border-radius:8px; margin-bottom:14px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.small-form { display:flex; gap:6px; align-items:center; }
.small-form input { width:120px; }
</style>
</head>
<body>
<div id="header">
  <h1>&#128101; User Management - <?php echo htmlspecialchars($company['company_name_en']); ?></h1>
  <div>
    <a href="settings.php">&#9881; Settings</a>
    <a href="index.php">&#8592; Back to POS</a>
    <a href="logout.php" onclick="showConfirm('Logout','Are you sure you want to logout?','Yes, Logout','\uD83D\uDEAA',function(){ window.location.href='logout.php'; }); return false;">Logout</a>
  </div>
</div>
<div id="content">
  <?php if ($message): ?><div class="msg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <div class="box">
    <form method="post" class="grid">
      <input type="hidden" name="action" value="add">
      <div><label>Username</label><input name="username" required></div>
      <div><label>Full Name</label><input name="full_name" required></div>
      <div><label>Password</label><input name="password" type="password" required></div>
      <div><label>Role</label><select name="role"><option value="staff">Staff</option><option value="admin">Admin</option></select></div>
      <button type="submit">Add User</button>
    </form>
  </div>
  <div class="box">
    <table>
      <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th><th>Update</th><th>Password</th></tr></thead>
      <tbody>
      <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td><?php echo intval($u['id']); ?></td>
          <td><?php echo htmlspecialchars($u['username']); ?></td>
          <td>
            <form method="post" class="small-form" id="form-<?php echo intval($u['id']); ?>">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?php echo intval($u['id']); ?>">
              <input name="full_name" value="<?php echo htmlspecialchars($u['full_name']); ?>">
          </td>
          <td><select name="role"><option value="staff" <?php if ($u['role']==='staff') echo 'selected'; ?>>Staff</option><option value="admin" <?php if ($u['role']==='admin') echo 'selected'; ?>>Admin</option></select></td>
          <td><label><input type="checkbox" name="is_active" <?php if (intval($u['is_active'])===1) echo 'checked'; ?>> Active</label></td>
          <td><button type="submit" class="secondary">Save</button></form></td>
          <td>
            <form method="post" class="small-form">
              <input type="hidden" name="action" value="password">
              <input type="hidden" name="id" value="<?php echo intval($u['id']); ?>">
              <input name="password" type="password" placeholder="New password">
              <button type="submit">Set</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'includes/confirm_modal.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
