<?php
$pageTitle = "Staff Directory - Student Course Hub";
require_once 'config.php';
include 'includes/header.php';
?>

<div class="container">
    <h1>Our Academic Staff</h1>
    
    <div class="staff-directory">
        <!-- Staff member view - what modules they lead -->
        <div class="staff-profile-detailed">
            <h2>Dr. Sarah Johnson</h2>
            <p class="staff-title">Senior Lecturer in Computer Science</p>
            
            <div class="staff-responsibilities">
                <h3>Programme Leadership</h3>
                <ul>
                    <li><a href="programme-detail.php?id=1">BSc Computer Science</a> - Programme Leader</li>
                </ul>
                
                <h3>Module Leadership</h3>
                <ul>
                    <li>
                        <a href="programme-detail.php?id=1#module-CS301">Software Engineering (CS301)</a>
                        <br>
                        <small>BSc Computer Science - Year 3</small>
                    </li>
                    <li>
                        <a href="programme-detail.php?id=1#module-CS401">Advanced Programming (CS401)</a>
                        <br>
                        <small>BSc Computer Science - Year 4</small>
                    </li>
                </ul>
                
                <h3>Teaching on Other Programmes</h3>
                <ul>
                    <li>
                        <a href="programme-detail.php?id=2">BSc Cyber Security</a> - 
                        Contributes to CS301 module
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>