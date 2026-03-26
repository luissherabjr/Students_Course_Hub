<?php
/**
 * Manage Programmes
 */

require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Manage Programmes';

// Handle delete with POST for CSRF protection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token';
    } else {
        $id = (int)$_POST['delete_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM Programmes WHERE ProgrammeID = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Programme deleted successfully';
        } else {
            $_SESSION['error'] = 'Error deleting programme';
        }
        mysqli_stmt_close($stmt);
    }
    header('Location: ' . BASE_PATH . 'admin/programmes.php');
    exit();
}

// Handle toggle status
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "UPDATE Programmes SET Status = IF(Status='published', 'draft', 'published') WHERE ProgrammeID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $_SESSION['message'] = 'Programme status updated';
    header('Location: ' . BASE_PATH . 'admin/programmes.php');
    exit();
}

include '../includes/header.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = "";
if ($filter == 'published') {
    $where = "WHERE p.Status = 'published'";
} elseif ($filter == 'draft') {
    $where = "WHERE p.Status = 'draft'";
}

$query = "SELECT p.*, l.LevelName,
                 (SELECT COUNT(*) FROM ProgrammeModules WHERE ProgrammeID = p.ProgrammeID) as module_count
          FROM Programmes p
          LEFT JOIN Levels l ON p.LevelID = l.LevelID
          $where
          ORDER BY p.CreatedAt DESC";
$result = mysqli_query($conn, $query);

$csrf_token = generateCSRFToken();
?>

<div class="table-container">
    <div class="table-header">
        <h1><i class="fas fa-book"></i> Manage Programmes</h1>
        <div style="display: flex; gap: 10px;">
            <select onchange="window.location.href='?filter='+this.value" class="filter-select">
                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All</option>
                <option value="published" <?php echo $filter == 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
            <a href="<?php echo BASE_PATH; ?>admin/edit-programme.php" class="add-btn">
                <i class="fas fa-plus"></i> Add New
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Programme Name</th>
                    <th>Level</th>
                    <th>Leader</th>
                    <th>Modules</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $leader_name = 'Not Assigned';
                    if ($row['ProgrammeLeaderID']) {
                        $leader_query = "SELECT Name FROM Staff WHERE StaffID = " . $row['ProgrammeLeaderID'];
                        $leader_result = mysqli_query($conn, $leader_query);
                        if ($leader_result && mysqli_num_rows($leader_result) > 0) {
                            $leader = mysqli_fetch_assoc($leader_result);
                            $leader_name = $leader['Name'];
                        }
                    }
                ?>
                <tr>
                    <td>#<?php echo $row['ProgrammeID']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['ProgrammeName']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['LevelName']); ?></td>
                    <td><?php echo htmlspecialchars($leader_name); ?></td>
                    <td><?php echo $row['module_count']; ?> modules</td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($row['Status'] ?? 'draft'); ?>">
                            <?php echo ucfirst($row['Status'] ?? 'Draft'); ?>
                        </span>
                    </td>
                    <td class="action-btns">
                        <a href="<?php echo BASE_PATH; ?>admin/edit-programme.php?id=<?php echo $row['ProgrammeID']; ?>" class="edit-btn" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>admin/programmes.php?toggle=1&id=<?php echo $row['ProgrammeID']; ?>" 
                           class="edit-btn" style="background: #ffc107;" title="Toggle Status">
                            <i class="fas fa-<?php echo $row['Status'] == 'published' ? 'eye-slash' : 'eye'; ?>"></i>
                        </a>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="delete_id" value="<?php echo $row['ProgrammeID']; ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Delete this programme?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>