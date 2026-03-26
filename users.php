<?php
/**
 * User Management - Manage admin and staff login accounts
 */

require_once '../includes/config.php';

// Only admin can access this page
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'User Management';

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Delete user
    if ($action == 'delete' && $id > 0) {
        // Don't allow deleting own account
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'You cannot delete your own account';
        } else {
            $delete = mysqli_query($conn, "DELETE FROM AdminUsers WHERE AdminID = $id");
            if ($delete) {
                $_SESSION['message'] = 'User deleted successfully';
                logSecurityEvent('User deleted: ID ' . $id);
            } else {
                $_SESSION['error'] = 'Error deleting user';
            }
        }
        header('Location: ' . BASE_PATH . 'admin/users.php');
        exit();
    }
    
    // Reset password
    if ($action == 'reset-password' && $id > 0) {
        $new_password = 'password123'; // Default password
        $hashed_password = $new_password; // For demo (use password_hash in production)
        
        $update = mysqli_query($conn, "UPDATE AdminUsers SET Password = '$hashed_password' WHERE AdminID = $id");
        if ($update) {
            $_SESSION['message'] = 'Password reset to: password123';
            logSecurityEvent('Password reset for user ID: ' . $id);
        } else {
            $_SESSION['error'] = 'Error resetting password';
        }
        header('Location: ' . BASE_PATH . 'admin/users.php');
        exit();
    }
    
    // Toggle user status (activate/deactivate)
    if ($action == 'toggle-status' && $id > 0) {
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'You cannot deactivate your own account';
        } else {
            $update = mysqli_query($conn, "UPDATE AdminUsers SET IsActive = NOT IsActive WHERE AdminID = $id");
            if ($update) {
                $_SESSION['message'] = 'User status updated';
            }
        }
        header('Location: ' . BASE_PATH . 'admin/users.php');
        exit();
    }
}

include '../includes/header.php';

// Get all users with staff info
$users_query = "SELECT a.*, s.Name as staff_name, s.Title as staff_title
                FROM AdminUsers a
                LEFT JOIN Staff s ON a.StaffID = s.StaffID
                ORDER BY a.Role, a.Username";
$users_result = mysqli_query($conn, $users_query);

// Get statistics
$total_users = mysqli_num_rows($users_result);
$admin_count = 0;
$staff_count = 0;
$active_count = 0;

while ($user = mysqli_fetch_assoc($users_result)) {
    if ($user['Role'] == 'admin') $admin_count++;
    if ($user['Role'] == 'staff') $staff_count++;
    if ($user['IsActive'] == 1) $active_count++;
}
mysqli_data_seek($users_result, 0);
?>

<div class="dashboard-title" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1><i class="fas fa-users-cog"></i> User Management</h1>
        <p style="color: #666; margin-top: 5px;">Manage admin and staff login accounts</p>
    </div>
    <a href="add-user.php" class="add-btn">
        <i class="fas fa-plus"></i> Add New User
    </a>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid" style="margin-bottom: 30px;">
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="card-number"><?php echo $total_users; ?></div>
        <div class="card-label">Total Users</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-user-shield" style="color: #dc3545;"></i>
        </div>
        <div class="card-number"><?php echo $admin_count; ?></div>
        <div class="card-label">Administrators</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-chalkboard-teacher" style="color: #28a745;"></i>
        </div>
        <div class="card-number"><?php echo $staff_count; ?></div>
        <div class="card-label">Staff Members</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-check-circle" style="color: #17a2b8;"></i>
        </div>
        <div class="card-number"><?php echo $active_count; ?></div>
        <div class="card-label">Active Accounts</div>
    </div>
</div>

<!-- Users List -->
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> System Users</h2>
        <span class="status-badge" style="background: #667eea; color: white;">
            <i class="fas fa-info-circle"></i> Default password: password123
        </span>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Linked Staff</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                    <td>#<?php echo $user['AdminID']; ?></td>
                    <td><strong><?php echo htmlspecialchars($user['Username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                    <td>
                        <span class="status-badge" style="background: <?php echo $user['Role'] == 'admin' ? '#dc3545' : '#28a745'; ?>; color: white;">
                            <?php echo ucfirst($user['Role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['StaffID']): ?>
                            <?php echo htmlspecialchars($user['staff_name']); ?>
                            <?php if (!empty($user['staff_title'])): ?>
                                <br><small><?php echo htmlspecialchars($user['staff_title']); ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #999;">Not linked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $user['IsActive'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['IsActive'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($user['CreatedAt'])); ?></td>
                    <td class="action-btns">
                        <a href="edit-user.php?id=<?php echo $user['AdminID']; ?>" class="edit-btn" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?action=reset-password&id=<?php echo $user['AdminID']; ?>" 
                           class="edit-btn" style="background: #ffc107;" 
                           onclick="return confirm('Reset password to default?')" title="Reset Password">
                            <i class="fas fa-key"></i>
                        </a>
                        <a href="?action=toggle-status&id=<?php echo $user['AdminID']; ?>" 
                           class="edit-btn" style="background: #17a2b8;" 
                           onclick="return confirm('Toggle account status?')" title="Activate/Deactivate">
                            <i class="fas fa-<?php echo $user['IsActive'] ? 'ban' : 'check'; ?>"></i>
                        </a>
                        <?php if ($user['AdminID'] != $_SESSION['user_id']): ?>
                            <a href="?action=delete&id=<?php echo $user['AdminID']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Delete this user?')" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Info -->
<div class="table-container" style="margin-top: 20px;">
    <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h4><i class="fas fa-info-circle"></i> Important Notes:</h4>
        <ul style="margin-top: 10px; color: #666;">
            <li><strong>Default password:</strong> All new users get password: <code>password123</code></li>
            <li><strong>Reset password:</strong> Reset sets password to default (password123)</li>
            <li><strong>Staff Link:</strong> Users must be linked to existing staff records in the Staff Management page</li>
            <li><strong>Admin vs Staff:</strong> Admin users can manage everything; Staff users have limited view-only access</li>
        </ul>
    </div>
</div>

<style>
.action-btns {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
.action-btns a {
    padding: 5px 8px;
    font-size: 0.8rem;
}
.status-active {
    background: #d4edda;
    color: #155724;
}
.status-inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>

<?php include '../includes/footer.php'; ?>