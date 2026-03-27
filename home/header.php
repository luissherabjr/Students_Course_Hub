<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Student Course Hub'; ?></title>
    <!-- Main stylesheet with cache-busting parameter -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Site Header - Contains logo and main navigation menu -->
<header class="site-header">
    <!-- Logo Link - Redirects to homepage -->
    <a href="index.php" class="logo-link">
        <div class="logo">Student Course Hub</div>
    </a>

    <!-- Navigation Menu - Links to main pages -->
    <nav class="nav-menu">
        <!-- Public Navigation Links - Always visible -->
        <a href="index.php">Home</a>
        <a href="programmes.php">Programmes</a>
        <a href="modules.php">Modules</a>
        <a href="staff.php">Staff</a>
        
        <!-- Login Link - Only visible when user is NOT logged in -->
        <?php if (!isLoggedIn()): ?>
            <a href="<?php echo BASE_PATH; ?>auth/login.php">Log in</a>
        <?php endif; ?>
        
        <!-- Dashboard & Logout Links - Only visible when user IS logged in -->
        <?php if (isLoggedIn()): ?>
            <!-- Admin Dashboard Link - Only visible to admin users -->
            <?php if (isAdmin()): ?>
                <a href="<?php echo BASE_PATH; ?>admin/dashboard.php">Admin Dashboard</a>
            <!-- Staff Dashboard Link - Only visible to staff users -->
            <?php elseif (isStaff()): ?>
                <a href="<?php echo BASE_PATH; ?>staff/dashboard.php">Staff Dashboard</a>
            <?php endif; ?>
            <!-- Logout Link - Visible to all logged-in users -->
            <a href="<?php echo BASE_PATH; ?>auth/logout.php">Logout</a>
        <?php endif; ?>
    </nav>
</header>
