<?php
/**
 * Edit Programme - Add or Edit Programmes with Image Upload
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Edit Programme';

// Get programme ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If adding new programme (no ID)
if ($id == 0) {
    $programme = [
        'ProgrammeID' => 0,
        'ProgrammeName' => '',
        'LevelID' => '',
        'ProgrammeLeaderID' => '',
        'Description' => '',
        'Image' => '',
        'ImageAlt' => '',
        'Status' => 'draft'
    ];
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Programmes WHERE ProgrammeID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = 'Programme not found';
        header('Location: ' . BASE_PATH . 'admin/programmes.php');
        exit();
    }
    $programme = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

include '../includes/header.php';

// Get levels and staff for dropdowns
$levels = mysqli_query($conn, "SELECT * FROM Levels ORDER BY LevelName");
$staff = mysqli_query($conn, "SELECT * FROM Staff ORDER BY Name");
$csrf_token = generateCSRFToken();
?>

<div class="form-container" style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 30px;">
        <i class="fas fa-<?php echo $id == 0 ? 'plus' : 'edit'; ?>-circle"></i> 
        <?php echo $id == 0 ? 'Add New' : 'Edit'; ?> Programme
    </h1>
    
    <form method="POST" action="<?php echo BASE_PATH; ?>admin/process-programme.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <?php if ($id > 0): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        <input type="hidden" name="action" value="<?php echo $id == 0 ? 'add' : 'edit'; ?>">
        
        <!-- Current Image Display -->
        <?php if (!empty($programme['Image'])): ?>
            <div class="form-group" style="text-align: center;">
                <label>Current Image</label>
                <div style="margin: 10px 0;">
                    <img src="<?php echo htmlspecialchars($programme['Image']); ?>" 
                         alt="<?php echo htmlspecialchars($programme['ImageAlt'] ?? 'Programme image'); ?>"
                         style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0;">
                </div>
            </div>
        <?php endif; ?>
        
        <!-- File Upload Option -->
        <div class="form-group">
            <label for="programme_image">Upload Programme Image from Computer</label>
            <input type="file" id="programme_image" name="programme_image" accept="image/jpeg,image/png,image/gif,image/webp">
            <small>Upload an image (JPG, PNG, GIF, WebP). Max size 2MB.</small>
        </div>
        
        <!-- OR URL Option -->
        <div class="form-group">
            <label for="image">Or Enter Image URL</label>
            <input type="url" id="image" name="image" 
                   value="<?php echo htmlspecialchars($programme['Image'] ?? ''); ?>"
                   placeholder="https://example.com/image.jpg" maxlength="255">
            <small>If you uploaded a file above, leave this empty.</small>
        </div>
        
        <div class="form-group">
            <label for="imageAlt">Image Description (for accessibility)</label>
            <input type="text" id="imageAlt" name="imageAlt" 
                   value="<?php echo htmlspecialchars($programme['ImageAlt'] ?? ''); ?>"
                   placeholder="Describe the image for screen readers" maxlength="255">
            <small>Required if image is provided</small>
        </div>
        
        <div class="form-group">
            <label for="programmeName">Programme Name <span style="color: red;">*</span></label>
            <input type="text" id="programmeName" name="programmeName" required 
                   value="<?php echo htmlspecialchars($programme['ProgrammeName']); ?>"
                   placeholder="e.g., BSc Computer Science" maxlength="150">
        </div>
        
        <div class="form-group">
            <label for="levelId">Level <span style="color: red;">*</span></label>
            <select id="levelId" name="levelId" required>
                <option value="">Select Level</option>
                <?php while ($level = mysqli_fetch_assoc($levels)): ?>
                    <option value="<?php echo $level['LevelID']; ?>" 
                        <?php echo $level['LevelID'] == $programme['LevelID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($level['LevelName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="programmeLeader">Programme Leader</label>
            <select id="programmeLeader" name="programmeLeader">
                <option value="">Select Programme Leader</option>
                <?php 
                mysqli_data_seek($staff, 0);
                while ($s = mysqli_fetch_assoc($staff)): 
                ?>
                    <option value="<?php echo $s['StaffID']; ?>" 
                        <?php echo $s['StaffID'] == $programme['ProgrammeLeaderID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['Name']); ?>
                        <?php if (!empty($s['Title'])): ?>
                            (<?php echo htmlspecialchars($s['Title']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description">Description <span style="color: red;">*</span></label>
            <textarea id="description" name="description" required rows="6" maxlength="1000"
                      placeholder="Enter programme description"><?php echo htmlspecialchars($programme['Description']); ?></textarea>
            <small>Max 1000 characters</small>
        </div>
        
        <div class="form-group">
            <label for="status">Status <span style="color: red;">*</span></label>
            <select id="status" name="status" required>
                <option value="draft" <?php echo $programme['Status'] == 'draft' ? 'selected' : ''; ?>>Draft (hidden from students)</option>
                <option value="published" <?php echo $programme['Status'] == 'published' ? 'selected' : ''; ?>>Published (visible to students)</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> <?php echo $id == 0 ? 'Save' : 'Update'; ?> Programme
            </button>
            <a href="<?php echo BASE_PATH; ?>admin/programmes.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>