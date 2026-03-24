<?php
/**
 * Edit Staff - Add or Edit Staff Profiles with Image Upload
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Edit Staff';

// Get staff ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If adding new staff (no ID)
if ($id == 0) {
    $staff = [
        'StaffID' => 0,
        'Name' => '',
        'Title' => '',
        'Department' => '',
        'Email' => '',
        'Bio' => '',
        'Photo' => ''
    ];
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Staff WHERE StaffID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = 'Staff member not found';
        header('Location: ' . BASE_PATH . 'admin/staff.php');
        exit();
    }
    $staff = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

include '../includes/header.php';
$csrf_token = generateCSRFToken();
?>

<div class="form-container" style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 30px;">
        <i class="fas fa-<?php echo $id == 0 ? 'plus' : 'edit'; ?>-circle"></i> 
        <?php echo $id == 0 ? 'Add New' : 'Edit'; ?> Staff Member
    </h1>
    
    <form method="POST" action="<?php echo BASE_PATH; ?>admin/process-staff.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <?php if ($id > 0): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        <input type="hidden" name="action" value="<?php echo $id == 0 ? 'add' : 'edit'; ?>">
        
        <!-- Current Photo Display -->
        <?php if (!empty($staff['Photo'])): ?>
            <div class="form-group" style="text-align: center;">
                <label>Current Photo</label>
                <div style="margin: 10px 0;">
                    <img src="<?php echo htmlspecialchars($staff['Photo']); ?>" 
                         alt="<?php echo htmlspecialchars($staff['Name']); ?>"
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #667eea;">
                </div>
            </div>
        <?php endif; ?>
        
        <!-- File Upload Option -->
        <div class="form-group">
            <label for="profile_photo">Upload Photo from Computer</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp">
            <small>Upload a photo (JPG, PNG, GIF, WebP). Max size 2MB.</small>
        </div>
        
        <!-- OR URL Option -->
        <div class="form-group">
            <label for="photo_url">Or Enter Image URL</label>
            <input type="url" id="photo_url" name="photo_url" 
                   value="<?php echo htmlspecialchars($staff['Photo'] ?? ''); ?>"
                   placeholder="https://example.com/photo.jpg" maxlength="255">
            <small>If you uploaded a file above, leave this empty.</small>
        </div>
        
        <div class="form-group">
            <label for="name">Full Name <span style="color: red;">*</span></label>
            <input type="text" id="name" name="name" required 
                   value="<?php echo htmlspecialchars($staff['Name']); ?>"
                   placeholder="e.g., Dr. John Smith" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="title">Title/Position</label>
            <input type="text" id="title" name="title" 
                   value="<?php echo htmlspecialchars($staff['Title'] ?? ''); ?>"
                   placeholder="e.g., Professor of Computer Science" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" 
                   value="<?php echo htmlspecialchars($staff['Department'] ?? ''); ?>"
                   placeholder="e.g., Computing" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($staff['Email'] ?? ''); ?>"
                   placeholder="staff@university.ac.uk" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="bio">Biography</label>
            <textarea id="bio" name="bio" rows="6" 
                      placeholder="Enter staff biography..."><?php echo htmlspecialchars($staff['Bio'] ?? ''); ?></textarea>
        </div>
        
        <?php if ($id > 0): ?>
            <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <p><strong><i class="fas fa-info-circle"></i> Note:</strong></p>
                <p class="small">This staff member still needs a <strong>User Account</strong> to login.</p>
                <p class="small">Go to <strong>Users Management</strong> → <strong>Add New User</strong> and link to this staff record.</p>
                <?php
                $check_user = mysqli_prepare($conn, "SELECT Username FROM AdminUsers WHERE StaffID = ?");
                mysqli_stmt_bind_param($check_user, "i", $id);
                mysqli_stmt_execute($check_user);
                $user_result = mysqli_stmt_get_result($check_user);
                if (mysqli_num_rows($user_result) > 0) {
                    $user = mysqli_fetch_assoc($user_result);
                    echo '<p class="small" style="color: green; margin-top: 10px;">✅ This staff has a user account: <strong>' . htmlspecialchars($user['Username']) . '</strong></p>';
                } else {
                    echo '<p class="small" style="color: orange; margin-top: 10px;">⚠️ This staff does NOT have a user account yet.</p>';
                    echo '<a href="add-user.php" class="add-btn" style="margin-top: 10px; display: inline-block;">Create User Account →</a>';
                }
                mysqli_stmt_close($check_user);
                ?>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> <?php echo $id == 0 ? 'Save Staff' : 'Update Staff'; ?>
            </button>
            <a href="staff.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.small {
    font-size: 0.85rem;
    margin-top: 5px;
}
</style>

<?php include '../includes/footer.php'; ?>