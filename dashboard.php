<?php
require_once '../includes/config.php';

// Check if user is logged in and is staff
if (!isLoggedIn() || !hasRole('staff')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Staff Dashboard';
$staff_id = $_SESSION['staff_id'] ?? 0;

if ($staff_id == 0) {
    $_SESSION['error'] = 'No staff profile linked to your account';
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit();
}

include '../includes/header.php';

// Get staff details
$staff_query = "SELECT * FROM Staff WHERE StaffID = $staff_id";
$staff_result = mysqli_query($conn, $staff_query);
$staff = mysqli_fetch_assoc($staff_result);

// Get modules led by this staff member
$modules_query = "SELECT m.*, 
                         (SELECT COUNT(*) FROM ProgrammeModules WHERE ModuleID = m.ModuleID) as programme_count,
                         (SELECT COUNT(*) FROM ProgrammeModules pm 
                          JOIN InterestedStudents i ON pm.ProgrammeID = i.ProgrammeID 
                          WHERE pm.ModuleID = m.ModuleID AND i.Status = 'active') as interested_count
                  FROM Modules m 
                  WHERE m.ModuleLeaderID = $staff_id
                  ORDER BY m.Status DESC, m.ModuleName";
$modules_result = mysqli_query($conn, $modules_query);
$modules_count = mysqli_num_rows($modules_result);

// Get programmes where this staff is programme leader
$programmes_query = "SELECT p.*, l.LevelName,
                            (SELECT COUNT(*) FROM ProgrammeModules WHERE ProgrammeID = p.ProgrammeID) as module_count,
                            (SELECT COUNT(*) FROM InterestedStudents WHERE ProgrammeID = p.ProgrammeID AND Status='active') as active_interests
                    FROM Programmes p
                    LEFT JOIN Levels l ON p.LevelID = l.LevelID
                    WHERE p.ProgrammeLeaderID = $staff_id
                    ORDER BY p.Status DESC, p.ProgrammeName";
$programmes_result = mysqli_query($conn, $programmes_query);
$programmes_count = mysqli_num_rows($programmes_result);

// Get total interested students
$total_interested = 0;
if ($modules_count > 0) {
    $interested_query = "SELECT COUNT(DISTINCT i.InterestID) as total 
                        FROM InterestedStudents i 
                        JOIN ProgrammeModules pm ON i.ProgrammeID = pm.ProgrammeID
                        WHERE pm.ModuleID IN (SELECT ModuleID FROM Modules WHERE ModuleLeaderID = $staff_id)
                        AND i.Status = 'active'";
    $interested_result = mysqli_query($conn, $interested_query);
    $total_interested = mysqli_fetch_assoc($interested_result)['total'];
}

// Get recent activities
$recent_query = "SELECT 'module' as type, m.ModuleName as name, m.Status, m.UpdatedAt as date,
                         'MOD' as prefix, m.ModuleID as id
                  FROM Modules m 
                  WHERE m.ModuleLeaderID = $staff_id 
                  UNION ALL
                  SELECT 'programme' as type, p.ProgrammeName as name, p.Status, p.UpdatedAt as date,
                         'PROG' as prefix, p.ProgrammeID as id
                  FROM Programmes p 
                  WHERE p.ProgrammeLeaderID = $staff_id 
                  ORDER BY date DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

// Show welcome message
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 8px; animation: slideIn 0.3s ease;">';
    echo '<i class="fas fa-check-circle"></i> ' . $_SESSION['success'];
    echo '</div>';
    unset($_SESSION['success']);
}
?>

<!-- Welcome Section -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 class="dashboard-title">
        <i class="fas fa-chalkboard-teacher" style="color: #667eea;"></i> 
        Welcome, <?php echo htmlspecialchars($staff['Name']); ?>!
    </h1>
    <div>
        <span class="status-badge" style="background: #667eea; color: white; padding: 8px 20px; border-radius: 30px;">
            <i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
        </span>
    </div>
</div>

<!-- Staff Profile Card -->
<div class="dashboard-grid" style="margin-bottom: 30px;">
    <div class="dashboard-card" style="grid-column: 1 / -1; padding: 30px;">
        <div style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
            <!-- Profile Photo -->
            <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); border: 4px solid white;">
                <?php if (!empty($staff['Photo'])): ?>
                    <img src="<?php echo htmlspecialchars($staff['Photo']); ?>" 
                         alt="<?php echo htmlspecialchars($staff['Name']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-graduate" style="font-size: 3rem; color: white;"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Staff Info -->
            <div style="flex: 1;">
                <h2 style="margin-bottom: 5px; color: #333;"><?php echo htmlspecialchars($staff['Name']); ?></h2>
                <p style="color: #667eea; font-weight: 600; margin-bottom: 10px;">
                    <i class="fas fa-tag"></i> 
                    <?php echo htmlspecialchars($staff['Title'] ?? 'Faculty Member'); ?>
                </p>
                <p style="margin-bottom: 5px; color: #666;">
                    <i class="fas fa-building" style="color: #667eea;"></i> 
                    <?php echo htmlspecialchars($staff['Department'] ?? 'Computing Department'); ?>
                </p>
                <?php if (!empty($staff['Email'])): ?>
                    <p>
                        <i class="fas fa-envelope" style="color: #667eea;"></i> 
                        <a href="mailto:<?php echo htmlspecialchars($staff['Email']); ?>" style="color: #007bff; text-decoration: none;">
                            <?php echo htmlspecialchars($staff['Email']); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Quick Stats -->
            <div style="display: flex; gap: 30px; background: #f8f9fa; padding: 20px 30px; border-radius: 15px;">
                <div style="text-align: center;">
                    <div style="font-size: 2.2rem; font-weight: 700; color: #667eea;"><?php echo $modules_count; ?></div>
                    <div style="color: #666; font-size: 0.9rem;">Modules Led</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.2rem; font-weight: 700; color: #667eea;"><?php echo $programmes_count; ?></div>
                    <div style="color: #666; font-size: 0.9rem;">Programmes Led</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.2rem; font-weight: 700; color: #667eea;"><?php echo $total_interested; ?></div>
                    <div style="color: #666; font-size: 0.9rem;">Interested Students</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($staff['Bio'])): ?>
            <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e0e0e0;">
                <h3 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-quote-right" style="color: #667eea;"></i> About
                </h3>
                <p style="line-height: 1.8; color: #555;"><?php echo nl2br(htmlspecialchars($staff['Bio'])); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Action Cards -->
<div class="dashboard-grid" style="margin-bottom: 30px;">
    <div class="dashboard-card" onclick="window.location.href='my-modules.php'" style="cursor: pointer; transition: transform 0.3s;">
        <div class="card-icon">
            <i class="fas fa-cube" style="color: #28a745;"></i>
        </div>
        <div class="card-number"><?php echo $modules_count; ?></div>
        <div class="card-label">My Modules</div>
        <div style="margin-top: 15px; color: #667eea;">
            <i class="fas fa-arrow-right"></i> Manage Modules
        </div>
    </div>
    
    <div class="dashboard-card" onclick="window.location.href='my-programmes.php'" style="cursor: pointer; transition: transform 0.3s;">
        <div class="card-icon">
            <i class="fas fa-book" style="color: #17a2b8;"></i>
        </div>
        <div class="card-number"><?php echo $programmes_count; ?></div>
        <div class="card-label">My Programmes</div>
        <div style="margin-top: 15px; color: #667eea;">
            <i class="fas fa-arrow-right"></i> Manage Programmes
        </div>
    </div>
    
    <div class="dashboard-card" onclick="window.location.href='profile.php'" style="cursor: pointer; transition: transform 0.3s;">
        <div class="card-icon">
            <i class="fas fa-id-card" style="color: #ffc107;"></i>
        </div>
        <div class="card-number">1</div>
        <div class="card-label">My Profile</div>
        <div style="margin-top: 15px; color: #667eea;">
            <i class="fas fa-arrow-right"></i> View Profile
        </div>
    </div>
</div>

<!-- Recent Activity -->
<?php if (mysqli_num_rows($recent_result) > 0): ?>
<div class="table-container" style="margin-top: 30px;">
    <div class="table-header">
        <h2><i class="fas fa-history" style="color: #667eea;"></i> Recent Activity</h2>
        <span class="status-badge" style="background: #667eea; color: white;">Last 10 updates</span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = mysqli_fetch_assoc($recent_result)): ?>
                <tr>
                    <td>
                        <?php if ($activity['type'] == 'module'): ?>
                            <span style="background: #e3f2fd; color: #1976d2; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                                <i class="fas fa-cube"></i> Module
                            </span>
                        <?php else: ?>
                            <span style="background: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                                <i class="fas fa-book"></i> Programme
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($activity['name']); ?></strong></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($activity['Status']); ?>">
                            <?php echo ucfirst($activity['Status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($activity['date'])); ?></td>
                    <td class="action-btns">
                        <?php if ($activity['type'] == 'module'): ?>
                            <a href="my-modules.php?view=<?php echo $activity['id']; ?>" class="view-btn" title="View Module">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_PATH; ?>admin/edit-module.php?id=<?php echo $activity['id']; ?>" class="edit-btn" title="Edit Module">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php else: ?>
                            <a href="my-programmes.php?view=<?php echo $activity['id']; ?>" class="view-btn" title="View Programme">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_PATH; ?>admin/edit-programme.php?id=<?php echo $activity['id']; ?>" class="edit-btn" title="Edit Programme">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
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

.dashboard-card {
    transition: all 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.status-badge.status-active {
    background: #d4edda;
    color: #155724;
}

.status-badge.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.status-published {
    background: #d4edda;
    color: #155724;
}

.status-badge.status-draft {
    background: #fff3cd;
    color: #856404;
}
</style>

<?php include '../includes/footer.php'; ?>