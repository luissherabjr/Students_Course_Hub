<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Mailing List';

// Handle interest status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'withdraw') {
        mysqli_query($conn, "UPDATE InterestedStudents SET Status='withdrawn', WithdrawnAt=NOW() WHERE InterestID=$id");
        $_SESSION['message'] = 'Interest marked as withdrawn';
    } elseif ($_GET['action'] == 'reactivate') {
        mysqli_query($conn, "UPDATE InterestedStudents SET Status='active', WithdrawnAt=NULL WHERE InterestID=$id");
        $_SESSION['message'] = 'Interest reactivated';
    } elseif ($_GET['action'] == 'delete') {
        mysqli_query($conn, "DELETE FROM InterestedStudents WHERE InterestID=$id");
        $_SESSION['message'] = 'Record deleted';
    }
    header('Location: ' . BASE_PATH . 'admin/mailing-list.php');
    exit();
}

include '../includes/header.php';

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';
$where = "";
if ($filter == 'active') {
    $where = "WHERE i.Status = 'active'";
} elseif ($filter == 'withdrawn') {
    $where = "WHERE i.Status = 'withdrawn'";
}

// Get summary statistics
$stats_query = "SELECT 
                    SUM(CASE WHEN Status='active' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN Status='withdrawn' THEN 1 ELSE 0 END) as withdrawn_count,
                    COUNT(*) as total_count
                FROM InterestedStudents";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="table-container">
    <div class="table-header">
        <h1><i class="fas fa-envelope"></i> Student Mailing List</h1>
        <div style="display: flex; gap: 10px;">
            <select onchange="window.location.href='?filter='+this.value" class="filter-select" aria-label="Filter interests">
                <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>Active Interests (<?php echo $stats['active_count']; ?>)</option>
                <option value="withdrawn" <?php echo $filter == 'withdrawn' ? 'selected' : ''; ?>>Withdrawn (<?php echo $stats['withdrawn_count']; ?>)</option>
                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Records (<?php echo $stats['total_count']; ?>)</option>
            </select>
            <a href="<?php echo BASE_PATH; ?>admin/export-mailing.php?filter=<?php echo $filter; ?>" class="add-btn" title="Export as CSV">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin: 15px; border-radius: 8px;">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Programme</th>
                    <th>Level</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT i.*, p.ProgrammeName, l.LevelName 
                         FROM InterestedStudents i
                         JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
                         JOIN Levels l ON p.LevelID = l.LevelID
                         $where
                         ORDER BY i.RegisteredAt DESC";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $statusClass = strtolower($row['Status']);
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['StudentName']) . "</strong></td>";
                        echo "<td><a href='mailto:" . htmlspecialchars($row['Email']) . "' title='Send email'>" . htmlspecialchars($row['Email']) . "</a></td>";
                        echo "<td>" . htmlspecialchars($row['ProgrammeName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['LevelName']) . "</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($row['RegisteredAt'])) . "</td>";
                        echo "<td>";
                        echo "<span class='status-badge status-" . $statusClass . "' title='Current status'>" . ucfirst($row['Status']) . "</span>";
                        if ($row['WithdrawnAt']) {
                            echo "<br><small class='withdrawn-date' title='Date withdrawn'>Withdrawn: " . date('d/m/Y', strtotime($row['WithdrawnAt'])) . "</small>";
                        }
                        echo "</td>";
                        echo "<td class='action-btns'>";
                        
                        if ($row['Status'] == 'active') {
                            echo "<a href='?action=withdraw&id=" . $row['InterestID'] . "' class='edit-btn' style='background: #ffc107;' title='Mark as withdrawn' onclick='return confirm(\"Mark this interest as withdrawn?\")'>";
                            echo "<i class='fas fa-user-slash'></i> Withdraw";
                            echo "</a>";
                        } else {
                            echo "<a href='?action=reactivate&id=" . $row['InterestID'] . "' class='edit-btn' style='background: #28a745;' title='Reactivate interest' onclick='return confirm(\"Reactivate this interest?\")'>";
                            echo "<i class='fas fa-user-check'></i> Reactivate";
                            echo "</a>";
                        }
                        
                        echo "<a href='?action=delete&id=" . $row['InterestID'] . "' class='delete-btn' title='Delete record' onclick='return confirm(\"Are you sure you want to delete this record?\")'>";
                        echo "<i class='fas fa-trash'></i></a>";
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; padding: 40px;'>";
                    echo "<i class='fas fa-inbox' style='font-size: 3rem; color: #ccc;'></i>";
                    echo "<p style='margin-top: 10px;'>No student interests found for the selected filter.</p>";
                    if ($filter != 'all') {
                        echo "<p><a href='?filter=all' class='view-btn' style='display: inline-block; margin-top: 10px;'>View all records</a></p>";
                    }
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>