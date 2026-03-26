<?php
require_once '../includes/config.php';

// Check if user is logged in and is staff
if (!isLoggedIn() || !hasRole('staff')) {
    header('Location: ' . BASE_URL . 'auth.login.php');
    exit();
}

$page_title = 'Module Students';
$staff_id = $_SESSION['staff_id'] ?? 0;
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

if ($staff_id == 0 || $module_id == 0) {
    header('Location: ' . BASE_URL . 'staff/my-modules.php');
    exit();
}

// Verify this module belongs to the staff
$check_query = "SELECT ModuleID, ModuleName FROM Modules WHERE ModuleID = $module_id AND ModuleLeaderID = $staff_id";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = 'You do not have permission to view this module';
    header('Location: ' . BASE_URL . 'staff/my-modules.php');
    exit();
}

$module = mysqli_fetch_assoc($check_result);

include '../includes/header.php';

// Get students interested in programmes that include this module
$students_query = "SELECT DISTINCT i.*, p.ProgrammeName, l.LevelName, pm.Year
                   FROM InterestedStudents i
                   JOIN ProgrammeModules pm ON i.ProgrammeID = pm.ProgrammeID
                   JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
                   JOIN Levels l ON p.LevelID = l.LevelID
                   WHERE pm.ModuleID = $module_id
                   ORDER BY i.RegisteredAt DESC";
$students_result = mysqli_query($conn, $students_query);

// Get statistics
$total_students = mysqli_num_rows($students_result);
$active_students = 0;
$withdrawn_students = 0;

while ($student = mysqli_fetch_assoc($students_result)) {
    if ($student['Status'] == 'active') {
        $active_students++;
    } else {
        $withdrawn_students++;
    }
}
mysqli_data_seek($students_result, 0);
?>

<div class="dashboard-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1><i class="fas fa-users"></i> Interested Students</h1>
        <p style="color: #666; margin-top: 5px;">
            Module: <strong><?php echo htmlspecialchars($module['ModuleName']); ?></strong>
        </p>
    </div>
    <a href="my-modules.php" class="view-btn" style="background: #6c757d;">
        <i class="fas fa-arrow-left"></i> Back to Modules
    </a>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid" style="margin-bottom: 30px;">
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="card-number"><?php echo $total_students; ?></div>
        <div class="card-label">Total Students</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-check-circle" style="color: #28a745;"></i>
        </div>
        <div class="card-number"><?php echo $active_students; ?></div>
        <div class="card-label">Active Interest</div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-times-circle" style="color: #dc3545;"></i>
        </div>
        <div class="card-number"><?php echo $withdrawn_students; ?></div>
        <div class="card-label">Withdrawn</div>
    </div>
</div>

<!-- Students List -->
<?php if ($total_students > 0): ?>
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> Students Interested in this Module</h2>
        <div style="display: flex; gap: 10px;">
            <select onchange="filterStudents(this.value)" class="filter-select">
                <option value="all">All Students</option>
                <option value="active">Active Only</option>
                <option value="withdrawn">Withdrawn Only</option>
            </select>
            <a href="export-module-students.php?module_id=<?php echo $module_id; ?>" class="add-btn">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Programme</th>
                    <th>Level</th>
                    <th>Year</th>
                    <th>Registered</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                <tr data-status="<?php echo $student['Status']; ?>">
                    <td><strong><?php echo htmlspecialchars($student['StudentName']); ?></strong></td>
                    <td>
                        <a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>">
                            <?php echo htmlspecialchars($student['Email']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($student['ProgrammeName']); ?></td>
                    <td><?php echo htmlspecialchars($student['LevelName']); ?></td>
                    <td>Year <?php echo $student['Year']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($student['RegisteredAt'])); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($student['Status']); ?>">
                            <?php echo ucfirst($student['Status']); ?>
                        </span>
                        <?php if ($student['WithdrawnAt']): ?>
                            <br><small>Withdrawn: <?php echo date('d/m/Y', strtotime($student['WithdrawnAt'])); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<div class="table-container">
    <div style="text-align: center; padding: 60px;">
        <i class="fas fa-users" style="font-size: 5rem; color: #ccc; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">No Interested Students</h3>
        <p style="color: #999;">No students have shown interest in this module yet.</p>
    </div>
</div>
<?php endif; ?>

<script>
function filterStudents(status) {
    const rows = document.querySelectorAll('#studentsTable tbody tr');
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
}
</script>

<style>
.status-badge.status-active {
    background: #d4edda;
    color: #155724;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
}

.status-badge.status-withdrawn {
    background: #f8d7da;
    color: #721c24;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
}
</style>

<?php include '../includes/footer.php'; ?>