<?php
/**
 * Edit Module - Add or Edit Modules with Image Upload
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Edit Module';

// Get module ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If adding new module (no ID)
if ($id == 0) {
    $module = [
        'ModuleID' => 0,
        'ModuleName' => '',
        'ModuleLeaderID' => '',
        'Description' => '',
        'Image' => '',
        'ImageAlt' => '',
        'Status' => 'active'
    ];
} else {
    // Get existing module
    $stmt = mysqli_prepare($conn, "SELECT m.*, s.Name as leader_name, s.Title as leader_title 
                                   FROM Modules m
                                   LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                                   WHERE m.ModuleID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = 'Module not found';
        header('Location: ' . BASE_PATH . 'admin/modules.php');
        exit();
    }
    $module = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

include '../includes/header.php';

// Get all staff for dropdown
$staff_query = "SELECT * FROM Staff ORDER BY Name";
$staff_result = mysqli_query($conn, $staff_query);

// Get programmes this module is used in (if editing)
$programmes_result = null;
if ($id > 0) {
    $prog_query = "SELECT p.ProgrammeName, l.LevelName, pm.Year
                   FROM ProgrammeModules pm
                   JOIN Programmes p ON pm.ProgrammeID = p.ProgrammeID
                   JOIN Levels l ON p.LevelID = l.LevelID
                   WHERE pm.ModuleID = $id
                   ORDER BY p.ProgrammeName";
    $programmes_result = mysqli_query($conn, $prog_query);
}

$csrf_token = generateCSRFToken();
?>

<div class="form-container" style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 30px;">
        <i class="fas fa-<?php echo $id == 0 ? 'plus' : 'edit'; ?>-circle"></i> 
        <?php echo $id == 0 ? 'Add New' : 'Edit'; ?> Module
    </h1>
    
    <form method="POST" action="<?php echo BASE_PATH; ?>admin/process-module.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <?php if ($id > 0): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        <input type="hidden" name="action" value="<?php echo $id == 0 ? 'add' : 'edit'; ?>">
        
        <!-- Current Image Display -->
        <?php if (!empty($module['Image'])): ?>
            <div class="form-group" style="text-align: center;">
                <label>Current Image</label>
                <div style="margin: 10px 0;">
                    <img src="<?php echo htmlspecialchars($module['Image']); ?>" 
                         alt="<?php echo htmlspecialchars($module['ImageAlt'] ?? 'Module image'); ?>"
                         style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0;">
                </div>
            </div>
        <?php endif; ?>
        
        <!-- File Upload Option -->
        <div class="form-group">
            <label for="module_image">Upload Module Image from Computer</label>
            <input type="file" id="module_image" name="module_image" accept="image/jpeg,image/png,image/gif,image/webp">
            <small>Upload an image (JPG, PNG, GIF, WebP). Max size 2MB.</small>
        </div>
        
        <!-- OR URL Option -->
        <div class="form-group">
            <label for="image">Or Enter Image URL</label>
            <input type="url" id="image" name="image" 
                   value="<?php echo htmlspecialchars($module['Image'] ?? ''); ?>"
                   placeholder="https://example.com/image.jpg" maxlength="255">
            <small>If you uploaded a file above, leave this empty.</small>
        </div>
        
        <div class="form-group">
            <label for="imageAlt">Image Description (for accessibility)</label>
            <input type="text" id="imageAlt" name="imageAlt" 
                   value="<?php echo htmlspecialchars($module['ImageAlt'] ?? ''); ?>"
                   placeholder="Describe the image for screen readers" maxlength="255">
            <small>Required if image is provided</small>
        </div>
        
        <div class="form-group">
            <label for="moduleName">Module Name <span style="color: red;">*</span></label>
            <input type="text" id="moduleName" name="moduleName" required 
                   value="<?php echo htmlspecialchars($module['ModuleName']); ?>"
                   placeholder="e.g., Introduction to Programming" maxlength="150">
        </div>
        
        <div class="form-group">
            <label for="description">Description <span style="color: red;">*</span></label>
            <textarea id="description" name="description" required rows="6" maxlength="1000"
                      placeholder="Enter module description"><?php echo htmlspecialchars($module['Description']); ?></textarea>
            <small>Max 1000 characters</small>
        </div>
        
        <div class="form-group">
            <label for="moduleLeader">Module Leader</label>
            <select id="moduleLeader" name="moduleLeader">
                <option value="">-- Select Module Leader --</option>
                <?php 
                mysqli_data_seek($staff_result, 0);
                while ($staff = mysqli_fetch_assoc($staff_result)): 
                ?>
                    <option value="<?php echo $staff['StaffID']; ?>" 
                        <?php echo $staff['StaffID'] == $module['ModuleLeaderID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($staff['Name']); ?>
                        <?php if (!empty($staff['Title'])): ?>
                            (<?php echo htmlspecialchars($staff['Title']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="status">Status <span style="color: red;">*</span></label>
            <select id="status" name="status" required>
                <option value="active" <?php echo $module['Status'] == 'active' ? 'selected' : ''; ?>>Active (visible to students)</option>
                <option value="inactive" <?php echo $module['Status'] == 'inactive' ? 'selected' : ''; ?>>Inactive (hidden from students)</option>
            </select>
        </div>
        
        <?php if ($id > 0 && $programmes_result && mysqli_num_rows($programmes_result) > 0): ?>
            <div class="form-group">
                <label>This module is used in:</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <?php while ($prog = mysqli_fetch_assoc($programmes_result)): ?>
                        <div style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 5px 12px; border-radius: 20px; margin: 5px; font-size: 0.9rem;">
                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($prog['ProgrammeName']); ?>
                            (Year <?php echo $prog['Year']; ?>, <?php echo $prog['LevelName']; ?>)
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> <?php echo $id == 0 ? 'Save Module' : 'Update Module'; ?>
            </button>
            <a href="<?php echo BASE_PATH; ?>admin/modules.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>