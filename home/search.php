<?php
// Include database configuration for database connection
require_once '../includes/config.php';

// Set page title for the browser tab
$pageTitle = 'Search Results - Student Course Hub';

// Get search query from URL parameter and sanitize it
$query = trim($_GET['q'] ?? '');
$searchTerm = cleanInput($query);

// Initialize arrays to store search results
$programmeResults = [];
$moduleResults = [];

// Perform search only if query is not empty
if ($query !== '') {
    // Search Programmes - Find programmes matching name or description
    $prog_sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, l.LevelName, s.Name as LeaderName
                 FROM Programmes p
                 JOIN Levels l ON p.LevelID = l.LevelID
                 LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
                 WHERE p.Status = 'published' 
                 AND (p.ProgrammeName LIKE '%$searchTerm%' OR p.Description LIKE '%$searchTerm%')
                 ORDER BY p.ProgrammeName
                 LIMIT 20";
    $prog_result = mysqli_query($conn, $prog_sql);
    while ($row = mysqli_fetch_assoc($prog_result)) {
        $programmeResults[] = $row;
    }
    
    // Search Modules - Find modules matching name or description
    $mod_sql = "SELECT m.ModuleID, m.ModuleName, m.Description, s.Name as ModuleLeader
                FROM Modules m
                LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                WHERE m.Status = 'active'
                AND (m.ModuleName LIKE '%$searchTerm%' OR m.Description LIKE '%$searchTerm%')
                ORDER BY m.ModuleName
                LIMIT 20";
    $mod_result = mysqli_query($conn, $mod_sql);
    while ($row = mysqli_fetch_assoc($mod_result)) {
        $moduleResults[] = $row;
    }
}

// Include the header template
include 'header.php';
?>

<!-- Page Banner Section - Displays search query and results count -->
<section class="page-banner small-banner">
    <div class="container">
        <h1>Search Results</h1>
        <p>Results for: <strong><?php echo htmlspecialchars($query); ?></strong></p>
    </div>
</section>

<!-- Search Results Section -->
<section class="section">
    <div class="container">
        <!-- Search Bar - Allows users to refine their search -->
        <form action="search.php" method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Search programmes, modules..." value="<?php echo htmlspecialchars($query); ?>" required>
            <button type="submit">Search</button>
        </form>

        <!-- Programmes Results Section -->
        <h2>Programmes (<?php echo count($programmeResults); ?>)</h2>
        <?php if (!empty($programmeResults)): ?>
            <div class="card-grid">
                <?php foreach ($programmeResults as $programme): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h3>
                        <p><strong>Level:</strong> <?php echo htmlspecialchars($programme['LevelName']); ?></p>
                        <p><strong>Leader:</strong> <?php echo htmlspecialchars($programme['LeaderName'] ?? 'Not Assigned'); ?></p>
                        <p><?php echo htmlspecialchars(substr($programme['Description'], 0, 100)) . '...'; ?></p>
                        <a href="programme_details.php?id=<?php echo $programme['ProgrammeID']; ?>">View Details →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No programme results found.</p>
        <?php endif; ?>

        <!-- Modules Results Section -->
        <h2>Modules (<?php echo count($moduleResults); ?>)</h2>
        <?php if (!empty($moduleResults)): ?>
            <div class="card-grid">
                <?php foreach ($moduleResults as $module): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($module['ModuleName']); ?></h3>
                        <p><strong>Module Leader:</strong> <?php echo htmlspecialchars($module['ModuleLeader'] ?? 'Not Assigned'); ?></p>
                        <p><?php echo htmlspecialchars(substr($module['Description'], 0, 100)) . '...'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No module results found.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
