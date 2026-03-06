<?php
$pageTitle = "Manage Programmes - Admin";
require_once '../includes/config.php';
include 'admin-header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <!-- Same sidebar as dashboard -->
        <nav class="admin-nav">
            <h2>Admin Menu</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="programmes.php" class="active">Manage Programmes</a></li>
                <li><a href="modules.php">Manage Modules</a></li>
                <li><a href="staff.php">Manage Staff</a></li>
                <li><a href="mailing-list.php">Mailing Lists</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>
    
    <main class="admin-content">
        <div class="content-header">
            <h1>Manage Programmes</h1>
            <a href="programme-edit.php" class="btn btn-primary">+ Add New Programme</a>
        </div>
        
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="published">Published</button>
            <button class="filter-tab" data-filter="unpublished">Unpublished</button>
            <button class="filter-tab" data-filter="undergraduate">Undergraduate</button>
            <button class="filter-tab" data-filter="postgraduate">Postgraduate</button>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Programme Name</th>
                    <th>Level</th>
                    <th>Modules</th>
                    <th>Status</th>
                    <th>Interested</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong>BSc Computer Science</strong>
                        <br>
                        <small>UCAS: G400</small>
                    </td>
                    <td><span class="badge undergraduate">Undergraduate</span></td>
                    <td>20</td>
                    <td>
                        <span class="badge badge-success">Published</span>
                        <br>
                        <small>Visible to students</small>
                    </td>
                    <td>45</td>
                    <td>2025-02-01</td>
                    <td class="actions">
                        <a href="programme-edit.php?id=1" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-warning" onclick="togglePublish(1)">Unpublish</a>
                        <a href="#" class="btn-small btn-danger" onclick="deleteProgramme(1)">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>
                        <strong>MSc Data Science</strong>
                        <br>
                        <small>UCAS: G401</small>
                    </td>
                    <td><span class="badge postgraduate">Postgraduate</span></td>
                    <td>8</td>
                    <td>
                        <span class="badge badge-warning">Draft</span>
                        <br>
                        <small>Hidden from students</small>
                    </td>
                    <td>0</td>
                    <td>2025-02-05</td>
                    <td class="actions">
                        <a href="programme-edit.php?id=2" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-success" onclick="publishProgramme(2)">Publish</a>
                        <a href="#" class="btn-small btn-danger" onclick="deleteProgramme(2)">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action">
                <option value="">Bulk Actions</option>
                <option value="publish">Publish</option>
                <option value="unpublish">Unpublish</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary">Apply</button>
        </div>
    </main>
</div>

<script>
function togglePublish(id) {
    if (confirm('Are you sure you want to unpublish this programme?')) {
        // AJAX call to toggle publish status
        alert('Programme unpublished (demo)');
    }
}

function deleteProgramme(id) {
    if (confirm('Are you sure you want to delete this programme? This action cannot be undone.')) {
        // AJAX call to delete
        alert('Programme deleted (demo)');
    }
}

function publishProgramme(id) {
    if (confirm('Publish this programme?')) {
        // AJAX call to publish
        alert('Programme published (demo)');
    }
}
</script>

<?php include 'admin-footer.php'; ?>