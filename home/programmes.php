<?php
require_once '../includes/config.php';
$pageTitle = "Programmes - Student Course Hub";

// Get all published programmes
$query = "SELECT p.*, l.LevelName, s.Name as leader_name
          FROM Programmes p
          JOIN Levels l ON p.LevelID = l.LevelID
          LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
          WHERE p.Status = 'published'
          ORDER BY p.ProgrammeName";
$result = mysqli_query($conn, $query);
$programmes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $programmes[] = $row;
}

include 'header.php';
?>

<section class="page-banner small-banner">
    <div class="container">
        <h1>Our Programmes</h1>
        <p>Explore our undergraduate and postgraduate programmes</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="card-grid">
            <?php foreach ($programmes as $programme): ?>
                <div class="card">
                    <?php if (!empty($programme['Image'])): ?>
                        <div class="programme-image">
                            <img src="<?php echo htmlspecialchars($programme['Image']); ?>" alt="<?php echo htmlspecialchars($programme['ImageAlt'] ?? $programme['ProgrammeName']); ?>">
                        </div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h3>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($programme['LevelName']); ?></p>
                    <p><strong>Leader:</strong> <?php echo htmlspecialchars($programme['leader_name'] ?? 'Not Assigned'); ?></p>
                    <p><?php echo htmlspecialchars(substr($programme['Description'], 0, 100)) . '...'; ?></p>
                    <a href="programme_details.php?id=<?php echo $programme['ProgrammeID']; ?>">View Details →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
