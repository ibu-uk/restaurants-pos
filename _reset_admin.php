<?php
require_once 'db/connect.php';

$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
$stmt->bind_param('s', $hash);
$stmt->execute();
$stmt->close();

echo "✅ Password reset successfully!<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "Hash used: " . $hash . "<br>";
echo "<br><strong>⚠️ Delete this file after use!</strong>";
?>