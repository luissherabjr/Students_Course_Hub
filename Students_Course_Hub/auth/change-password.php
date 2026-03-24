<?php
/**
 * Change Password - With password hashing
 */

require_once '../includes/config.php';

// Must be logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Process form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verify CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'Please fill all fields';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match';
        } elseif (strlen($new) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            // Verify current password using hashed password
            $stmt = mysqli_prepare($conn, "SELECT Password FROM AdminUsers WHERE AdminID = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            
            // NEW: Use verifyPassword() instead of plain text comparison
            if (verifyPassword($current, $user['Password'])) {
                // Hash the new password
                $hashed_password = hashPassword($new);
                
                $stmt2 = mysqli_prepare($conn, "UPDATE AdminUsers SET Password = ? WHERE AdminID = ?");
                mysqli_stmt_bind_param($stmt2, "si", $hashed_password, $user_id);
                
                if (mysqli_stmt_execute($stmt2)) {
                    $success = 'Password changed successfully! Please login again.';
                    logSecurityEvent('Password changed for user: ' . $_SESSION['user_username']);
                    
                    // Logout
                    session_destroy();
                    session_start();
                    $_SESSION['success'] = $success;
                    header('Location: ' . BASE_URL . 'auth/login.php');
                    exit();
                } else {
                    $error = 'Error updating password';
                }
                mysqli_stmt_close($stmt2);
            } else {
                $error = 'Current password is incorrect';
                logSecurityEvent('Failed password change attempt');
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Check if first login (check if password is still default)
$is_first_login = false;
$stmt = mysqli_prepare($conn, "SELECT Password FROM AdminUsers WHERE AdminID = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Check if using default password by verifying against hashed default
if (verifyPassword('password123', $user['Password'])) {
    $is_first_login = true;
}
mysqli_stmt_close($stmt);

$csrf_token = generateCSRFToken();
include '../includes/header.php';
?>

<div class="form-container" style="max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 20px;">
        <i class="fas fa-key"></i> Change Password
    </h1>
    
    <?php if ($is_first_login): ?>
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>First Login!</strong> Please change your default password immediately.
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required minlength="6">
            <small>Minimum 6 characters</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Change Password
            </button>
            <a href="<?php echo isAdmin() ? BASE_PATH . 'admin/dashboard.php' : BASE_PATH . 'staff/dashboard.php'; ?>" 
               class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>