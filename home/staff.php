<?php
// Include database configuration for database connection
require_once '../includes/config.php';

// Set page title for the browser tab
$pageTitle = "Staff Directory - Student Course Hub";

// Query to fetch all staff with module counts
$query = "SELECT s.*, 
                 (SELECT COUNT(*) FROM Modules WHERE ModuleLeaderID = s.StaffID) as module_count
          FROM Staff s
          ORDER BY s.Name";
$result = mysqli_query($conn, $query);
$staff = [];
// Store results in array for use in HTML
while ($row = mysqli_fetch_assoc($result)) {
    $staff[] = $row;
}

// Include the header template
include 'header.php';
?>

<!-- Page Banner Section - Displays page title and description -->
<section class="page-banner small-banner">
    <div class="container">
        <h1>Our Staff</h1>
        <p>Meet our academic staff and module leaders</p>
    </div>
</section>

<!-- Staff Listing Section - Displays all staff in a card grid -->
<section class="section">
    <div class="container">
        <div class="card-grid staff-grid">
            <?php foreach ($staff as $member): ?>
                <div class="card staff-card">
                    <!-- Staff Photo - Display if available -->
                    <div class="staff-photo">
                        <?php if (!empty($member['Photo'])): ?>
                            <img src="<?php echo htmlspecialchars($member['Photo']); ?>" alt="<?php echo htmlspecialchars($member['Name']); ?>">
                        <?php else: ?>
                            <div class="staff-placeholder">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Staff Name -->
                    <h3><?php echo htmlspecialchars($member['Name']); ?></h3>
                    
                    <!-- Staff Title/Position -->
                    <p class="staff-title"><?php echo htmlspecialchars($member['Title'] ?? 'Faculty Member'); ?></p>
                    
                    <!-- Staff Department -->
                    <p class="staff-dept"><?php echo htmlspecialchars($member['Department'] ?? 'Computing'); ?></p>
                    
                    <!-- Staff Stats Badge - Shows number of modules they lead -->
                    <div class="staff-stats">
                        <span class="stats-badge">
                            <i class="fas fa-cube"></i> <?php echo $member['module_count']; ?> Modules
                        </span>
                    </div>
                    
                    <!-- Short Bio - Display if available -->
                    <?php if (!empty($member['Bio'])): ?>
                        <p class="staff-bio"><?php echo htmlspecialchars(substr($member['Bio'], 0, 100)) . '...'; ?></p>
                    <?php else: ?>
                        <p class="staff-bio">Expert in computing and technology education.</p>
                    <?php endif; ?>
                    
                    <!-- Contact Email Button -->
                    <?php if (!empty($member['Email'])): ?>
                        <a href="mailto:<?php echo htmlspecialchars($member['Email']); ?>" class="staff-contact" title="Send Email">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
