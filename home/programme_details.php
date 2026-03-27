<?php
// Include database configuration for database connection
require_once '../includes/config.php';

// Set page title for the browser tab
$pageTitle = "Programme Details - Student Course Hub";

// Get programme ID from URL parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query to fetch programme details along with level and leader information and JOINs three to get complete programme information
$query = "SELECT p.*, l.LevelName, s.Name as leader_name, s.Title as leader_title, s.Photo as leader_photo
          FROM Programmes p
          JOIN Levels l ON p.LevelID = l.LevelID
          LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
          WHERE p.ProgrammeID = $id AND p.Status = 'published'";
$result = mysqli_query($conn, $query);
$programme = mysqli_fetch_assoc($result);

// Redirect if programme not found
if (!$programme) {
    die("Programme not found.");
}

// Query to fetch all modules for this programme grouped by year
$modules_query = "SELECT pm.Year, m.ModuleID, m.ModuleName, m.Description, m.Image, m.ImageAlt,
                         s.Name as leader_name, s.Title as leader_title
                  FROM ProgrammeModules pm
                  JOIN Modules m ON pm.ModuleID = m.ModuleID
                  LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                  WHERE pm.ProgrammeID = $id AND m.Status = 'active'
                  ORDER BY pm.Year, m.ModuleName";
$modules_result = mysqli_query($conn, $modules_query);

// Organize modules by year for easy display
$modules_by_year = [];
while ($module = mysqli_fetch_assoc($modules_result)) {
    $modules_by_year[$module['Year']][] = $module;
}

// Include the header template
include 'header.php';
?>

<!-- Page Banner Section - Displays programme name and level -->
<section class="page-banner small-banner">
    <div class="container">
        <h1><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h1>
        <p><?php echo htmlspecialchars($programme['LevelName']); ?></p>
    </div>
</section>

<!-- Programme Details Section -->
<section class="section">
    <div class="container">
        <!-- Programme Information Box -->
        <div class="detail-box">
            <!-- Programme Image - Display if available -->
            <?php if (!empty($programme['Image'])): ?>
                <div class="programme-detail-image">
                    <img src="<?php echo htmlspecialchars($programme['Image']); ?>" alt="<?php echo htmlspecialchars($programme['ImageAlt'] ?? $programme['ProgrammeName']); ?>">
                </div>
            <?php endif; ?>
            
            <!-- Programme Leader and Description -->
            <div class="programme-info">
                <p><strong>Programme Leader:</strong> 
                    <?php if (!empty($programme['leader_name'])): ?>
                        <?php echo htmlspecialchars($programme['leader_name']); ?>
                        <?php if (!empty($programme['leader_title'])): ?>
                            (<?php echo htmlspecialchars($programme['leader_title']); ?>)
                        <?php endif; ?>
                    <?php else: ?>
                        Not Assigned
                    <?php endif; ?>
                </p>
                <p><strong>Description:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($programme['Description'])); ?></p>
            </div>
        </div>

        <!-- Modules Section - Display modules grouped by year -->
        <h2>Modules by Year</h2>
        
        <?php if (count($modules_by_year) > 0): ?>
            <!-- Loop through each year and display modules -->
            <?php foreach ($modules_by_year as $year => $modules): ?>
                <h3>Year <?php echo $year; ?></h3>
                <div class="table-responsive">
                     <table>
                        <thead>
                             <tr>
                                <th>Module Name</th>
                                <th>Module Leader</th>
                                <th>Description</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                 <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($module['ModuleName']); ?></strong>
                                        <!-- Module Thumbnail Image - Display if available -->
                                        <?php if (!empty($module['Image'])): ?>
                                            <div class="module-thumb">
                                                <img src="<?php echo htmlspecialchars($module['Image']); ?>" alt="<?php echo htmlspecialchars($module['ImageAlt'] ?? $module['ModuleName']); ?>">
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($module['leader_name'])): ?>
                                            <?php echo htmlspecialchars($module['leader_name']); ?>
                                            <?php if (!empty($module['leader_title'])): ?>
                                                <br><small><?php echo htmlspecialchars($module['leader_title']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Not Assigned
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($module['Description']); ?></td>
                                 </tr>
                            <?php endforeach; ?>
                        </tbody>
                     </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No modules found for this programme.</p>
        <?php endif; ?>
        
        <!-- Call to Action Buttons - Register interest or go back -->
        <div class="register-section">

            <a href="programmes.php" class="btn btn-secondary">Back to Programmes</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
