<?php
/**
 * Manage Staff - Display staff list with photos
 */

require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Manage Staff';

// Handle staff actions (using POST for delete security)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token';
    } else {
        $id = (int)$_POST['delete_id'];
        
        $check_module = mysqli_query($conn, "SELECT * FROM Modules WHERE ModuleLeaderID = $id");
        $check_programme = mysqli_query($conn, "SELECT * FROM Programmes WHERE ProgrammeLeaderID = $id");
        
        if (mysqli_num_rows($check_module) > 0 || mysqli_num_rows($check_programme) > 0) {
            $_SESSION['error'] = 'Cannot delete staff member who is assigned as module or programme leader';
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM Staff WHERE StaffID = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'Staff member deleted successfully';
            } else {
                $_SESSION['error'] = 'Error deleting staff';
            }
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: ' . BASE_PATH . 'admin/staff.php');
    exit();
}

include '../includes/header.php';

// Get all staff members
$query = "SELECT s.*, 
                 (SELECT COUNT(*) FROM Modules WHERE ModuleLeaderID = s.StaffID) as module_count,
                 (SELECT COUNT(*) FROM Programmes WHERE ProgrammeLeaderID = s.StaffID) as programme_count
          FROM Staff s
          ORDER BY s.Name";
$result = mysqli_query($conn, $query);

$csrf_token = generateCSRFToken();
?>

<div class="table-container">
    <div class="table-header">
        <h1><i class="fas fa-users"></i> Manage Staff</h1>
        <a href="<?php echo BASE_PATH; ?>admin/edit-staff.php" class="add-btn">
            <i class="fas fa-plus"></i> Add New Staff
        </a>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Modules</th>
                    <th>Programmes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <?php if (!empty($row['Photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['Photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['Name']); ?>"
                                         style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #667eea;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-user" style="color: #6c757d; font-size: 1.2rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>#<?php echo $row['StaffID']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['Name']); ?></strong>
                                <?php if (!empty($row['Photo'])): ?>
                                    <br><small><i class="fas fa-image"></i> Has photo</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['Title'] ?? 'Not set'); ?></td>
                            <td><?php echo htmlspecialchars($row['Department'] ?? 'Not set'); ?></td>
                            <td>
                                <?php if (!empty($row['Email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($row['Email']); ?>">
                                        <?php echo htmlspecialchars($row['Email']); ?>
                                    </a>
                                <?php else: ?>
                                    Not set
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><?php echo $row['module_count']; ?></td>
                            <td style="text-align: center;"><?php echo $row['programme_count']; ?></td>
                            <td class="action-btns">
                                <a href="<?php echo BASE_PATH; ?>admin/edit-staff.php?id=<?php echo $row['StaffID']; ?>" class="edit-btn" title="Edit Staff">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($row['module_count'] == 0 && $row['programme_count'] == 0): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['StaffID']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Delete this staff member?')" title="Delete Staff">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="delete-btn" disabled title="Cannot delete - assigned to modules or programmes">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <i class="fas fa-users" style="font-size: 3rem; color: #ccc;"></i>
                            <p style="margin-top: 10px;">No staff members found. Click 'Add New Staff' to get started.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.alert {
    padding: 15px;
    margin: 15px;
    border-radius: 8px;
}
.alert-success {
    background: #d4edda;
    color: #155724;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
}
</style>

<?php include '../includes/footer.php'; ?>