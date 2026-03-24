<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Manage Modules';

// Handle module actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action == 'delete' && $id > 0) {
        // Check if module is used in any programme
        $check = mysqli_query($conn, "SELECT * FROM ProgrammeModules WHERE ModuleID = $id");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = 'Cannot delete module that is assigned to programmes';
        } else {
            mysqli_query($conn, "DELETE FROM Modules WHERE ModuleID = $id");
            $_SESSION['message'] = 'Module deleted successfully';
        }
        header('Location: ' . BASE_PATH . 'admin/modules.php');
        exit();
    }
}

include '../includes/header.php';

// Get all staff for module leader dropdown
$staff_query = "SELECT * FROM Staff ORDER BY Name";
$staff_result = mysqli_query($conn, $staff_query);
?>

<div class="table-container">
    <div class="table-header">
        <h1><i class="fas fa-cube"></i> Manage Modules</h1>
        <a href="<?php echo BASE_PATH; ?>admin/edit-module.php" class="add-btn">
            <i class="fas fa-plus"></i> Add New Module
        </a>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin: 15px; border-radius: 8px;">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 8px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Module Code</th>
                    <th>Module Name</th>
                    <th>Module Leader</th>
                    <th>Description</th>
                    <th>Programmes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT m.*, s.Name as leader_name, s.Title as leader_title,
                                (SELECT COUNT(*) FROM ProgrammeModules WHERE ModuleID = m.ModuleID) as programme_count 
                         FROM Modules m
                         LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                         ORDER BY m.ModuleID";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td><strong>MOD" . str_pad($row['ModuleID'], 3, '0', STR_PAD_LEFT) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($row['ModuleName']) . "</td>";
                        echo "<td>";
                        echo htmlspecialchars($row['leader_name'] ?? 'Not assigned');
                        if (!empty($row['leader_title'])) {
                            echo "<br><small>" . htmlspecialchars($row['leader_title']) . "</small>";
                        }
                        echo "</td>";
                        echo "<td><small>" . substr(htmlspecialchars($row['Description']), 0, 50) . "...</small></td>";
                        echo "<td>" . $row['programme_count'] . " programmes</td>";
                        echo "<td>";
                        $statusClass = strtolower($row['Status'] ?? 'active');
                        echo "<span class='status-badge status-" . $statusClass . "'>" . ucfirst($row['Status'] ?? 'Active') . "</span>";
                        echo "</td>";
                        echo "<td class='action-btns'>";
                        
                        // Edit button
                        echo "<a href='" . BASE_PATH . "admin/edit-module.php?id=" . $row['ModuleID'] . "' class='edit-btn' title='Edit Module'>";
                        echo "<i class='fas fa-edit'></i></a>";
                        
                        // Assign to Programme button
                        echo "<a href='" . BASE_PATH . "admin/assign-module.php?module_id=" . $row['ModuleID'] . "' class='view-btn' title='Assign to Programme'>";
                        echo "<i class='fas fa-link'></i></a>";
                        
                        // Delete button
                        echo "<a href='?action=delete&id=" . $row['ModuleID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this module?\")' title='Delete Module'>";
                        echo "<i class='fas fa-trash'></i></a>";
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; padding: 40px;'>";
                    echo "<i class='fas fa-cubes' style='font-size: 3rem; color: #ccc;'></i>";
                    echo "<p style='margin-top: 10px;'>No modules found. Click 'Add New Module' to get started.</p>";
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>