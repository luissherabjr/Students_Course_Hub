<?php
/**
 * Add New User - Create login accounts for admin and staff
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Add New User';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token';
        header('Location: ' . BASE_PATH . 'admin/users.php');
        exit();
    }
    
    $username = cleanInput($_POST['username']);
    $fullname = cleanInput($_POST['fullname']);
    $email = cleanInput($_POST['email']);
    $role = cleanInput($_POST['role']);
    $staff_id = !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : null;
    
    // Hash the default password
    $hashed_password = hashPassword('password123');
    
    // ============================================
    // DUPLICATE USERNAME CHECK
    // ============================================
    
    // Check if username already exists
    $check_stmt = mysqli_prepare($conn, "SELECT Username FROM AdminUsers WHERE LOWER(Username) = LOWER(?)");
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = 'Username "' . htmlspecialchars($username) . '" already exists.';
    }
    // Check if email already exists
    else {
        $email_stmt = mysqli_prepare($conn, "SELECT Email FROM AdminUsers WHERE Email = ?");
        mysqli_stmt_bind_param($email_stmt, "s", $email);
        mysqli_stmt_execute($email_stmt);
        $email_result = mysqli_stmt_get_result($email_stmt);
        
        if (mysqli_num_rows($email_result) > 0) {
            $_SESSION['error'] = 'Email address already registered.';
        }
        // Validation
        elseif (empty($username) || empty($fullname) || empty($email)) {
            $_SESSION['error'] = 'Please fill all required fields';
        } 
        // Check username format
        elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $_SESSION['error'] = 'Username can only contain letters, numbers, and underscore (_)';
        }
        // Check username length
        elseif (strlen($username) < 3 || strlen($username) > 50) {
            $_SESSION['error'] = 'Username must be between 3 and 50 characters';
        }
        // Check email format
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
        }
        else {
            // Use prepared statement for secure insertion
            $stmt = mysqli_prepare($conn, "INSERT INTO AdminUsers (Username, Password, FullName, Email, Role, StaffID) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssi", $username, $hashed_password, $fullname, $email, $role, $staff_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'User created successfully. Default password: password123';
                logSecurityEvent('New user created: ' . $username . ' (' . $role . ')');
                mysqli_stmt_close($stmt);
                header('Location: ' . BASE_PATH . 'admin/users.php');
                exit();
            } else {
                $_SESSION['error'] = 'Error creating user: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($email_stmt);
    }
    mysqli_stmt_close($check_stmt);
}

include '../includes/header.php';

// Get staff list for linking
$staff_list = mysqli_query($conn, "SELECT StaffID, Name, Title FROM Staff ORDER BY Name");
$csrf_token = generateCSRFToken();
?>

<div class="form-container" style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 30px;">
        <i class="fas fa-user-plus"></i> Add New User
    </h1>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="form-group">
            <label for="username">Username <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" required 
                   placeholder="e.g., jdoe" maxlength="50"
                   pattern="[a-zA-Z0-9_]+"
                   title="Only letters, numbers, and underscore allowed">
            <small>Only letters, numbers, and underscore (_). Must be unique.</small>
        </div>
        
        <div class="form-group">
            <label for="fullname">Full Name <span style="color: red;">*</span></label>
            <input type="text" id="fullname" name="fullname" required 
                   placeholder="e.g., John Doe" maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" required 
                   placeholder="john.doe@university.ac.uk" maxlength="100">
            <small>Must be a valid email address and unique in the system.</small>
        </div>
        
        <div class="form-group">
            <label for="role">Role <span style="color: red;">*</span></label>
            <select id="role" name="role" required>
                <option value="staff">Staff (View Only)</option>
                <option value="admin">Administrator (Full Access)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="staff_id">Link to Staff Record (Optional)</label>
            <select id="staff_id" name="staff_id">
                <option value="">-- Not linked --</option>
                <?php while ($staff = mysqli_fetch_assoc($staff_list)): ?>
                    <option value="<?php echo $staff['StaffID']; ?>">
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
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <p><strong><i class="fas fa-key"></i> Default Password:</strong> <code>password123</code></p>
                <p class="small" style="color: #666; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Password is securely hashed before storage.
                </p>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Create User
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
}
code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
}
</style>

<?php include '../includes/footer.php'; ?>