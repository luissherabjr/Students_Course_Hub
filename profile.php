<?php
/**
 * Staff Profile - View staff profile with photo
 */

require_once '../includes/config.php';

if (!isLoggedIn() || !isStaff()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'My Profile';
$staff_id = $_SESSION['staff_id'] ?? 0;

if ($staff_id == 0) {
    $_SESSION['error'] = 'No staff profile linked to your account';
    header('Location: ' . BASE_URL . 'staff/dashboard.php');
    exit();
}

include '../includes/header.php';

// Get staff details with photo
$staff_query = "SELECT * FROM Staff WHERE StaffID = $staff_id";
$staff_result = mysqli_query($conn, $staff_query);
$staff = mysqli_fetch_assoc($staff_result);

// Get login account details
$login_query = "SELECT * FROM AdminUsers WHERE StaffID = $staff_id";
$login_result = mysqli_query($conn, $login_query);
$login = mysqli_fetch_assoc($login_result);

// Get statistics
$modules_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Modules WHERE ModuleLeaderID = $staff_id"))['count'];
$programmes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Programmes WHERE ProgrammeLeaderID = $staff_id"))['count'];

// Get recent activities
$recent_query = "SELECT 'module' as type, ModuleName as name, Status, UpdatedAt as date 
                 FROM Modules WHERE ModuleLeaderID = $staff_id 
                 UNION ALL
                 SELECT 'programme' as type, ProgrammeName as name, Status, UpdatedAt as date 
                 FROM Programmes WHERE ProgrammeLeaderID = $staff_id 
                 ORDER BY date DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);
?>

<div class="dashboard-title">
    <h1><i class="fas fa-id-card"></i> My Profile</h1>
</div>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 30px;">
    <!-- Left Column - Profile Photo & Basic Info -->
    <div>
        <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
            
            <!-- Profile Photo - Large Display -->
            <div style="width: 200px; height: 200px; border-radius: 50%; margin: 0 auto 20px; overflow: hidden; border: 5px solid #667eea; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                <?php if (!empty($staff['Photo'])): ?>
                    <img src="<?php echo htmlspecialchars($staff['Photo']); ?>" 
                         alt="<?php echo htmlspecialchars($staff['Name']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-graduate" style="font-size: 4rem; color: white;"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <h2 style="margin-bottom: 5px;"><?php echo htmlspecialchars($staff['Name']); ?></h2>
            <p style="color: #667eea; font-weight: 500; margin-bottom: 15px;"><?php echo htmlspecialchars($staff['Title'] ?? 'Staff Member'); ?></p>
            
            <div style="display: flex; justify-content: center; gap: 20px; padding: 15px 0; border-top: 1px solid #e0e0e0; border-bottom: 1px solid #e0e0e0; margin: 15px 0;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #667eea;"><?php echo $modules_count; ?></div>
                    <div style="font-size: 0.85rem; color: #666;">Modules</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #667eea;"><?php echo $programmes_count; ?></div>
                    <div style="font-size: 0.85rem; color: #666;">Programmes</div>
                </div>
            </div>
            
            <div style="text-align: left;">
                <p><i class="fas fa-envelope" style="width: 25px; color: #667eea;"></i> <?php echo htmlspecialchars($staff['Email'] ?? 'Not provided'); ?></p>
                <p><i class="fas fa-building" style="width: 25px; color: #667eea;"></i> <?php echo htmlspecialchars($staff['Department'] ?? 'Not specified'); ?></p>
                <p><i class="fas fa-id-badge" style="width: 25px; color: #667eea;"></i> Staff ID: #<?php echo $staff['StaffID']; ?></p>
                <?php if ($login): ?>
                    <p><i class="fas fa-user" style="width: 25px; color: #667eea;"></i> Username: <?php echo htmlspecialchars($login['Username']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Detailed Info -->
    <div>
        <!-- Biography -->
        <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h3 style="margin-bottom: 15px;"><i class="fas fa-quote-right" style="color: #667eea;"></i> Biography</h3>
            <?php if (!empty($staff['Bio'])): ?>
                <p style="line-height: 1.8; color: #555;"><?php echo nl2br(htmlspecialchars($staff['Bio'])); ?></p>
            <?php else: ?>
                <p style="color: #999; font-style: italic;">No biography provided.</p>
            <?php endif; ?>
        </div>
        
        <!-- Account Information -->
        <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-lock" style="color: #667eea;"></i> Account Information</h3>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; width: 150px;">Username:</td>
                    <td><?php echo htmlspecialchars($login['Username'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Email:</td>
                    <td><?php echo htmlspecialchars($login['Email'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Role:</td>
                    <td>
                        <span class="role-badge" style="background: #e3f2fd; color: #1976d2; padding: 5px 15px; border-radius: 20px;">
                            <?php echo ucfirst($login['Role'] ?? 'staff'); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Account Created:</td>
                    <td><?php echo isset($login['CreatedAt']) ? date('d/m/Y H:i', strtotime($login['CreatedAt'])) : 'N/A'; ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Recent Activity -->
        <?php if (mysqli_num_rows($recent_result) > 0): ?>
        <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-history" style="color: #667eea;"></i> Recent Activity</h3>
            <div style="max-height: 300px; overflow-y: auto;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($activity = mysqli_fetch_assoc($recent_result)): ?>
                        <tr>
                            <td>
                                <?php if ($activity['type'] == 'module'): ?>
                                    <span style="background: #e3f2fd; color: #1976d2; padding: 3px 10px; border-radius: 15px; font-size: 0.75rem;">
                                        <i class="fas fa-cube"></i> Module
                                    </span>
                                <?php else: ?>
                                    <span style="background: #fff3cd; color: #856404; padding: 3px 10px; border-radius: 15px; font-size: 0.75rem;">
                                        <i class="fas fa-book"></i> Programme
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($activity['name']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($activity['Status']); ?>">
                                    <?php echo ucfirst($activity['Status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($activity['date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.role-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}
.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-active {
    background: #d4edda;
    color: #155724;
}
.status-inactive {
    background: #f8d7da;
    color: #721c24;
}
.status-published {
    background: #d4edda;
    color: #155724;
}
.status-draft {
    background: #fff3cd;
    color: #856404;
}
</style>

<?php include '../includes/footer.php'; ?>