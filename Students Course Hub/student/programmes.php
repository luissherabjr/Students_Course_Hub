<?php
$pageTitle = "All Programmes - Student Course Hub";
require_once '../includes/config.php';
include '../includes/header.php';

// Get filter parameters
$level = isset($_GET['level']) ? $_GET['level'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<div class="page-header">
    <div class="container">
        <h1>Our Programmes</h1>
        <?php if ($search): ?>
            <p>Search results for: "<?php echo htmlspecialchars($search); ?>"</p>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="programmes-layout">
        <!-- Sidebar filters -->
        <aside class="filters-sidebar" aria-label="Programme filters">
            <h2>Filter Programmes</h2>
            <form action="../programmes.php" method="GET" class="filter-form">
                <div class="filter-group">
                    <h3>Level</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" name="level[]" value="undergraduate" 
                               <?php echo (in_array('undergraduate', (array)$level)) ? 'checked' : ''; ?>>
                        Undergraduate
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="level[]" value="postgraduate"
                               <?php echo (in_array('postgraduate', (array)$level)) ? 'checked' : ''; ?>>
                        Postgraduate
                    </label>
                </div>
                
                <div class="filter-group">
                    <h3>Study Mode</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" name="mode[]" value="full-time"> Full-time
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="mode[]" value="part-time"> Part-time
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="../programmes.php" class="btn btn-secondary">Clear Filters</a>
            </form>
        </aside>
        
        <!-- Main content -->
        <div class="programmes-list">
            <!-- This will be populated from database later -->
            <div class="programme-item">
                <div class="programme-info">
                    <h2><a href="../student/programme-details.php?id=1">BSc Computer Science</a></h2>
                    <span class="programme-badge undergraduate">Undergraduate</span>
                    <p class="programme-summary">A comprehensive programme covering programming, algorithms, and software engineering principles.</p>
                    <div class="programme-details">
                        <span>📅 3 years</span>
                        <span>⏰ Full-time</span>
                        <span>👥 20 modules</span>
                    </div>
                </div>
                <a href="../student/programme-details.php?id=1" class="btn btn-outline">View Details</a>
            </div>
            
            <div class="programme-item">
                <div class="programme-info">
                    <h2><a href="../student/programme-details.php?id=2">BSc Cyber Security</a></h2>
                    <span class="programme-badge undergraduate">Undergraduate</span>
                    <p class="programme-summary">Focus on network security, ethical hacking, and digital forensics.</p>
                    <div class="programme-details">
                        <span>📅 3 years</span>
                        <span>⏰ Full-time</span>
                        <span>👥 18 modules</span>
                    </div>
                </div>
                <a href="../student/programme-details.php?id=2" class="btn btn-outline">View Details</a>
            </div>
            
            <div class="programme-item">
                <div class="programme-info">
                    <h2><a href="../student/programme-details.php?id=3">MSc Data Science</a></h2>
                    <span class="programme-badge postgraduate">Postgraduate</span>
                    <p class="programme-summary">Advanced study in machine learning, big data analytics, and statistical methods.</p>
                    <div class="programme-details">
                        <span>📅 1 year</span>
                        <span>⏰ Full-time</span>
                        <span>👥 8 modules</span>
                    </div>
                </div>
                <a href="../student/programme-details.php?id=3" class="btn btn-outline">View Details</a>
            </div>
            
            <?php if (empty($programmes)): ?>
                <p class="no-results">No programmes found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>