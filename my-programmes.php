<?php
require_once '../includes/config.php';

// Check if user is logged in and is staff
if (!isLoggedIn() || !hasRole('staff')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'My Programmes';
$staff_id = $_SESSION['staff_id'] ?? 0;

if ($staff_id == 0) {
    $_SESSION['error'] = 'No staff profile linked to your account';
    header('Location: ' . BASE_URL . 'staff/dashboard.php');
    exit();
}

include '../includes/header.php';

// Get all programmes where this staff is programme leader - SIMPLIFIED for table view
$programmes_query = "SELECT p.*, l.LevelName,
                            (SELECT COUNT(*) FROM ProgrammeModules WHERE ProgrammeID = p.ProgrammeID) as module_count,
                            (SELECT COUNT(*) FROM InterestedStudents WHERE ProgrammeID = p.ProgrammeID AND Status='active') as active_interests
                    FROM Programmes p
                    LEFT JOIN Levels l ON p.LevelID = l.LevelID
                    WHERE p.ProgrammeLeaderID = $staff_id
                    ORDER BY p.ProgrammeName";
$programmes_result = mysqli_query($conn, $programmes_query);

// Get statistics
$total_programmes = mysqli_num_rows($programmes_result);
$published_count = 0;
$draft_count = 0;

$count_query = "SELECT 
                    SUM(CASE WHEN Status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN Status = 'draft' THEN 1 ELSE 0 END) as draft
                FROM Programmes WHERE ProgrammeLeaderID = $staff_id";
$count_result = mysqli_query($conn, $count_query);
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $published_count = $count_row['published'] ?? 0;
    $draft_count = $count_row['draft'] ?? 0;
}

// Get total interested students across all programmes
$total_interested_query = "SELECT COUNT(*) as total 
                          FROM InterestedStudents 
                          WHERE ProgrammeID IN (SELECT ProgrammeID FROM Programmes WHERE ProgrammeLeaderID = $staff_id)
                          AND Status = 'active'";
$total_interested_result = mysqli_query($conn, $total_interested_query);
$total_interested = mysqli_fetch_assoc($total_interested_result)['total'] ?? 0;

// Handle view single programme - COMPREHENSIVE details in modal
$view_programme = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    
    // Get ALL programme details for the modal view
    $view_query = "SELECT p.*, l.LevelName,
                          (SELECT COUNT(*) FROM ProgrammeModules WHERE ProgrammeID = p.ProgrammeID) as module_count,
                          (SELECT COUNT(*) FROM InterestedStudents WHERE ProgrammeID = p.ProgrammeID AND Status='active') as active_interests,
                          (SELECT COUNT(*) FROM InterestedStudents WHERE ProgrammeID = p.ProgrammeID) as total_interests,
                          (SELECT GROUP_CONCAT(CONCAT(m.ModuleName, ' (Year ', pm.Year, ') - ', m.Status) SEPARATOR '||') 
                           FROM ProgrammeModules pm 
                           JOIN Modules m ON pm.ModuleID = m.ModuleID 
                           WHERE pm.ProgrammeID = p.ProgrammeID) as module_list,
                          s.Name as leader_name,
                          s.Title as leader_title,
                          s.Email as leader_email,
                          s.Department as leader_department
                   FROM Programmes p
                   LEFT JOIN Levels l ON p.LevelID = l.LevelID
                   LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
                   WHERE p.ProgrammeID = $view_id AND p.ProgrammeLeaderID = $staff_id";
    $view_result = mysqli_query($conn, $view_query);
    
    if (mysqli_num_rows($view_result) > 0) {
        $view_programme = mysqli_fetch_assoc($view_result);
        // Parse module list
        if (!empty($view_programme['module_list'])) {
            $view_programme['modules'] = explode('||', $view_programme['module_list']);
        } else {
            $view_programme['modules'] = [];
        }
        
        // Get recent interested students for this programme (last 5)
        $recent_students_query = "SELECT StudentName, Email, RegisteredAt, Status
                                   FROM InterestedStudents
                                   WHERE ProgrammeID = $view_id
                                   ORDER BY RegisteredAt DESC
                                   LIMIT 5";
        $recent_students_result = mysqli_query($conn, $recent_students_query);
        $view_programme['recent_students'] = [];
        while ($student = mysqli_fetch_assoc($recent_students_result)) {
            $view_programme['recent_students'][] = $student;
        }
    } else {
        $_SESSION['error'] = 'Programme not found or you do not have permission to view it';
        header('Location: my-programmes.php');
        exit();
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

<div class="dashboard-title" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1><i class="fas fa-book"></i> My Programmes</h1>
        <p style="color: #666; margin-top: 5px;">Click the <i class="fas fa-eye" style="color: #17a2b8;"></i> button to view complete programme details</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid" style="margin-bottom: 30px;">
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="card-number"><?php echo $total_programmes; ?></div>
        <div class="card-label">Total Programmes</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-check-circle" style="color: #28a745;"></i>
        </div>
        <div class="card-number"><?php echo $published_count; ?></div>
        <div class="card-label">Published</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-users" style="color: #17a2b8;"></i>
        </div>
        <div class="card-number"><?php echo $total_interested; ?></div>
        <div class="card-label">Interested Students</div>
    </div>
</div>

<!-- View Single Programme Modal - COMPREHENSIVE DETAILS -->
<?php if ($view_programme): ?>
<div id="viewProgrammeModal" class="modal" style="display: block;">
    <div class="modal-content" style="max-width: 900px;">
        <span class="close" onclick="window.location.href='my-programmes.php'">&times;</span>
        <h3><i class="fas fa-info-circle"></i> Programme Details: <?php echo htmlspecialchars($view_programme['ProgrammeName']); ?></h3>
        
        <div style="padding: 20px 0;">
            <!-- Programme Header with Status -->
            <div style="display: flex; gap: 20px; margin-bottom: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px;">
                <div style="flex: 1;">
                    <h2 style="color: white; margin-bottom: 10px; font-size: 1.8rem;"><?php echo htmlspecialchars($view_programme['ProgrammeName']); ?></h2>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($view_programme['LevelName']); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge" style="background: <?php echo $view_programme['Status'] == 'published' ? '#28a745' : '#ffc107'; ?>; color: white; padding: 5px 15px; border-radius: 20px;">
                            <?php echo ucfirst($view_programme['Status']); ?>
                        </span>
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2.5rem; font-weight: 700;"><?php echo $view_programme['active_interests'] ?? 0; ?></div>
                    <div>Active Interests</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-top: 10px;"><?php echo $view_programme['module_count'] ?? 0; ?></div>
                    <div>Total Modules</div>
                </div>
            </div>
            
            <!-- Programme Leader Information -->
            <?php if (!empty($view_programme['leader_name'])): ?>
            <div style="margin-bottom: 25px; background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-user-tie"></i> Programme Leader
                </h4>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #667eea; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-tie" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <div>
                        <p><strong><?php echo htmlspecialchars($view_programme['leader_name']); ?></strong></p>
                        <?php if (!empty($view_programme['leader_title'])): ?>
                            <p><?php echo htmlspecialchars($view_programme['leader_title']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($view_programme['leader_department'])): ?>
                            <p><small><?php echo htmlspecialchars($view_programme['leader_department']); ?></small></p>
                        <?php endif; ?>
                        <?php if (!empty($view_programme['leader_email'])): ?>
                            <p><a href="mailto:<?php echo htmlspecialchars($view_programme['leader_email']); ?>"><?php echo htmlspecialchars($view_programme['leader_email']); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Two Column Layout for Statistics -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                    <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                        <i class="fas fa-cubes"></i> Module Statistics
                    </h4>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Total Modules:</td>
                            <td style="text-align: right;"><?php echo $view_programme['module_count'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Active Interests:</td>
                            <td style="text-align: right;"><?php echo $view_programme['active_interests'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Total Interests:</td>
                            <td style="text-align: right;"><?php echo $view_programme['total_interests'] ?? 0; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                    <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                        <i class="fas fa-calendar"></i> Timeline
                    </h4>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Created:</td>
                            <td style="text-align: right;"><?php echo date('d/m/Y', strtotime($view_programme['CreatedAt'])); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Last Updated:</td>
                            <td style="text-align: right;"><?php echo date('d/m/Y', strtotime($view_programme['UpdatedAt'])); ?></td>
                        </tr>
                        <?php if (!empty($view_programme['Image'])): ?>
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
                    <i class="fas fa-align-left"></i> Programme Description
                </h4>
                <div style="background: white; border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($view_programme['Description'])); ?>
                </div>
            </div>
            
            <!-- Modules List by Year -->
            <div style="margin-bottom: 25px; background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-cube"></i> Modules in this Programme (<?php echo count($view_programme['modules']); ?>)
                </h4>
                <?php if (!empty($view_programme['modules'])): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 10px;">
                        <?php foreach ($view_programme['modules'] as $module): ?>
                            <div style="background: #e3f2fd; padding: 12px; border-radius: 8px; border-left: 4px solid #1976d2;">
                                <?php echo htmlspecialchars($module); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #999; background: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <i class="fas fa-info-circle"></i> No modules assigned to this programme yet.
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Interested Students -->
            <?php if (!empty($view_programme['recent_students'])): ?>
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
                            <?php foreach ($view_programme['recent_students'] as $student): ?>
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
            
            <!-- Programme Image -->
            <?php if (!empty($view_programme['Image'])): ?>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h4 style="color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    <i class="fas fa-image"></i> Programme Image
                </h4>
                <div style="text-align: center;">
                    <img src="<?php echo htmlspecialchars($view_programme['Image']); ?>" 
                         alt="<?php echo htmlspecialchars($view_programme['ImageAlt'] ?? ''); ?>" 
                         style="max-width: 100%; max-height: 300px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <?php if (!empty($view_programme['ImageAlt'])): ?>
                        <p style="color: #666; font-size: 0.9rem; margin-top: 10px;"><i class="fas fa-info-circle"></i> Alt: <?php echo htmlspecialchars($view_programme['ImageAlt']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 25px; border-top: 2px solid #e0e0e0; padding-top: 20px;">
            <!-- Only Close button - Students button is in the table -->
            <a href="my-programmes.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Close
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Programmes List - SIMPLIFIED TABLE VIEW -->
<?php if ($total_programmes > 0): ?>
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> My Programmes Overview</h2>
        <div style="display: flex; gap: 10px;">
            <select onchange="filterProgrammes(this.value)" class="filter-select">
                <option value="all">All Programmes</option>
                <option value="published">Published Only</option>
                <option value="draft">Drafts Only</option>
            </select>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="programmesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Programme Name</th>
                    <th>Level</th>
                    <th>Modules</th>
                    <th>Active Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($programmes_result, 0);
                while ($programme = mysqli_fetch_assoc($programmes_result)): 
                ?>
                <tr data-status="<?php echo $programme['Status']; ?>">
                    <td>#<?php echo $programme['ProgrammeID']; ?></td>
                    <td><strong><?php echo htmlspecialchars($programme['ProgrammeName']); ?></strong></td>
                    <td><?php echo htmlspecialchars($programme['LevelName']); ?></td>
                    <td style="text-align: center;">
                        <span class="badge" style="background: #e3f2fd; color: #1976d2;">
                            <?php echo $programme['module_count'] ?? 0; ?> modules
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge" style="background: #17a2b8; color: white;">
                            <?php echo $programme['active_interests'] ?? 0; ?> students
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($programme['Status']); ?>">
                            <?php echo ucfirst($programme['Status']); ?>
                        </span>
                    </td>
                    <td class="action-btns">
                        <!-- View Details button - Shows comprehensive modal -->
                        <a href="?view=<?php echo $programme['ProgrammeID']; ?>" class="view-btn" title="View Complete Programme Details">
                            <i class="fas fa-eye"></i> Details
                        </a>
                        
                        <!-- View Interested Students button - Direct link to students page -->
                        <a href="programme-students.php?programme_id=<?php echo $programme['ProgrammeID']; ?>" 
                           class="edit-btn" style="background: #17a2b8;" title="View Interested Students">
                            <i class="fas fa-users"></i> Students
                            <?php if (($programme['active_interests'] ?? 0) > 0): ?>
                                <span style="background: white; color: #17a2b8; padding: 2px 6px; border-radius: 10px; margin-left: 5px; font-size: 0.7rem;">
                                    <?php echo $programme['active_interests'] ?? 0; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Module Distribution by Year -->
<div class="table-container" style="margin-top: 30px;">
    <div class="table-header">
        <h2><i class="fas fa-chart-bar"></i> Module Distribution by Year</h2>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Programme</th>
                    <th>Year 1</th>
                    <th>Year 2</th>
                    <th>Year 3</th>
                    <th>Year 4</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($programmes_result, 0);
                while ($programme = mysqli_fetch_assoc($programmes_result)): 
                    $year_query = "SELECT 
                                        SUM(CASE WHEN Year = 1 THEN 1 ELSE 0 END) as y1,
                                        SUM(CASE WHEN Year = 2 THEN 1 ELSE 0 END) as y2,
                                        SUM(CASE WHEN Year = 3 THEN 1 ELSE 0 END) as y3,
                                        SUM(CASE WHEN Year = 4 THEN 1 ELSE 0 END) as y4
                                  FROM ProgrammeModules 
                                  WHERE ProgrammeID = " . $programme['ProgrammeID'];
                    $year_result = mysqli_query($conn, $year_query);
                    $years = mysqli_fetch_assoc($year_result);
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($programme['ProgrammeName']); ?></strong></td>
                    <td style="text-align: center;"><?php echo $years['y1'] ?? 0; ?></td>
                    <td style="text-align: center;"><?php echo $years['y2'] ?? 0; ?></td>
                    <td style="text-align: center;"><?php echo $years['y3'] ?? 0; ?></td>
                    <td style="text-align: center;"><?php echo $years['y4'] ?? 0; ?></td>
                    <td style="text-align: center;"><strong><?php echo $programme['module_count'] ?? 0; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<div class="table-container">
    <div style="text-align: center; padding: 60px;">
        <i class="fas fa-book-open" style="font-size: 5rem; color: #ccc; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">No Programmes Assigned</h3>
        <p style="color: #999;">You are not assigned as a programme leader for any programmes yet.</p>
        <p style="color: #999; margin-top: 10px;">Please contact the administrator if you believe this is an error.</p>
    </div>
</div>
<?php endif; ?>

<script>
function filterProgrammes(status) {
    const rows = document.querySelectorAll('#programmesTable tbody tr');
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
    const modal = document.getElementById('viewProgrammeModal');
    if (event.target == modal) {
        window.location.href = 'my-programmes.php';
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

.status-badge.status-published {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

.status-badge.status-draft {
    background: #fff3cd;
    color: #856404;
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