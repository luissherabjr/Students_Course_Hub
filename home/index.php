<?php
// Include database configuration for database connection
require_once '../includes/config.php';

// Set page title for the browser tab
$pageTitle = "Home - Student Course Hub";

// Query to fetch 3 featured published programmes for homepage with their images
$query = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.Image, p.ImageAlt, l.LevelName 
          FROM Programmes p
          JOIN Levels l ON p.LevelID = l.LevelID
          WHERE p.Status = 'published'
          ORDER BY p.CreatedAt DESC
          LIMIT 3";
$result = mysqli_query($conn, $query);
$programmes = [];
// Store results in array for use in HTML
while ($row = mysqli_fetch_assoc($result)) {
    $programmes[] = $row;
}

// Include the header template
include 'header.php';
?>


<!-- Hero Section - Main banner with call-to-action -->
<section class="hero">
    <div class="hero-content">
        <h1>Find Your Future in Computing</h1>
        <p>Explore our undergraduate and postgraduate programmes, discover modules, meet our staff, and register your interest today.</p>

        <!-- Search Bar - Allows users to search programmes, staff, and modules -->
        <form action="search.php" method="GET" class="search-bar hero-search">
            <input type="text" name="q" placeholder="Search programmes, staff, modules..." required>
            <button type="submit">Search</button>
        </form>

        <!-- Hero Buttons - Quick navigation to programmes page -->
        <div class="hero-buttons">
            <a href="programmes.php" class="btn">Explore Programmes</a>
        </div>
    </div>
</section>

<!-- Featured Programmes Section - Displays 3 latest published programmes -->
<section class="section">
    <div class="container">
        <h2>Some Of Our Featured Programmes</h2>
        <div class="card-grid">
            <?php foreach ($programmes as $programme): ?>
                <div class="card">
                    <!-- Display programme image if available -->
                    <?php if (!empty($programme['Image'])): ?>
                        <div class="programme-image">
                            <img src="<?php echo htmlspecialchars($programme['Image']); ?>" alt="<?php echo htmlspecialchars($programme['ImageAlt'] ?? $programme['ProgrammeName']); ?>">
                        </div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h3>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($programme['LevelName']); ?></p>
                    <p><?php echo htmlspecialchars(substr($programme['Description'], 0, 100)) . '...'; ?></p>
                    <a href="programme_details.php?id=<?php echo $programme['ProgrammeID']; ?>">View Details →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Study With Us Section - Highlights key benefits of studying at the university -->
<section class="section light-section">
    <div class="container">
        <h2>Why Study With Us?</h2>
        <div class="info-grid">
            <div class="info-box">
                <h3>Undergraduate and Postgraduate Options</h3>
                <p>Choose from a wide range of modern computing programmes across different study levels.</p>
            </div>
            <div class="info-box">
                <h3>Industry-Relevant Modules</h3>
                <p>Study programming, AI, cyber security, data science, cloud computing, and more.</p>
            </div>
            <div class="info-box">
                <h3>Experienced Academic Staff</h3>
                <p>Learn from programme leaders and module leaders with specialist subject knowledge.</p>
            </div>
            <div class="info-box">
                <h3>Easy Registration</h3>
                <p>Register your interest in a programme quickly and receive future updates.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
