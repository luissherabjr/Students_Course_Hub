<?php
// Include database configuration for database connection
require_once '../includes/config.php';

// Set page title for the browser tab
$pageTitle = "Modules - Student Course Hub";

// Query to fetch all active modules with their leaders' information
$query = "SELECT m.*, s.Name as leader_name, s.Title as leader_title, s.Photo as leader_photo
          FROM Modules m
          LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
          WHERE m.Status = 'active'
          ORDER BY m.ModuleName";
$result = mysqli_query($conn, $query);
$modules = [];
// Store results in array for use in HTML
while ($row = mysqli_fetch_assoc($result)) {
    $modules[] = $row;
}

// Include the header template
include 'header.php';
?>

<!-- Page Banner Section - Displays page title and description -->
<section class="page-banner small-banner">
    <div class="container">
        <h1>All Modules</h1>
        <p>Explore our modules and see what you'll learn</p>
    </div>
</section>

<!-- Modules Listing Section - Displays all active modules in a card grid -->
<section class="section">
    <div class="container">
        <div class="card-grid">
            <?php foreach ($modules as $module): ?>
                <div class="card">
                    <!-- Display module image if available -->
                    <?php if (!empty($module['Image'])): ?>
                        <div class="module-image">
                            <img src="<?php echo htmlspecialchars($module['Image']); ?>" alt="<?php echo htmlspecialchars($module['ImageAlt'] ?? $module['ModuleName']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Module Name -->
                    <h3><?php echo htmlspecialchars($module['ModuleName']); ?></h3>
                    
                    <!-- Module Description -->
                    <p><?php echo htmlspecialchars($module['Description']); ?></p>
                    
                    <!-- Module Leader Information - Displays photo and name -->
                    <div class="leader-info">
                        <!-- Module Leader Photo or Placeholder -->
                        <?php if (!empty($module['leader_photo'])): ?>
                            <img src="<?php echo htmlspecialchars($module['leader_photo']); ?>" alt="<?php echo htmlspecialchars($module['leader_name']); ?>" class="leader-photo">
                        <?php else: ?>
                            <div class="leader-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <!-- Module Leader Name -->
                        <span>Module Leader: <strong><?php echo htmlspecialchars($module['leader_name'] ?? 'Not Assigned'); ?></strong></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
