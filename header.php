<?php
/**
 * Header File - Contains navigation, user menu, and site header
 * Used across all pages for consistent layout
 */

if (!isset($page_title)) {
    $page_title = 'Dashboard';
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config if not already included
if (!function_exists('isLoggedIn')) {
    require_once dirname(__DIR__) . '/includes/config.php';
}

// Path configuration
$base_url = BASE_PATH; // This is '/students_course_hub/'
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_admin = ($current_dir == 'admin');
$is_staff = ($current_dir == 'staff');
$is_student = ($current_dir == 'student');
$is_login = (basename($_SERVER['PHP_SELF']) == 'login.php');

$root_path = ($is_admin || $is_staff) ? '../' : '';

// Get current user info from session
$current_user_name = $_SESSION['user_name'] ?? 'User';
$current_user_role = $_SESSION['user_role'] ?? '';
$staff_name = $_SESSION['staff_name'] ?? '';
$staff_id = $_SESSION['staff_id'] ?? 0;

//always load common styles
$css_files = ['common.css'];

// Add role-specific CSS
if ($is_admin) {
    $css_files[] = 'admin/admin-style.css';
} elseif ($is_staff) {
    $css_files[] = 'staff/staff-style.css';
} elseif ($is_student) {
    $css_files[] = 'student/student-style.css';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; font-src 'self' https: data:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:;">
    <title>Student Course Hub - <?php echo $page_title; ?></title>
       <!-- common stylesheet-->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/common.css">
       <!-- google font stylesheet-->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
       <!-- font awesome stylesheet-->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/fontawesome/css/all.min.css">

    <!-- Role-Specific Stylesheets -->
    <?php foreach ($css_files as $css_file): ?>
        <?php if ($css_file === 'common.css'): ?>
            <!-- Already loaded above -->
        <?php elseif ($css_file === 'admin/admin-style.css'): ?>
            <link rel="stylesheet" href="<?php echo $base_url; ?>admin/css/admin-style.css">
        <?php elseif ($css_file === 'staff/staff-style.css'): ?>
            <link rel="stylesheet" href="<?php echo $base_url; ?>staff/css/staff-style.css">
        <?php elseif ($css_file === 'student/student-style.css'): ?>
            <link rel="stylesheet" href="<?php echo $base_url; ?>student/css/student-style.css">
        <?php endif; ?>
    <?php endforeach; ?>
    
    <!-- Login page specific styles -->
    <?php if (basename($_SERVER['PHP_SELF']) == 'login.php'): ?>
        <link rel="stylesheet" href="<?php echo $base_url; ?>css/login.css">
    <?php endif; ?>

</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar" aria-label="Main navigation">
        <div class="nav-container">
            <!-- Logo -->
            <a href="<?php echo $base_url; ?>auth/login.php" class="logo" aria-label="Home">
                <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                <span>Student Course Hub</span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <?php if (isLoggedIn()): ?>
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navigation Menu -->
            <div class="nav-menu" id="nav-menu">
                
                <!-- ============================================ -->
                <!-- ADMIN NAVIGATION -->
                <!-- ============================================ -->
                <?php if (hasRole('admin')): ?>
                    <a href="<?php echo $base_url; ?>admin/dashboard.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard
                    </a>
                    <a href="<?php echo $base_url; ?>admin/programmes.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'programmes.php') ? 'active' : ''; ?>">
                        <i class="fas fa-book" aria-hidden="true"></i> Programmes
                    </a>
                    <a href="<?php echo $base_url; ?>admin/modules.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'modules.php') ? 'active' : ''; ?>">
                        <i class="fas fa-cube" aria-hidden="true"></i> Modules
                    </a>
                    <a href="<?php echo $base_url; ?>admin/staff.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'staff.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users" aria-hidden="true"></i> Staff
                    </a>
                    <a href="<?php echo $base_url; ?>home/index.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'staff.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users" aria-hidden="true"></i> Student Interface
                    </a>
                    <a href="<?php echo $base_url; ?>admin/users.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog" aria-hidden="true"></i> Users
                    </a>
                    <a href="<?php echo $base_url; ?>admin/mailing-list.php" class="nav-link <?php echo ($is_admin && basename($_SERVER['PHP_SELF']) == 'mailing-list.php') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope" aria-hidden="true"></i> Mailing List
                    </a>
                    
                <!-- ============================================ -->
                <!-- STAFF NAVIGATION -->
                <!-- ============================================ -->
                <?php elseif (hasRole('staff')): ?>
                    <a href="<?php echo $base_url; ?>staff/dashboard.php" class="nav-link <?php echo ($is_staff && basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i> My Dashboard
                    </a>
                    <?php if ($staff_id > 0): ?>
                    <a href="<?php echo $base_url; ?>staff/my-modules.php" class="nav-link <?php echo ($is_staff && basename($_SERVER['PHP_SELF']) == 'my-modules.php') ? 'active' : ''; ?>">
                        <i class="fas fa-cube" aria-hidden="true"></i> My Modules
                    </a>
                    <a href="<?php echo $base_url; ?>staff/my-programmes.php" class="nav-link <?php echo ($is_staff && basename($_SERVER['PHP_SELF']) == 'my-programmes.php') ? 'active' : ''; ?>">
                        <i class="fas fa-book" aria-hidden="true"></i> My Programmes
                    </a>
                     <a href="<?php echo $base_url; ?>home/index.php" class="nav-link <?php echo ($is_staff && basename($_SERVER['PHP_SELF']) == 'my-programmes.php') ? 'active' : ''; ?>">
                        <i class="fas fa-book" aria-hidden="true"></i> Student Interface
                    </a>
                    <a href="<?php echo $base_url; ?>staff/profile.php" class="nav-link <?php echo ($is_staff && basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                        <i class="fas fa-id-card" aria-hidden="true"></i> My Profile
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- ============================================ -->
                <!-- USER MENU (Right Side) -->
                <!-- ============================================ -->
                <div class="user-menu">
                    <span class="user-name" aria-label="Logged in as">
                        <i class="fas fa-user-circle" aria-hidden="true"></i> 
                        <?php echo htmlspecialchars($current_user_name); ?>
                        <?php if (!empty($staff_name)): ?>
                            <small>(<?php echo htmlspecialchars($staff_name); ?>)</small>
                        <?php endif; ?>
                        <span class="role-badge" style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 5px;">
                            <?php echo ucfirst($current_user_role); ?>
                        </span>
                    </span>
                    
                    <!-- Change Password Link -->
                    <a href="<?php echo $base_url; ?>auth/change-password.php" class="change-password-btn" title="Change Password" style="color: white; text-decoration: none; padding: 6px 12px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: background 0.3s; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fas fa-key" aria-hidden="true"></i>
                    </a>
                    
                    <!-- Logout Link -->
                    <a href="<?php echo $base_url; ?>auth/logout.php" class="logout-btn" aria-label="Logout">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- Quick Info Bar (for logged in users) -->
    <?php if (isLoggedIn()): ?>
    <div style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0; padding: 5px 0;">
        <div class="nav-container" style="justify-content: flex-end; gap: 20px;">
            <small>
                <i class="fas fa-clock"></i> 
                Login: <?php echo isset($_SESSION['login_time']) ? date('H:i', $_SESSION['login_time']) : ''; ?>
            </small>
            <?php if ($staff_id > 0): ?>
            <small>
                <i class="fas fa-id-badge"></i> 
                Staff ID: #<?php echo $staff_id; ?>
            </small>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content Area -->
    <main class="main-content" id="main-content">