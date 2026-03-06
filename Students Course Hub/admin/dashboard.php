<?php
$pageTitle = "Admin Dashboard - Student Course Hub";
require_once '../includes/config.php';
include 'admin-header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h2>Student Course Hub</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
        </div>
        <nav class="admin-nav">
            <ul>
                <li><a href="../admin/dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="../admin/programmes.php">📚 Programmes</a></li>
                <li><a href="../admin/modules.php">📖 Modules</a></li>
                <li><a href="../admin/staff.php">👥 Staff</a></li>
                <li><a href="../admin/mailing.php">📧 Mailing Lists</a></li>
                <li><a href="../admin/logout.php">🚪 Logout</a></li>
            </ul>
        </nav>
    </aside>
    
    <main class="admin-content">
        <div class="content-header">
            <h1>Dashboard</h1>
            <div class="date-display">
                <?php echo date('l, F j, Y'); ?>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-details">
                    <h3>Total Programmes</h3>
                    <p class="stat-number">24</p>
                    <span class="stat-trend positive">↑ 3 this month</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-details">
                    <h3>Total Modules</h3>
                    <p class="stat-number">156</p>
                    <span class="stat-trend positive">↑ 12 this month</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <h3>Staff Members</h3>
                    <p class="stat-number">42</p>
                    <span class="stat-trend neutral">→ No change</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📧</div>
                <div class="stat-details">
                    <h3>Interested Students</h3>
                    <p class="stat-number">347</p>
                    <span class="stat-trend positive">↑ 28 this week</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="programme-edit.php" class="action-btn">
                    <span class="action-icon">➕</span>
                    Add Programme
                </a>
                <a href="module-edit.php" class="action-btn">
                    <span class="action-icon">📝</span>
                    Add Module
                </a>
                <a href="staff-edit.php" class="action-btn">
                    <span class="action-icon">👤</span>
                    Add Staff
                </a>
                <a href="mailing-list.php?export=csv" class="action-btn">
                    <span class="action-icon">📊</span>
                    Export List
                </a>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2>Recent Interest Registrations</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Programme</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025-02-09</td>
                        <td>John Smith</td>
                        <td>john.s@email.com</td>
                        <td>BSc Computer Science</td>
                        <td><span class="badge badge-new">New</span></td>
                        <td>
                            <a href="mailing-list.php?view=1" class="btn-small">View</a>
                            <a href="#" onclick="removeInterest(1)" class="btn-small btn-danger">Remove</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2025-02-08</td>
                        <td>Emma Wilson</td>
                        <td>emma.w@email.com</td>
                        <td>MSc Data Science</td>
                        <td><span class="badge badge-contacted">Contacted</span></td>
                        <td>
                            <a href="mailing-list.php?view=2" class="btn-small">View</a>
                            <a href="#" onclick="removeInterest(2)" class="btn-small btn-danger">Remove</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2025-02-07</td>
                        <td>Michael Brown</td>
                        <td>michael.b@email.com</td>
                        <td>BSc Cyber Security</td>
                        <td><span class="badge badge-new">New</span></td>
                        <td>
                            <a href="mailing-list.php?view=3" class="btn-small">View</a>
                            <a href="#" onclick="removeInterest(3)" class="btn-small btn-danger">Remove</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <a href="mailing-list.php" class="view-all-link">View all registrations →</a>
        </div>
        
        <!-- Programme Status -->
        <div class="programme-status">
            <h2>Programme Publication Status</h2>
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label">Published:</span>
                    <span class="status-value">18</span>
                    <span class="status-bar" style="width: 75%"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Draft:</span>
                    <span class="status-value">6</span>
                    <span class="status-bar" style="width: 25%"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Undergraduate:</span>
                    <span class="status-value">15</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Postgraduate:</span>
                    <span class="status-value">9</span>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Additional Dashboard Styles */
.admin-sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.admin-sidebar-header h2 {
    margin: 0;
    font-size: 1.2rem;
}

.admin-sidebar-header p {
    margin: 5px 0 0;
    font-size: 0.9rem;
    opacity: 0.8;
}

.stat-icon {
    font-size: 2.5rem;
    margin-right: 15px;
}

.stat-details {
    flex: 1;
}

.stat-trend {
    font-size: 0.8rem;
    display: block;
    margin-top: 5px;
}

.stat-trend.positive {
    color: #27ae60;
}

.stat-trend.negative {
    color: #e74c3c;
}

.stat-trend.neutral {
    color: #f39c12;
}

.quick-actions {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.action-btn {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.action-btn:hover {
    background: var(--secondary-color);
    color: white;
    transform: translateY(-2px);
}

.action-icon {
    font-size: 1.5rem;
    margin-right: 10px;
}

.recent-activity, .programme-status {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.view-all-link {
    display: inline-block;
    margin-top: 15px;
    color: var(--secondary-color);
    text-decoration: none;
}

.status-grid {
    margin-top: 15px;
}

.status-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    position: relative;
}

.status-label {
    width: 120px;
}

.status-value {
    width: 40px;
    font-weight: bold;
}

.status-bar {
    height: 8px;
    background: var(--secondary-color);
    border-radius: 4px;
    margin-left: 10px;
}
</style>

<script>
function removeInterest(id) {
    if (confirm('Remove this student from the mailing list?')) {
        alert('Student removed (demo)');
        // In real implementation, this would make an AJAX call
    }
}
</script>

<?php include 'admin-footer.php'; ?>