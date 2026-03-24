<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Admin Dashboard';

// Get statistics
$programmes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Programmes"))['count'];
$modules_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Modules"))['count'];
$staff_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Staff"))['count'];
$interests_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM InterestedStudents WHERE Status='active'"))['count'];

// Get published vs draft counts
$published_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Programmes WHERE Status='published'"))['count'];
$draft_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Programmes WHERE Status='draft'"))['count'];

include '../includes/header.php';

// Get admin details from session
$admin_name = $_SESSION['user_name'] ?? 'Admin';
$login_time = $_SESSION['login_time'] ?? time();

// European date and time format (UK/EU)
date_default_timezone_set('Europe/London');
$login_date = date('d/m/Y', $login_time);        // DD/MM/YYYY format
$login_time_formatted = date('H:i', $login_time); // 24-hour format

// Show welcome message if set
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 8px;">';
    echo '<i class="fas fa-check-circle"></i> ' . $_SESSION['success'];
    echo '</div>';
    unset($_SESSION['success']);
}
?>

<!-- Simple Welcome Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #e0e0e0; padding-bottom: 15px;">
    <h1 class="dashboard-title" style="margin-bottom: 0;">
        <i class="fas fa-tachometer-alt" style="color: #667eea; margin-right: 10px;"></i>
        Welcome, <?php echo htmlspecialchars($admin_name); ?>
    </h1>
    
    <div style="color: #666; font-size: 0.9rem;">
        <i class="fas fa-calendar-alt" style="color: #667eea; margin-right: 5px;"></i> 
        <?php echo $login_date; ?> 
        <span style="margin: 0 8px; color: #ccc;">|</span>
        <i class="fas fa-clock" style="color: #667eea; margin-right: 5px;"></i> 
        <?php echo $login_time_formatted; ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-book" aria-hidden="true"></i>
        </div>
        <div class="card-number"><?php echo $programmes_count; ?></div>
        <div class="card-label">Total Programmes</div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <span class="status-badge status-published">Published: <?php echo $published_count; ?></span>
            <span class="status-badge status-draft" style="margin-left: 5px;">Draft: <?php echo $draft_count; ?></span>
        </div>
        <a href="<?php echo BASE_PATH; ?>admin/programmes.php" class="view-btn" style="margin-top: 15px; display: inline-block;">Manage</a>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-cube" aria-hidden="true"></i>
        </div>
        <div class="card-number"><?php echo $modules_count; ?></div>
        <div class="card-label">Total Modules</div>
        <a href="<?php echo BASE_PATH; ?>admin/modules.php" class="view-btn" style="margin-top: 15px; display: inline-block;">Manage</a>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-users" aria-hidden="true"></i>
        </div>
        <div class="card-number"><?php echo $staff_count; ?></div>
        <div class="card-label">Staff Members</div>
        <a href="<?php echo BASE_PATH; ?>admin/staff.php" class="view-btn" style="margin-top: 15px; display: inline-block;">Manage</a>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-envelope" aria-hidden="true"></i>
        </div>
        <div class="card-number"><?php echo $interests_count; ?></div>
        <div class="card-label">Active Interests</div>
        <a href="<?php echo BASE_PATH; ?>admin/mailing-list.php" class="view-btn" style="margin-top: 15px; display: inline-block;">View</a>
    </div>
</div>
<!-- Staff Members Section -->
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-users"></i> Staff Members</h2>
        <a href="<?php echo BASE_PATH; ?>admin/staff.php" class="view-btn">Manage All Staff</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">Photo</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Modules</th>
                    <th>Programmes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get staff members with counts of modules and programmes they lead
                $staff_query = "SELECT s.*, 
                                    (SELECT COUNT(*) FROM Modules WHERE ModuleLeaderID = s.StaffID) as module_count,
                                    (SELECT COUNT(*) FROM Programmes WHERE ProgrammeLeaderID = s.StaffID) as programme_count
                                FROM Staff s 
                                ORDER BY s.Name 
                                LIMIT 5";
                $staff_result = mysqli_query($conn, $staff_query);
                
                if (mysqli_num_rows($staff_result) > 0):
                    while ($staff = mysqli_fetch_assoc($staff_result)):
                ?>
                <tr>
                    <!-- Photo Column -->
                    <td style="text-align: center;">
                        <?php if (!empty($staff['Photo'])): ?>
                            <img src="<?php echo htmlspecialchars($staff['Photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($staff['Name']); ?>"
                                 style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0;">
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <i class="fas fa-user" style="color: white; font-size: 1.2rem;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Name Column -->
                    <td>
                        <strong><?php echo htmlspecialchars($staff['Name']); ?></strong>
                        <br>
                        <small style="color: #6c757d;">ID: #<?php echo $staff['StaffID']; ?></small>
                    </td>
                    
                    <!-- Title Column -->
                    <td>
                        <?php if (!empty($staff['Title'])): ?>
                            <span style="font-weight: 500;"><?php echo htmlspecialchars($staff['Title']); ?></span>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">No title</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Department Column -->
                    <td>
                        <?php if (!empty($staff['Department'])): ?>
                            <i class="fas fa-building" style="color: #667eea; margin-right: 5px;"></i>
                            <?php echo htmlspecialchars($staff['Department']); ?>
                        <?php else: ?>
                            <span style="color: #999;">Not assigned</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Email Column -->
                    <td>
                        <?php if (!empty($staff['Email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($staff['Email']); ?>" 
                               style="color: #007bff; text-decoration: none; font-size: 0.85rem;">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($staff['Email']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">No email</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Modules Led Count -->
                    <td style="text-align: center;">
                        <?php 
                        $module_count = (int)$staff['module_count'];
                        if ($module_count > 0): ?>
                            <span class="badge-module">
                                <i class="fas fa-cube"></i> <?php echo $module_count; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #999;">0</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Programmes Led Count -->
                    <td style="text-align: center;">
                        <?php 
                        $programme_count = (int)$staff['programme_count'];
                        if ($programme_count > 0): ?>
                            <span class="badge-programme">
                                <i class="fas fa-book"></i> <?php echo $programme_count; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #999;">0</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 50px;">
                        <i class="fas fa-users" style="font-size: 4rem; color: #ccc;"></i>
                        <p style="margin-top: 15px; color: #666;">No staff members found.</p>
                        <a href="<?php echo BASE_PATH; ?>admin/edit-staff.php" class="add-btn" style="margin-top: 15px; display: inline-block;">
                            <i class="fas fa-plus"></i> Add First Staff Member
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Programmes - Simplified -->
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-clock" aria-hidden="true"></i> Recent Programmes</h2>
        <a href="<?php echo BASE_PATH; ?>admin/edit-programme.php" class="add-btn">
            <i class="fas fa-plus" aria-hidden="true"></i> Add New
        </a>
    </div>
    
    <div class="table-responsive">
        <table aria-label="Recent programmes list">
            <thead>
                <tr>
                    <th>Programme Name</th>
                    <th>Level</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT p.*, l.LevelName 
                         FROM Programmes p
                         LEFT JOIN Levels l ON p.LevelID = l.LevelID
                         ORDER BY p.CreatedAt DESC 
                         LIMIT 5";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['ProgrammeName']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($row['LevelName']) . "</td>";
                        echo "<td>";
                        $statusClass = strtolower($row['Status'] ?? 'draft');
                        echo "<span class='status-badge status-" . $statusClass . "'>" . ucfirst($row['Status'] ?? 'Draft') . "</span>";
                        echo "</td>";
                        echo "<td class='action-btns'>";
                        echo "<a href='" . BASE_PATH . "admin/edit-programme.php?id=" . $row['ProgrammeID'] . "' class='edit-btn' title='Edit Programme'><i class='fas fa-edit'></i></a>";
                        echo "<a href='" . BASE_PATH . "admin/programmes.php?action=delete&id=" . $row['ProgrammeID'] . "' class='delete-btn' onclick='return confirm(\"Are you sure?\")' title='Delete Programme'><i class='fas fa-trash'></i></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center; padding: 40px;'>";
                    echo "<i class='fas fa-folder-open' style='font-size: 3rem; color: #ccc;' aria-hidden='true'></i>";
                    echo "<p style='margin-top: 10px;'>No programmes found.</p>";
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.dashboard-title {
    font-size: 1.8rem;
    color: #333;
    margin: 0;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.dashboard-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
    text-align: center;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.card-icon {
    font-size: 2.5rem;
    color: #667eea;
    margin-bottom: 15px;
}

.card-number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.card-label {
    color: #777;
    font-size: 0.95rem;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-published {
    background: #d4edda;
    color: #155724;
}

.status-draft {
    background: #fff3cd;
    color: #856404;
}

.view-btn {
    background: #667eea;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.view-btn:hover {
    background: #5a67d8;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.add-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.add-btn:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.table-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h2 {
    font-size: 1.2rem;
    color: #333;
    margin: 0;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #555;
    border-bottom: 2px solid #e0e0e0;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
    vertical-align: middle;
}

tr:hover {
    background: #f8f9fa;
}

.action-btns {
    display: flex;
    gap: 8px;
}

.edit-btn, .delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    color: white;
}

.edit-btn {
    background: #007bff;
}

.edit-btn:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.delete-btn {
    background: #dc3545;
}

.delete-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
}

.alert {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .dashboard-title {
        font-size: 1.5rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .table-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
}
</style>

<?php include '../includes/footer.php'; ?>