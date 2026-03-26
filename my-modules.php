<?php
require_once '../includes/config.php';

// Check if user is logged in and is staff
if (!isLoggedIn() || !hasRole('staff')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'My Modules';
$staff_id = $_SESSION['staff_id'] ?? 0;

if ($staff_id == 0) {
    $_SESSION['error'] = 'No staff profile linked to your account';
    header('Location: ' . BASE_URL . 'staff/dashboard.php');
    exit();
}

include '../includes/header.php';

// Get all modules led by this staff member
$modules_query = "SELECT m.*, 
                         (SELECT COUNT(*) FROM ProgrammeModules WHERE ModuleID = m.ModuleID) as programme_count,
                         (SELECT COUNT(DISTINCT i.InterestID) FROM InterestedStudents i 
                          JOIN ProgrammeModules pm ON i.ProgrammeID = pm.ProgrammeID 
                          WHERE pm.ModuleID = m.ModuleID AND i.Status = 'active') as interested_count
                  FROM Modules m
                  WHERE m.ModuleLeaderID = $staff_id
                  ORDER BY m.ModuleName";
$modules_result = mysqli_query($conn, $modules_query);

// Get statistics
$total_modules = mysqli_num_rows($modules_result);
$active_count = 0;
$inactive_count = 0;

$count_query = "SELECT 
                    SUM(CASE WHEN Status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN Status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM Modules WHERE ModuleLeaderID = $staff_id";
$count_result = mysqli_query($conn, $count_query);
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $active_count = $count_row['active'] ?? 0;
    $inactive_count = $count_row['inactive'] ?? 0;
}

// Get total interested students across all modules
$total_interested_query = "SELECT COUNT(DISTINCT i.InterestID) as total 
                          FROM InterestedStudents i 
                          JOIN ProgrammeModules pm ON i.ProgrammeID = pm.ProgrammeID
                          WHERE pm.ModuleID IN (SELECT ModuleID FROM Modules WHERE ModuleLeaderID = $staff_id)
                          AND i.Status = 'active'";
$total_interested_result = mysqli_query($conn, $total_interested_query);
$total_interested = mysqli_fetch_assoc($total_interested_result)['total'];

// Handle view single module (modal) - COMPREHENSIVE details here
$view_module = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    
    // Get ALL details for the modal view
    $view_query = "SELECT m.*, 
                          s.Name as leader_name, 
                          s.Title as leader_title, 
                          s.Email as leader_email,
                          s.Department as leader_department,
                          s.Bio as leader_bio,
                          s.Photo as leader_photo,
                          (SELECT COUNT(*) FROM ProgrammeModules WHERE ModuleID = m.ModuleID) as programme_count,
                          (SELECT COUNT(DISTINCT i.InterestID) FROM InterestedStudents i 
                           JOIN ProgrammeModules pm ON i.ProgrammeID = pm.ProgrammeID 
                           WHERE pm.ModuleID = m.ModuleID AND i.Status = 'active') as interested_count,
                          (SELECT GROUP_CONCAT(DISTINCT CONCAT(p.ProgrammeName, ' (Year ', pm.Year, ') - ', l.LevelName) SEPARATOR '||') 
                           FROM ProgrammeModules pm 
                           JOIN Programmes p ON pm.ProgrammeID = p.ProgrammeID
                           JOIN Levels l ON p.LevelID = l.LevelID 
                           WHERE pm.ModuleID = m.ModuleID) as programme_list
                   FROM Modules m
                   LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                   WHERE m.ModuleID = $view_id AND m.ModuleLeaderID = $staff_id";
    $view_result = mysqli_query($conn, $view_query);
    
    if (mysqli_num_rows($view_result) > 0) {
        $view_module = mysqli_fetch_assoc($view_result);
        // Parse programme list
        if (!empty($view_module['programme_list'])) {
            $view_module['programmes'] = explode('||', $view_module['programme_list']);
        } else {
            $view_module['programmes'] = [];
        }
        
        // Get recent interested students for this module (last 5)
        $recent_students_query = "SELECT i.StudentName, i.Email, i.RegisteredAt, i.Status
                                   FROM InterestedStudents i
                                   JOIN ProgrammeModules pm ON i.ProgrammeID = pm.ProgrammeID
                                   WHERE pm.ModuleID = $view_id
                                   ORDER BY i.RegisteredAt DESC
                                   LIMIT 5";
        $recent_students_result = mysqli_query($conn, $recent_students_query);
        $view_module['recent_students'] = [];
        while ($student = mysqli_fetch_assoc($recent_students_result)) {
            $view_module['recent_students'][] = $student;
        }
    }
}

// Show success/error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 8px;">';
    echo '<i class="fas fa-check-circle"></i> ' . $_SESSION['success'];
    echo '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 8px;">';
    echo '<i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'];
    echo '</div>';
    unset($_SESSION['error']);
}
?>

<div class="dashboard-title">
    <h1><i class="fas fa-cube"></i> My Assigned Modules</h1>
    <p style="color: #666; margin-top: 5px;">Click the <i class="fas fa-eye" style="color: #17a2b8;"></i> button to view complete module details</p>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid" style="margin-bottom: 30px;">
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-cubes"></i>
        </div>
        <div class="card-number"><?php echo $total_modules; ?></div>
        <div class="card-label">Total Modules</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-check-circle" style="color: #28a745;"></i>
        </div>
        <div class="card-number"><?php echo $active_count; ?></div>
        <div class="card-label">Active Modules</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-users" style="color: #17a2b8;"></i>
        </div>
        <div class="card-number"><?php echo $total_interested; ?></div>
        <div class="card-label">Interested Students</div>
    </div>
</div>

<!-- View Single Module Modal - COMPREHENSIVE DETAILS -->
<?php if ($view_module): ?>
<div id="viewModuleModal" class="modal" style="display: block;">
    <div class="modal-content" style="max-width: 900px;">
        <span class="close" onclick="window.location.href='my-modules.php'">&times;</span>
        <h3><i class="fas fa-info-circle"></i> Module Details: <?php echo htmlspecialchars($view_module['ModuleName']); ?></h3>
        
        <div style="padding: 20px 0;">
            <!-- Module Header with Status -->
            <div style="display: flex; gap: 20px; margin-bottom: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px;">
                <div style="flex: 1;">
                    <h2 style="color: white; margin-bottom: 10px; font-size: 1.8rem;"><?php echo htmlspecialchars($view_module['ModuleName']); ?></h2>
                    <p><strong>Module Code:</strong> MOD<?php echo str_pad($view_module['ModuleID'], 3, '0', STR_PAD_LEFT); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge" style="background: <?php echo $view_module['Status'] == 'active' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 5px 15px; border-radius: 20px;">
                            <?php echo ucfirst($view_module['Status']); ?>
                        </span>
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2.5rem; font-weight: 700;"><?php echo $view_module['interested_count'] ?? 0; ?></div>
                    <div>Interested Students</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-top: 10px;"><?php echo $view_module['programme_count'] ?? 0; ?></div>
                    <div>Programmes</div>
                </div>
            </div>
            
            <!-- Two Column Layout for Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                <!-- Module Leader Information -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                    <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                        <i class="fas fa-user-tie"></i> Module Leader
                    </h4>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <?php if (!empty($view_module['leader_photo'])): ?>
                            <img src="<?php echo htmlspecialchars($view_module['leader_photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($view_module['leader_name'] ?? 'Module Leader'); ?>"
                                 style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: #667eea; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-tie" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p><strong><?php echo htmlspecialchars($view_module['leader_name'] ?? 'You'); ?></strong></p>
                            <?php if (!empty($view_module['leader_title'])): ?>
                                <p><?php echo htmlspecialchars($view_module['leader_title']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($view_module['leader_department'])): ?>
                                <p><small><?php echo htmlspecialchars($view_module['leader_department']); ?></small></p>
                            <?php endif; ?>
                            <?php if (!empty($view_module['leader_email'])): ?>
                                <p><a href="mailto:<?php echo htmlspecialchars($view_module['leader_email']); ?>"><?php echo htmlspecialchars($view_module['leader_email']); ?></a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($view_module['leader_bio'])): ?>
                        <div style="margin-top: 15px;">
                            <p><small><?php echo substr(htmlspecialchars($view_module['leader_bio']), 0, 150); ?>...</small></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Module Statistics -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                    <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                        <i class="fas fa-chart-bar"></i> Detailed Statistics
                    </h4>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Total Programmes:</td>
                            <td style="text-align: right;"><?php echo $view_module['programme_count'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Interested Students:</td>
                            <td style="text-align: right;"><?php echo $view_module['interested_count'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Created:</td>
                            <td style="text-align: right;"><?php echo date('d/m/Y', strtotime($view_module['CreatedAt'])); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Last Updated:</td>
                            <td style="text-align: right;"><?php echo date('d/m/Y', strtotime($view_module['UpdatedAt'])); ?></td>
                        </tr>
                        <?php if (!empty($view_module['Image'])): ?>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Has Image:</td>
                            <td style="text-align: right;"><i class="fas fa-check-circle" style="color: #28a745;"></i> Yes</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            
            <!-- Full Description -->
            <div style="margin-bottom: 25px; background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-align-left"></i> Full Description
                </h4>
                <div style="background: white; border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($view_module['Description'])); ?>
                </div>
            </div>
            
            <!-- Programmes List -->
            <div style="margin-bottom: 25px; background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-book"></i> Used in Programmes (<?php echo count($view_module['programmes'] ?? []); ?>)
                </h4>
                <?php if (!empty($view_module['programmes'])): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 10px;">
                        <?php foreach ($view_module['programmes'] as $programme): ?>
                            <div style="background: #e3f2fd; padding: 12px; border-radius: 8px; border-left: 4px solid #1976d2;">
                                <?php echo htmlspecialchars($programme); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #999; background: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <i class="fas fa-info-circle"></i> Not assigned to any programmes yet.
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Interested Students -->
            <?php if (!empty($view_module['recent_students'])): ?>
            <div style="margin-bottom: 25px; background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-users"></i> Recent Interested Students (Last 5)
                </h4>
                <div class="table-responsive">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($view_module['recent_students'] as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['StudentName']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>"><?php echo htmlspecialchars($student['Email']); ?></a></td>
                                <td><?php echo date('d/m/Y', strtotime($student['RegisteredAt'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($student['Status']); ?>">
                                        <?php echo ucfirst($student['Status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Module Image -->
            <?php if (!empty($view_module['Image'])): ?>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-image"></i> Module Image
                </h4>
                <div style="text-align: center;">
                    <img src="<?php echo htmlspecialchars($view_module['Image']); ?>" 
                         alt="<?php echo htmlspecialchars($view_module['ImageAlt'] ?? ''); ?>" 
                         style="max-width: 100%; max-height: 300px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <?php if (!empty($view_module['ImageAlt'])): ?>
                        <p style="color: #666; font-size: 0.9rem; margin-top: 10px;"><i class="fas fa-info-circle"></i> Alt: <?php echo htmlspecialchars($view_module['ImageAlt']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 25px; border-top: 2px solid #e0e0e0; padding-top: 20px;">
            <a href="my-modules.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Close
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modules List - SIMPLIFIED TABLE VIEW -->
<?php if ($total_modules > 0): ?>
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> My Modules Overview</h2>
        <div style="display: flex; gap: 10px;">
            <select onchange="filterModules(this.value)" class="filter-select">
                <option value="all">All Modules</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="modulesTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Module Name</th>
                    <th>Status</th>
                    <th>Programmes</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($modules_result, 0);
                while ($module = mysqli_fetch_assoc($modules_result)): 
                    // Get programmes count for this module (simplified)
                    $prog_count_query = "SELECT COUNT(*) as count FROM ProgrammeModules WHERE ModuleID = " . $module['ModuleID'];
                    $prog_count_result = mysqli_query($conn, $prog_count_query);
                    $prog_count = mysqli_fetch_assoc($prog_count_result)['count'];
                ?>
                <tr data-status="<?php echo $module['Status']; ?>">
                    <td><strong>MOD<?php echo str_pad($module['ModuleID'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo htmlspecialchars($module['ModuleName']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($module['Status']); ?>">
                            <?php echo ucfirst($module['Status']); ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge" style="background: #e3f2fd; color: #1976d2;">
                            <?php echo $prog_count; ?> programme(s)
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge" style="background: #17a2b8; color: white;">
                            <?php echo $module['interested_count']; ?> student(s)
                        </span>
                    </td>
                    <td class="action-btns">
                        <!-- View Details button - Shows comprehensive modal -->
                        <a href="?view=<?php echo $module['ModuleID']; ?>" class="view-btn" title="View Complete Module Details">
                            <i class="fas fa-eye"></i> Details
                        </a>
                        <!-- View Interested Students button - Direct link to students page -->
                        <a href="module-students.php?module_id=<?php echo $module['ModuleID']; ?>" class="edit-btn" title="View Interested Students">
                            <i class="fas fa-users"></i> Students
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Module Distribution Summary -->
<div class="table-container" style="margin-top: 30px;">
    <div class="table-header">
        <h2><i class="fas fa-chart-pie"></i> Module Distribution by Programme</h2>
    </div>
    <div style="padding: 20px;">
        <?php
        $dist_query = "SELECT p.ProgrammeName, l.LevelName, COUNT(pm.ModuleID) as module_count 
                       FROM ProgrammeModules pm
                       JOIN Programmes p ON pm.ProgrammeID = p.ProgrammeID
                       JOIN Levels l ON p.LevelID = l.LevelID
                       WHERE pm.ModuleID IN (SELECT ModuleID FROM Modules WHERE ModuleLeaderID = $staff_id)
                       GROUP BY p.ProgrammeID
                       ORDER BY module_count DESC";
        $dist_result = mysqli_query($conn, $dist_query);
        
        if (mysqli_num_rows($dist_result) > 0):
        ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <?php while ($dist = mysqli_fetch_assoc($dist_result)): ?>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #667eea;">
                <div style="font-size: 2rem; font-weight: 700; color: #667eea;"><?php echo $dist['module_count']; ?></div>
                <div style="font-weight: 600; margin: 5px 0;"><?php echo htmlspecialchars($dist['ProgrammeName']); ?></div>
                <div style="color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($dist['LevelName']); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <p style="text-align: center; color: #999; padding: 20px;">No module assignments found.</p>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="table-container">
    <div style="text-align: center; padding: 60px;">
        <i class="fas fa-cube" style="font-size: 5rem; color: #ccc; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">No Modules Assigned</h3>
        <p style="color: #999;">You are not assigned as a module leader for any modules yet.</p>
        <p style="color: #999; margin-top: 10px;">Please contact the administrator if you believe this is an error.</p>
    </div>
</div>
<?php endif; ?>

<script>
function filterModules(status) {
    const rows = document.querySelectorAll('#modulesTable tbody tr');
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
}

// Close modal if clicked outside
window.onclick = function(event) {
    const modal = document.getElementById('viewModuleModal');
    if (event.target == modal) {
        window.location.href = 'my-modules.php';
    }
}
</script>

<style>
.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge.status-active {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

.status-badge.status-inactive {
    background: #f8d7da;
    color: #721c24;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: #fefefe;
    margin: 30px auto;
    padding: 25px;
    border: 1px solid #888;
    width: 90%;
    max-width: 900px;
    border-radius: 15px;
    position: relative;
    max-height: 85vh;
    overflow-y: auto;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
    z-index: 10;
}

.close:hover {
    color: #667eea;
}

.action-btns {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.action-btns a {
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.action-btns a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.view-btn {
    background: #17a2b8;
}

.edit-btn {
    background: #28a745;
}

.table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.table-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.table-header h2 {
    font-size: 1.3rem;
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
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
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
    font-size: 0.9rem;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    font-size: 0.9rem;
    cursor: pointer;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
}
</style>

<?php include '../includes/footer.php'; ?>