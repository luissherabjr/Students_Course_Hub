<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Programme Modules';

// Get programme ID from URL
$programme_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($programme_id == 0) {
    header('Location: ' . BASE_PATH . 'admin/programmes.php');
    exit();
}

// Get programme details with Level and Leader info
$prog_query = "SELECT p.*, l.LevelName, s.Name as leader_name, s.Title as leader_title
               FROM Programmes p
               LEFT JOIN Levels l ON p.LevelID = l.LevelID
               LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
               WHERE p.ProgrammeID = $programme_id";
$prog_result = mysqli_query($conn, $prog_query);

if (mysqli_num_rows($prog_result) == 0) {
    header('Location: ' . BASE_PATH . 'admin/programmes.php');
    exit();
}

$programme = mysqli_fetch_assoc($prog_result);

// Handle module assignment removal
if (isset($_GET['remove']) && isset($_GET['module_id'])) {
    $module_id = (int)$_GET['module_id'];
    $delete = mysqli_query($conn, "DELETE FROM ProgrammeModules WHERE ProgrammeID = $programme_id AND ModuleID = $module_id");
    if ($delete) {
        $_SESSION['message'] = 'Module removed from programme';
    } else {
        $_SESSION['error'] = 'Error removing module';
    }
    header('Location: ' . BASE_PATH . 'admin/programme-modules.php?id=' . $programme_id);
    exit();
}

include '../includes/header.php';

// Get modules already in this programme with all details
$modules_query = "SELECT pm.*, m.ModuleName, m.ModuleID, m.Description, m.Status as module_status,
                         s.Name as leader_name, s.Title as leader_title
                  FROM ProgrammeModules pm
                  JOIN Modules m ON pm.ModuleID = m.ModuleID
                  LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                  WHERE pm.ProgrammeID = $programme_id
                  ORDER BY pm.Year, m.ModuleName";
$modules_result = mysqli_query($conn, $modules_query);

// Get available modules not in this programme
$available_query = "SELECT m.*, s.Name as leader_name, s.Title as leader_title
                   FROM Modules m
                   LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                   WHERE m.ModuleID NOT IN (
                       SELECT ModuleID FROM ProgrammeModules WHERE ProgrammeID = $programme_id
                   ) AND m.Status = 'active'
                   ORDER BY m.ModuleName";
$available_result = mysqli_query($conn, $available_query);

// Count modules per year
$year_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
$year_count_query = "SELECT Year, COUNT(*) as count FROM ProgrammeModules WHERE ProgrammeID = $programme_id GROUP BY Year";
$year_count_result = mysqli_query($conn, $year_count_query);
while ($yc = mysqli_fetch_assoc($year_count_result)) {
    $year_counts[$yc['Year']] = $yc['count'];
}
?>

<div class="table-container">
    <div class="table-header" style="flex-direction: column; align-items: flex-start; gap: 10px;">
        <div style="display: flex; justify-content: space-between; width: 100%;">
            <h1>
                <i class="fas fa-cubes"></i> 
                Programme Structure: <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
            </h1>
            <div style="display: flex; gap: 10px;">
                <a href="<?php echo BASE_PATH; ?>admin/programmes.php" class="view-btn">
                    <i class="fas fa-arrow-left"></i> Back to Programmes
                </a>
                <?php if (mysqli_num_rows($available_result) > 0): ?>
                    <button class="add-btn" onclick="openModal('addModuleModal')">
                        <i class="fas fa-plus"></i> Add Module
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div style="background: #e9ecef; padding: 15px; border-radius: 8px; width: 100%;">
            <p><strong>Level:</strong> <?php echo htmlspecialchars($programme['LevelName']); ?></p>
            <p><strong>Programme Leader:</strong> <?php echo htmlspecialchars($programme['leader_name'] ?? 'Not assigned'); ?>
            <?php if (!empty($programme['leader_title'])): ?>
                <br><small><?php echo htmlspecialchars($programme['leader_title']); ?></small>
            <?php endif; ?>
            </p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-<?php echo strtolower($programme['Status']); ?>">
                    <?php echo ucfirst($programme['Status']); ?>
                </span>
            </p>
        </div>
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
    
    <!-- Module summary by year -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
        <?php for ($year = 1; $year <= 4; $year++): ?>
            <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center;">
                <h3 style="color: #667eea;">Year <?php echo $year; ?></h3>
                <p style="font-size: 2rem; font-weight: bold;"><?php echo $year_counts[$year]; ?></p>
                <p style="color: #666;">modules</p>
            </div>
        <?php endfor; ?>
    </div>
    
    <!-- Modules by Year -->
    <?php for ($year = 1; $year <= 4; $year++): ?>
        <div class="table-responsive" style="margin-top: 20px;">
            <h3 style="padding: 15px; background: #f8f9fa; margin: 0; border: 1px solid #e0e0e0; border-bottom: none;">
                <i class="fas fa-calendar-alt" style="color: #667eea;"></i> Year <?php echo $year; ?> Modules
                <span style="float: right; background: #667eea; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.9rem;">
                    <?php echo $year_counts[$year]; ?> modules
                </span>
            </h3>
            <table style="border-top: none;">
                <thead>
                    <tr>
                        <th>Module Code</th>
                        <th>Module Name</th>
                        <th>Module Leader</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $year_has_modules = false;
                    mysqli_data_seek($modules_result, 0);
                    while ($module = mysqli_fetch_assoc($modules_result)): 
                        if ($module['Year'] == $year):
                            $year_has_modules = true;
                    ?>
                        <tr>
                            <td><strong>MOD<?php echo str_pad($module['ModuleID'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo htmlspecialchars($module['ModuleName']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($module['leader_name'] ?? 'Not assigned'); ?>
                                <?php if (!empty($module['leader_title'])): ?>
                                    <br><small><?php echo htmlspecialchars($module['leader_title']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small><?php echo substr(htmlspecialchars($module['Description']), 0, 100); ?>...</small></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($module['module_status']); ?>">
                                    <?php echo ucfirst($module['module_status']); ?>
                                </span>
                            </td>
                            <td class="action-btns">
                                <a href="<?php echo BASE_PATH; ?>admin/edit-module.php?id=<?php echo $module['ModuleID']; ?>" class="edit-btn" title="Edit Module">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?id=<?php echo $programme_id; ?>&remove=1&module_id=<?php echo $module['ModuleID']; ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Remove this module from Year <?php echo $year; ?>?')"
                                   title="Remove from programme">
                                    <i class="fas fa-times"></i>
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endif;
                    endwhile; 
                    if (!$year_has_modules):
                    ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                <i class="fas fa-info-circle"></i> No modules assigned to Year <?php echo $year; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endfor; ?>
</div>

<!-- Add Module Modal -->
<div id="addModuleModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <span class="close" onclick="closeModal('addModuleModal')">&times;</span>
        <h3><i class="fas fa-plus-circle"></i> Add Module to <?php echo htmlspecialchars($programme['ProgrammeName']); ?></h3>
        
        <?php if (mysqli_num_rows($available_result) > 0): ?>
            <form method="POST" action="<?php echo BASE_PATH; ?>admin/process-programme-module.php">
                <input type="hidden" name="programme_id" value="<?php echo $programme_id; ?>">
                
                <div class="form-group">
                    <label for="module_id">Select Module <span style="color: red;">*</span></label>
                    <select id="module_id" name="module_id" required>
                        <option value="">Choose a module...</option>
                        <?php while ($module = mysqli_fetch_assoc($available_result)): ?>
                            <option value="<?php echo $module['ModuleID']; ?>">
                                <?php echo htmlspecialchars($module['ModuleName']); ?> 
                                (Leader: <?php echo htmlspecialchars($module['leader_name'] ?? 'Not assigned'); ?>)
                                <?php if (!empty($module['leader_title'])): ?>
                                    - <?php echo htmlspecialchars($module['leader_title']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="year">Year of Study <span style="color: red;">*</span></label>
                    <select id="year" name="year" required>
                        <option value="">Select Year</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                </div>
                
                <button type="submit" name="action" value="add" class="submit-btn">
                    <i class="fas fa-plus"></i> Add Module to Programme
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-check-circle" style="font-size: 4rem; color: #28a745;"></i>
                <p style="margin-top: 20px; font-size: 1.1rem;">All available modules are already assigned to this programme!</p>
                <p>You can create new modules in the <a href="<?php echo BASE_PATH; ?>admin/modules.php">Modules Management</a> page.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>