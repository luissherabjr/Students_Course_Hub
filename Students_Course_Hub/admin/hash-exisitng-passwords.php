<?php
/**
 * ONE-TIME SCRIPT: Convert existing plain text passwords to hashed passwords
 * RUN THIS ONCE, THEN DELETE FOR SECURITY!
 */

require_once '../includes/config.php';

// Only allow running from localhost for security
$allowed_ips = ['127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied. Run this script only from localhost.');
}

echo "<h2>Converting Passwords to Hashed Format</h2>";
echo "<pre>";

$result = mysqli_query($conn, "SELECT AdminID, Username, Password FROM AdminUsers");
$converted = 0;

while ($user = mysqli_fetch_assoc($result)) {
    $plain_password = $user['Password'];
    $hashed_password = hashPassword($plain_password);
    
    $update = mysqli_prepare($conn, "UPDATE AdminUsers SET Password = ? WHERE AdminID = ?");
    mysqli_stmt_bind_param($update, "si", $hashed_password, $user['AdminID']);
    
    if (mysqli_stmt_execute($update)) {
        echo "✅ User: " . $user['Username'] . " (ID: {$user['AdminID']}) - Password converted\n";
        $converted++;
    } else {
        echo "❌ User: " . $user['Username'] . " (ID: {$user['AdminID']}) - Conversion failed\n";
    }
    mysqli_stmt_close($update);
}

echo "\n\n========================================\n";
echo "Conversion complete! $converted passwords converted.\n";
echo "========================================\n";
echo "\n⚠️  IMPORTANT: DELETE THIS FILE NOW FOR SECURITY! ⚠️\n";

mysqli_close($conn);
?>