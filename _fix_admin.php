<?php
require_once __DIR__ . '/db/connect.php';

$plain = 'admin123';
$hash = password_hash($plain, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash = ?, role = 'admin', is_active = 1, full_name = 'Administrator' WHERE username = 'admin'");
$stmt->bind_param('s', $hash);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo "UPDATE failed: " . $conn->error . "\n";
    exit(1);
}

$stmt2 = $conn->prepare("SELECT password_hash FROM users WHERE username = 'admin'");
$stmt2->execute();
$stmt2->bind_result($stored);
$stmt2->fetch();
$stmt2->close();

$verify = password_verify($plain, $stored);

echo "Generated hash prefix: " . substr($hash, 0, 7) . "\n";
echo "Stored hash prefix:    " . substr($stored, 0, 7) . "\n";
echo "Verify result:          " . ($verify ? "PASS" : "FAIL") . "\n";

$conn->close();
unlink(__FILE__);
