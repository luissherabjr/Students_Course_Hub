<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/loginphp');
    exit();
}

$page_title = 'Assign Module to Programme';

// Get module ID from URL
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

if ($module_id == 0) {
    header('Location: ' . BASE_PATH . 'admin/modules.php');
    exit();
}

// Get module details
$module_query = "SELECT m.*, s.Name as leader_name, s.Title as leader_title
                 FROM Modules m
                 LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                 WHERE m.ModuleID = $module_id";
$module_result = mysqli_query($conn, $module_query);

if (mysqli_num_rows($module_result) == 0) {
    header('Location: ' . BASE_PATH . 'admin/modules.php');
    exit();
}

$module = mysqli_fetch_assoc($module_result);

include '../includes/header.php';

// Get all programmes that don't already have this module
$programmes_query = "SELECT p.*, l.LevelName,
                            (SELECT Year FROM ProgrammeModules WHERE ProgrammeID = p.ProgrammeID AND ModuleID = $module_id) as already_assigned
                     FROM Programmes p
                     LEFT JOIN Levels l ON p.LevelID = l.LevelID
                     WHERE p.Status = 'published'
                     ORDER BY p.ProgrammeName";
$programmes_result = mysqli_query($conn, $programmes_query);
?>

<div class="form-container" style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h1 style="margin-bottom: 30px;">
        <i class="fas fa-link"></i> 
        Assign Module to Programme
    </h1>
    
    <div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <h3><?php echo htmlspecialchars($module['ModuleName']); ?></h3>
        <p><strong>Module Code:</strong> MOD<?php echo str_pad($module['ModuleID'], 3, '0', STR_PAD_LEFT); ?></p>
        <p><strong>Module Leader:</strong> <?php echo htmlspecialchars($module['leader_name'] ?? 'Not assigned'); ?>
        <?php if (!empty($module['leader_title'])): ?>
            <br><small><?php echo htmlspecialchars($module['leader_title']); ?></small>
        <?php endif; ?>
        </p>
        <p><strong>Status:</strong> <span class="status-badge status-<?php echo strtolower($module['Status']); ?>"><?php echo ucfirst($module['Status']); ?></span></p>
    </div>
    
    <form method="POST" action="<?php echo BASE_PATH; ?>admin/process-assign-module.php">
        <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
        
        <div class="form-group">
            <label for="programme_id">Select Programme <span style="color: red;">*</span></label>
            <select id="programme_id" name="programme_id" required>
                <option value="">Choose a programme...</option>
                <?php while ($prog = mysqli_fetch_assoc($programmes_result)): ?>
                    <option value="<?php echo $prog['ProgrammeID']; ?>" 
                        <?php echo $prog['already_assigned'] ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($prog['ProgrammeName']); ?> 
                        (<?php echo htmlspecialchars($prog['LevelName']); ?>)
                        <?php if ($prog['already_assigned']): ?>
                            - Already assigned (Year <?php echo $prog['already_assigned']; ?>)
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
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" name="action" value="assign" class="submit-btn">
                <i class="fas fa-link"></i> Assign Module to Programme
            </button>
            <a href="<?php echo BASE_PATH; ?>admin/modules.php" class="view-btn" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>