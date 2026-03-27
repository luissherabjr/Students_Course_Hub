<?php
require_once '../includes/config.php';

// Check if user is logged in and is staff
if (!isLoggedIn() || !hasRole('staff')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$page_title = 'Programme Students';
$staff_id = $_SESSION['staff_id'] ?? 0;
$programme_id = isset($_GET['programme_id']) ? (int)$_GET['programme_id'] : 0;

if ($staff_id == 0 || $programme_id == 0) {
    header('Location: ' . BASE_URL . 'staff/my-programmes.php');
    exit();
}

// Verify this programme belongs to the staff
$check_query = "SELECT p.ProgrammeID, p.ProgrammeName, l.LevelName 
                FROM Programmes p
                LEFT JOIN Levels l ON p.LevelID = l.LevelID
                WHERE p.ProgrammeID = $programme_id AND p.ProgrammeLeaderID = $staff_id";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = 'You do not have permission to view this programme';
    header('Location: ' . BASE_URL . 'staff/my-programmes.php');
    exit();
}

$programme = mysqli_fetch_assoc($check_result);

include '../includes/header.php';

// Get students interested in this programme
$students_query = "SELECT i.*, 
                          (SELECT COUNT(*) FROM ProgrammeModules pm 
                           JOIN Modules m ON pm.ModuleID = m.ModuleID 
                           WHERE pm.ProgrammeID = i.ProgrammeID AND m.ModuleLeaderID = $staff_id) as taught_modules
                   FROM InterestedStudents i
                   WHERE i.ProgrammeID = $programme_id
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

// Get module statistics for this programme
$modules_query = "SELECT m.ModuleID, m.ModuleName, m.Status,
                         (SELECT COUNT(*) FROM ProgrammeModules WHERE ProgrammeID = $programme_id AND ModuleID = m.ModuleID) as in_programme
                  FROM Modules m
                  WHERE m.ModuleLeaderID = $staff_id
                  ORDER BY m.ModuleName";
$modules_result = mysqli_query($conn, $modules_query);

// Get monthly registration trend
$trend_query = "SELECT DATE_FORMAT(RegisteredAt, '%Y-%m') as month, COUNT(*) as count
                FROM InterestedStudents
                WHERE ProgrammeID = $programme_id
                GROUP BY DATE_FORMAT(RegisteredAt, '%Y-%m')
                ORDER BY month DESC
                LIMIT 6";
$trend_result = mysqli_query($conn, $trend_query);

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

<div class="dashboard-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1><i class="fas fa-users"></i> Interested Students</h1>
        <p style="color: #666; margin-top: 5px;">
            Programme: <strong><?php echo htmlspecialchars($programme['ProgrammeName']); ?></strong> 
            (<?php echo htmlspecialchars($programme['LevelName'] ?? 'N/A'); ?>)
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="my-programmes.php" class="view-btn" style="background: #6c757d;">
            <i class="fas fa-arrow-left"></i> Back to Programmes
        </a>
        <?php if ($total_students > 0): ?>
        <a href="export-programme-students.php?programme_id=<?php echo $programme_id; ?>" class="add-btn">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <?php endif; ?>
    </div>
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

<!-- Registration Trend Chart (Simple Stats) -->
<?php if (mysqli_num_rows($trend_result) > 0): ?>
<div class="table-container" style="margin-bottom: 30px;">
    <div class="table-header">
        <h2><i class="fas fa-chart-line"></i> Registration Trend (Last 6 Months)</h2>
    </div>
    <div style="padding: 20px;">
        <div style="display: flex; gap: 20px; justify-content: space-around; flex-wrap: wrap;">
            <?php while ($trend = mysqli_fetch_assoc($trend_result)): ?>
                <div style="text-align: center; min-width: 100px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #667eea;"><?php echo $trend['count']; ?></div>
                    <div style="color: #666; font-size: 0.9rem;"><?php echo date('M Y', strtotime($trend['month'] . '-01')); ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Students List -->
<?php if ($total_students > 0): ?>
<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> Students Interested in this Programme</h2>
        <div style="display: flex; gap: 10px;">
            <select onchange="filterStudents(this.value)" class="filter-select">
                <option value="all">All Students</option>
                <option value="active">Active Only</option>
                <option value="withdrawn">Withdrawn Only</option>
            </select>
            <span class="status-badge" style="background: #17a2b8; color: white;">
                <i class="fas fa-info-circle"></i> Click email to contact
            </span>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Modules You Teach</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                <tr data-status="<?php echo $student['Status']; ?>">
                    <td><strong><?php echo htmlspecialchars($student['StudentName']); ?></strong></td>
                    <td>
                        <a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>" class="email-link" title="Send Email">
                            <?php echo htmlspecialchars($student['Email']); ?>
                        </a>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($student['RegisteredAt'])); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($student['Status']); ?>">
                            <?php echo ucfirst($student['Status']); ?>
                        </span>
                        <?php if ($student['WithdrawnAt']): ?>
                            <br><small class="withdrawn-date">Withdrawn: <?php echo date('d/m/Y', strtotime($student['WithdrawnAt'])); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($student['taught_modules'] > 0): ?>
                            <span class="badge" style="background: #17a2b8; color: white;">
                                <i class="fas fa-check-circle"></i> <?php echo $student['taught_modules']; ?> module(s)
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background: #6c757d; color: white;">
                                <i class="fas fa-times-circle"></i> Not in your modules
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="action-btns">
                        <a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>" class="view-btn" title="Send Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Summary by Module -->
<?php if (mysqli_num_rows($modules_result) > 0): ?>
<div class="table-container" style="margin-top: 30px;">
    <div class="table-header">
        <h2><i class="fas fa-cube"></i> Your Modules in this Programme</h2>
        <span class="status-badge" style="background: #28a745; color: white;">
            <i class="fas fa-chart-line"></i> Teaching Impact
        </span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Status</th>
                    <th>Students in Programme</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($modules_result, 0);
                while ($module = mysqli_fetch_assoc($modules_result)): 
                    if ($module['in_programme'] > 0):
                        // Count students interested in this programme that would take this module
                        $module_students_query = "SELECT COUNT(*) as count 
                                                  FROM InterestedStudents i
                                                  WHERE i.ProgrammeID = $programme_id 
                                                  AND i.Status = 'active'";
                        $module_students_result = mysqli_query($conn, $module_students_query);
                        $module_students = mysqli_fetch_assoc($module_students_result);
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($module['ModuleName']); ?></strong></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($module['Status']); ?>">
                            <?php echo ucfirst($module['Status']); ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge" style="background: #17a2b8; color: white;">
                            <i class="fas fa-users"></i> <?php echo $module_students['count']; ?> potential students
                        </span>
                    </td>
                    <td class="action-btns">
                        <a href="module-students.php?module_id=<?php echo $module['ModuleID']; ?>" class="view-btn" title="View Students in this Module">
                            <i class="fas fa-users"></i> View Details
                        </a>
                    </td>
                </tr>
                <?php 
                    endif;
                endwhile; 
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Quick Export Options -->
<?php if ($total_students > 0): ?>
<div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
    <a href="export-programme-students.php?programme_id=<?php echo $programme_id; ?>" class="add-btn">
        <i class="fas fa-file-csv"></i> Export All Students as CSV
    </a>
</div>
<?php endif; ?>

<?php else: ?>
<div class="table-container">
    <div style="text-align: center; padding: 60px;">
        <i class="fas fa-users" style="font-size: 5rem; color: #ccc; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">No Interested Students</h3>
        <p style="color: #999;">No students have shown interest in this programme yet.</p>
        <a href="my-programmes.php" class="view-btn" style="margin-top: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Programmes
        </a>
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

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Search students...';
    searchInput.style.padding = '8px 12px';
    searchInput.style.border = '1px solid #e0e0e0';
    searchInput.style.borderRadius = '5px';
    searchInput.style.marginLeft = '10px';
    
    const filterSelect = document.querySelector('.filter-select');
    if (filterSelect) {
        filterSelect.parentNode.appendChild(searchInput);
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTable tbody tr');
            
            rows.forEach(row => {
                const studentName = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<style>
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.status-active {
    background: #d4edda;
    color: #155724;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.status-withdrawn {
    background: #f8d7da;
    color: #721c24;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.status-published {
    background: #d4edda;
    color: #155724;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.status-draft {
    background: #fff3cd;
    color: #856404;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.email-link {
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s;
}

.email-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

.withdrawn-date {
    color: #856404;
    font-size: 0.7rem;
    display: block;
    margin-top: 3px;
}

.action-btns {
    display: flex;
    gap: 5px;
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

.add-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.add-btn:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
</style>

<?php include '../includes/footer.php'; ?>