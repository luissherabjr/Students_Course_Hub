<?php
/**
 * Edit User - Modify existing user accounts
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Edit User';

// Get user ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: ' . BASE_PATH . 'admin/users.php');
    exit();
}

// Get user details
$query = "SELECT a.*, s.Name as staff_name, s.Title as staff_title, s.Department as staff_department
          FROM AdminUsers a
          LEFT JOIN Staff s ON a.StaffID = s.StaffID
          WHERE a.AdminID = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'User not found';
    header('Location: ' . BASE_PATH . 'admin/users.php');
    exit();
}

$user = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token';
        header('Location: ' . BASE_PATH . 'admin/users.php');
        exit();
    }
    
    $fullname = cleanInput($_POST['fullname']);
    $email = cleanInput($_POST['email']);
    $role = cleanInput($_POST['role']);
    $staff_id = !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : 'NULL';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($fullname) || empty($email)) {
        $_SESSION['error'] = 'Please fill all required fields';
    } else {
        // Update user
        $query = "UPDATE AdminUsers SET 
                  FullName = '$fullname',
                  Email = '$email',
                  Role = '$role',
                  StaffID = $staff_id,
                  IsActive = $is_active
                  WHERE AdminID = $id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'User updated successfully';
            logSecurityEvent('User updated: ' . $user['Username'] . ' (ID: ' . $id . ')');
            header('Location: ' . BASE_PATH . 'admin/users.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error updating user: ' . mysqli_error($conn);
        }
    }
}

include '../includes/header.php';

// Get staff list for dropdown
$staff_list = mysqli_query($conn, "SELECT StaffID, Name, Title FROM Staff ORDER BY Name");
?>

<div class="form-container" style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 30px;">
        <i class="fas fa-user-edit"></i> Edit User
    </h1>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong><i class="fas fa-user"></i> Username:</strong> <?php echo htmlspecialchars($user['Username']); ?></p>
        <p><strong><i class="fas fa-calendar"></i> Created:</strong> <?php echo date('d/m/Y H:i', strtotime($user['CreatedAt'])); ?></p>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="fullname">Full Name <span style="color: red;">*</span></label>
            <input type="text" id="fullname" name="fullname" required 
                   value="<?php echo htmlspecialchars($user['FullName']); ?>"
                   placeholder="e.g., John Doe" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($user['Email']); ?>"
                   placeholder="john.doe@university.ac.uk" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="role">Role <span style="color: red;">*</span></label>
            <select id="role" name="role" required>
                <option value="staff" <?php echo $user['Role'] == 'staff' ? 'selected' : ''; ?>>
                    Staff (View Only)
                </option>
                <option value="admin" <?php echo $user['Role'] == 'admin' ? 'selected' : ''; ?>>
                    Administrator (Full Access)
                </option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="staff_id">Link to Staff Record (Optional)</label>
            <select id="staff_id" name="staff_id">
                <option value="">-- Not linked --</option>
                <?php while ($staff = mysqli_fetch_assoc($staff_list)): ?>
                    <option value="<?php echo $staff['StaffID']; ?>" 
                        <?php echo $staff['StaffID'] == $user['StaffID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($staff['Name']); ?>
                        <?php if (!empty($staff['Title'])): ?>
                            (<?php echo htmlspecialchars($staff['Title']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small>Link this account to an existing staff member for profile information</small>
        </div>
        
        <div class="form-group">
            <label for="is_active">
                <input type="checkbox" id="is_active" name="is_active" value="1" 
                    <?php echo $user['IsActive'] ? 'checked' : ''; ?>>
                Active Account
            </label>
            <small>Inactive accounts cannot log in</small>
        </div>
        
        <div class="form-group">
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                <p><strong><i class="fas fa-key"></i> Password Management:</strong></p>
                <p class="small">To reset password, use the <strong>Reset Password</strong> button on the users list page.</p>
                <p class="small">Default password is: <code>password123</code></p>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Update User
            </button>
            <a href="users.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.small {
    font-size: 0.85rem;
    margin-top: 5px;
    color: #666;
}
code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
}
</style>

<?php include '../includes/footer.php'; ?>