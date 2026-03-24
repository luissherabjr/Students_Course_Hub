<?php
/**
 * Login Page - Supports both plain text and hashed passwords
 */

require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(BASE_URL . 'admin/dashboard.php');
    } elseif (isStaff()) {
        redirect(BASE_URL . 'staff/dashboard.php');
    }
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT a.*, s.Name as staff_name, s.StaffID, s.Title, 
                                              s.Department, s.Photo, s.Email as staff_email
                                       FROM AdminUsers a 
                                       LEFT JOIN Staff s ON a.StaffID = s.StaffID 
                                       WHERE a.Username = ? AND a.IsActive = 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Check if password is hashed (starts with $2y$)
            if (strpos($user['Password'], '$2y$') === 0) {
                $password_valid = verifyPassword($password, $user['Password']);
            } else {
                // Plain text password - compare directly
                $password_valid = ($password === $user['Password']);
                
                // Convert to hash if valid
                if ($password_valid) {
                    $new_hash = hashPassword($password);
                    $update = mysqli_prepare($conn, "UPDATE AdminUsers SET Password = ? WHERE AdminID = ?");
                    mysqli_stmt_bind_param($update, "si", $new_hash, $user['AdminID']);
                    mysqli_stmt_execute($update);
                    mysqli_stmt_close($update);
                    logSecurityEvent("Password upgraded to hash for user: " . $username);
                }
            }
            
            if ($password_valid) {
                clearLoginAttempts();
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['AdminID'];
                $_SESSION['user_name'] = $user['FullName'];
                $_SESSION['user_username'] = $user['Username'];
                $_SESSION['user_role'] = $user['Role'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = getUserIP();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                if ($user['StaffID']) {
                    $_SESSION['staff_id'] = $user['StaffID'];
                    $_SESSION['staff_name'] = $user['staff_name'];
                    $_SESSION['staff_title'] = $user['Title'];
                    $_SESSION['staff_department'] = $user['Department'];
                    $_SESSION['staff_photo'] = $user['Photo'];
                    $_SESSION['staff_email'] = $user['staff_email'];
                }
                
                logSecurityEvent("Successful login - Role: " . $user['Role']);
                $_SESSION['success'] = 'Welcome back, ' . $user['FullName'] . '!';
                
                if ($user['Role'] == 'admin') {
                    redirect(BASE_URL . 'admin/dashboard.php');
                } else {
                    redirect(BASE_URL . 'staff/dashboard.php');
                }
            } else {
                $error = 'Invalid password';
                trackLoginAttempt($username);
                logSecurityEvent("Failed login - Invalid password for: $username");
            }
        } else {
            $error = 'User not found or account inactive';
            trackLoginAttempt($username);
            logSecurityEvent("Failed login - Username not found: $username");
        }
        mysqli_stmt_close($stmt);
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Hub - Login</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/fontawesome/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h1>Student Course Hub</h1>
            <p>Sign in to access your dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="demo-credentials">
            <p><i class="fas fa-info-circle"></i> Demo Credentials</p>
            <p><strong>Admin:</strong> admin / admin123</p>
            <p><strong>Staff:</strong> ajohnson / staff123</p>
        </div>
    </div>
</body>
</html>