<?php
$pageTitle = "Manage Modules - Admin";
require_once '../includes/config.php';
include 'admin-header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <!-- Same sidebar -->
    </aside>
    
    <main class="admin-content">
        <div class="content-header">
            <h1>Manage Modules</h1>
            <a href="module-edit.php" class="btn btn-primary">+ Add New Module</a>
        </div>
        
        <!-- Filter by Programme -->
        <div class="filter-section">
            <label for="filter-programme">Filter by Programme:</label>
            <select id="filter-programme" class="form-control" style="width: auto;">
                <option value="">All Programmes</option>
                <option value="1">BSc Computer Science</option>
                <option value="2">BSc Cyber Security</option>
                <option value="3">MSc Data Science</option>
            </select>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Module Name</th>
                    <th>Programme</th>
                    <th>Year</th>
                    <th>Module Leader</th>
                    <th>Credits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CS101</td>
                    <td><strong>Programming Fundamentals</strong></td>
                    <td>BSc Computer Science</td>
                    <td>Year 1</td>
                    <td>Prof. Michael Chen</td>
                    <td>20</td>
                    <td class="actions">
                        <a href="module-edit.php?id=1" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-danger">Remove</a>
                    </td>
                </tr>
                <tr>
                    <td>CS201</td>
                    <td><strong>Object-Oriented Programming</strong></td>
                    <td>BSc Computer Science</td>
                    <td>Year 2</td>
                    <td>Dr. Sarah Johnson</td>
                    <td>20</td>
                    <td class="actions">
                        <a href="module-edit.php?id=2" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-danger">Remove</a>
                    </td>
                </tr>
                <tr>
                    <td>CS301</td>
                    <td><strong>Software Engineering</strong></td>
                    <td>BSc Computer Science</td>
                    <td>Year 3</td>
                    <td>Prof. Michael Chen</td>
                    <td>30</td>
                    <td class="actions">
                        <a href="module-edit.php?id=3" class="btn-small">Edit</a>
                        <a href="#" class="btn-small btn-danger">Remove</a>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Module Assignment Matrix -->
        <div class="matrix-view">
            <h2>Module Assignment Matrix</h2>
            <p>See which modules are taught across multiple programmes</p>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>BSc Computer Science</th>
                        <th>BSc Cyber Security</th>
                        <th>MSc Data Science</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Programming Fundamentals</td>
                        <td>✅ Year 1</td>
                        <td>✅ Year 1</td>
                        <td>❌</td>
                    </tr>
                    <tr>
                        <td>Database Systems</td>
                        <td>✅ Year 2</td>
                        <td>✅ Year 2</td>
                        <td>✅ Core</td>
                    </tr>
                    <tr>
                        <td>Machine Learning</td>
                        <td>❌</td>
                        <td>❌</td>
                        <td>✅ Core</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include 'admin-footer.php'; ?>