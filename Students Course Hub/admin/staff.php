<?php
$pageTitle = "Manage Staff - Admin";
require_once '../includes/config.php';
include 'admin-header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <!-- Same sidebar -->
    </aside>
    
    <main class="admin-content">
        <div class="content-header">
            <h1>Manage Staff</h1>
            <a href="staff-edit.php" class="btn btn-primary">+ Add New Staff</a>
        </div>
        
        <!-- Staff List -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Programmes</th>
                    <th>Modules</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong>Dr. Sarah Johnson</strong>
                        <br>
                        <small>s.johnson@university.ac.uk</small>
                    </td>
                    <td>Senior Lecturer</td>
                    <td>Computer Science</td>
                    <td>Programme Leader</td>
                    <td>BSc Computer Science</td>
                    <td>CS301, CS401</td>
                    <td>
                        <a href="staff-edit.php?id=1" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-danger">Remove</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>
                        <strong>Prof. Michael Chen</strong>
                        <br>
                        <small>m.chen@university.ac.uk</small>
                    </td>
                    <td>Professor</td>
                    <td>Computer Science</td>
                    <td>Module Leader</td>
                    <td>BSc Computer Science, BSc Cyber Security</td>
                    <td>CS101, CS201, CS301</td>
                    <td>
                        <a href="staff-edit.php?id=2" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-danger">Remove</a>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Staff Module Assignment -->
        <div class="assignment-section">
            <h2>Module Leader Assignments</h2>
            
            <div class="assignment-grid">
                <div class="assignment-card">
                    <h4>CS101 - Programming Fundamentals</h4>
                    <p>Current Leader: <strong>Prof. Michael Chen</strong></p>
                    <button class="btn-small" onclick="reassignModule(101)">Reassign</button>
                </div>
                
                <div class="assignment-card">
                    <h4>CS201 - Object-Oriented Programming</h4>
                    <p>Current Leader: <strong>Dr. Sarah Johnson</strong></p>
                    <button class="btn-small" onclick="reassignModule(201)">Reassign</button>
                </div>
                
                <div class="assignment-card">
                    <h4>CS301 - Software Engineering</h4>
                    <p>Current Leader: <strong>Prof. Michael Chen</strong></p>
                    <button class="btn-small" onclick="reassignModule(301)">Reassign</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function reassignModule(moduleId) {
    // Show reassignment modal
    alert(`Reassign module ${moduleId} (demo)`);
}
</script>

<?php include 'admin-footer.php'; ?>