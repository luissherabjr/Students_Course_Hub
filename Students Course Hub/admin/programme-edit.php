<?php
$pageTitle = "Edit Programme - Admin";
require_once '../includes/config.php';
include 'admin-header.php';

$isEdit = isset($_GET['id']);
$programmeId = $isEdit ? (int)$_GET['id'] : 0;
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <!-- Same sidebar -->
    </aside>
    
    <main class="admin-content">
        <h1><?php echo $isEdit ? 'Edit Programme' : 'Add New Programme'; ?></h1>
        
        <form method="POST" action="save-programme.php" class="admin-form" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-section">
                    <h2>Basic Information</h2>
                    
                    <div class="form-group">
                        <label for="title">Programme Title *</label>
                        <input type="text" id="title" name="title" required class="form-control" 
                               value="<?php echo $isEdit ? 'BSc Computer Science' : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="code">Programme Code (UCAS) *</label>
                        <input type="text" id="code" name="code" required class="form-control"
                               value="<?php echo $isEdit ? 'G400' : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="level">Level *</label>
                        <select id="level" name="level" required class="form-control">
                            <option value="undergraduate" <?php echo $isEdit ? 'selected' : ''; ?>>Undergraduate</option>
                            <option value="postgraduate">Postgraduate</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Duration *</label>
                        <input type="text" id="duration" name="duration" required class="form-control"
                               value="<?php echo $isEdit ? '3 years' : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="mode">Study Mode *</label>
                        <select id="mode" name="mode" required class="form-control">
                            <option value="full-time" <?php echo $isEdit ? 'selected' : ''; ?>>Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required class="form-control" rows="5">A comprehensive programme covering programming, algorithms, and software engineering principles.</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="overview">Overview / Key Features</label>
                        <textarea id="overview" name="overview" class="form-control" rows="5">This degree programme offers a comprehensive education in computer science, combining theoretical knowledge with practical skills.</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="careers">Career Opportunities</label>
                        <textarea id="careers" name="careers" class="form-control" rows="3">Graduates can pursue careers as software developers, systems analysts, IT consultants</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Programme Image</label>
                        <input type="file" id="image" name="image" accept="image/*" class="form-control">
                        <?php if ($isEdit): ?>
                            <small>Current: computer-science.jpg</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Entry Requirements</h2>
                    
                    <div class="form-group">
                        <label for="a_levels">A-Level Requirements</label>
                        <input type="text" id="a_levels" name="a_levels" class="form-control"
                               value="ABB including Mathematics or Computing">
                    </div>
                    
                    <div class="form-group">
                        <label for="ib">International Baccalaureate</label>
                        <input type="text" id="ib" name="ib" class="form-control"
                               value="32 points including Higher Level Mathematics">
                    </div>
                    
                    <div class="form-group">
                        <label for="ielts">English Language Requirements</label>
                        <input type="text" id="ielts" name="ielts" class="form-control"
                               value="IELTS 6.5 overall with no less than 6.0 in each component">
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Publication Status</h2>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="published" value="1" <?php echo $isEdit ? 'checked' : ''; ?>>
                            Publish immediately (visible to students)
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="publish_date">Schedule Publication (optional)</label>
                        <input type="date" id="publish_date" name="publish_date" class="form-control">
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">Save Programme</button>
                <a href="programmes.php" class="btn btn-secondary">Cancel</a>
                
                <?php if ($isEdit): ?>
                    <button type="button" class="btn btn-danger" onclick="deleteProgramme(<?php echo $programmeId; ?>)">Delete Programme</button>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="programme_image">Programme Image</label>
                <input type="file" id="programme_image" name="programme_image" accept="image/*" class="form-control">
                <?php if ($isEdit ($programmeId)): ?>
                    <div class="current-image">
                        <img src="../assets/images/programmes/<?php echo $programmeImage; ?>" alt="Current programme image" style="max-width: 200px;">
                        <label>
                            <input type="checkbox" name="remove_image"> Remove current image
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </main>
</div>

<?php include 'admin-footer.php'; ?>