<?php
$pageTitle = "Programme Details - Student Course Hub";
require_once '../includes/config.php';
include '../includes/header.php';

// Get programme ID from URL
$programme_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<div class="programme-details">
    <div class="container">
        <!-- Programme header -->
        <div class="programme-header">
            <div class="programme-header-content">
                <span class="programme-badge undergraduate">Undergraduate</span>
                <h1>BSc Computer Science</h1>
                <p class="programme-overview">
                    This programme provides a solid foundation in computer science principles,
                    preparing students for careers in software development, IT consulting, and research.
                </p>
            </div>
            <div class="programme-actions">
                <a href="../register-interest.php?programme_id=<?php echo $programme_id; ?>" class="btn btn-primary btn-large">
                    Register Interest
                </a>
            </div>
        </div>
        
        <!-- Programme tabs -->
        <div class="programme-tabs">
            <nav class="tab-navigation" aria-label="Programme information tabs">
                <button class="tab-link active" data-tab="overview">Overview</button>
                <button class="tab-link" data-tab="modules">Modules</button>
                <button class="tab-link" data-tab="staff">Staff</button>
                <button class="tab-link" data-tab="entry">Entry Requirements</button>
            </nav>
            
            <!-- Overview tab -->
            <div class="tab-panel active" id="overview">
                <div class="overview-content">
                    <h2>Programme Overview</h2>
                    <p>This degree programme offers a comprehensive education in computer science, combining theoretical knowledge with practical skills. Students will develop expertise in programming, algorithms, database design, and software engineering.</p>
                    
                    <h3>Key Features</h3>
                    <ul>
                        <li>Industry-focused curriculum</li>
                        <li>Hands-on programming projects</li>
                        <li>Optional placement year</li>
                        <li>Access to dedicated computing labs</li>
                    </ul>
                    
                    <h3>Career Opportunities</h3>
                    <p>Graduates can pursue careers as software developers, systems analysts, IT consultants, or progress to postgraduate study.</p>
                </div>
            </div>
            
            <!-- Modules tab -->
            <div class="tab-panel" id="modules">
                <div class="modules-content">
                    <h2>Programme Modules</h2>
                    
                    <div class="year-modules">
                        <h3>Year 1</h3>
                        <div class="module-list">
                            <div class="module-item">
                                <h4>Programming Fundamentals</h4>
                                <p>Introduction to programming concepts using Python</p>
                                <span class="module-code">CS101</span>
                            </div>
                            <div class="module-item">
                                <h4>Computer Systems</h4>
                                <p>Understanding computer architecture and operating systems</p>
                                <span class="module-code">CS102</span>
                            </div>
                            <div class="module-item">
                                <h4>Mathematics for Computing</h4>
                                <p>Discrete mathematics and mathematical thinking</p>
                                <span class="module-code">CS103</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="year-modules">
                        <h3>Year 2</h3>
                        <div class="module-list">
                            <div class="module-item">
                                <h4>Object-Oriented Programming</h4>
                                <p>Advanced programming with Java</p>
                                <span class="module-code">CS201</span>
                            </div>
                            <div class="module-item">
                                <h4>Database Systems</h4>
                                <p>Database design and SQL</p>
                                <span class="module-code">CS202</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="year-modules">
                        <h3>Year 3</h3>
                        <div class="module-list">
                            <div class="module-item">
                                <h4>Software Engineering</h4>
                                <p>Software development methodologies and project management</p>
                                <span class="module-code">CS301</span>
                            </div>
                            <div class="module-item">
                                <h4>Final Year Project</h4>
                                <p>Individual research and development project</p>
                                <span class="module-code">CS399</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Staff tab -->
            <div class="tab-panel" id="staff">
                <h2>Teaching Staff</h2>
                <div class="staff-grid">
                    <div class="staff-card">
                        <div class="staff-image">
                            <img src="../assets/images/placeholder.jpg" alt="Dr. Sarah Johnson">
                        </div>
                        <div class="staff-info">
                            <h3>Dr. Sarah Johnson</h3>
                            <p class="staff-role">Programme Leader</p>
                            <p>Expertise in Artificial Intelligence and Machine Learning</p>
                        </div>
                    </div>
                    
                    <div class="staff-card">
                        <div class="staff-image">
                            <img src="../assets/images/placeholder.jpg" alt="Prof. Michael Chen">
                        </div>
                        <div class="staff-info">
                            <h3>Prof. Michael Chen</h3>
                            <p class="staff-role">Module Leader - Programming</p>
                            <p>Specializes in Software Engineering and Programming Languages</p>
                        </div>
                    </div>
                    
                    <div class="staff-card">
                        <div class="staff-image">
                            <img src="../assets/images/placeholder.jpg" alt="Dr. Emily Williams">
                        </div>
                        <div class="staff-info">
                            <h3>Dr. Emily Williams</h3>
                            <p class="staff-role">Module Leader - Databases</p>
                            <p>Expert in Database Systems and Data Management</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Entry Requirements tab -->
            <div class="tab-panel" id="entry">
                <div class="entry-requirements">
                    <h2>Entry Requirements</h2>
                    
                    <div class="requirement-section">
                        <h3>A-Levels</h3>
                        <p>ABB including Mathematics or Computing</p>
                    </div>
                    
                    <div class="requirement-section">
                        <h3>International Baccalaureate</h3>
                        <p>32 points including Higher Level Mathematics</p>
                    </div>
                    
                    <div class="requirement-section">
                        <h3>English Language Requirements</h3>
                        <p>IELTS 6.5 overall with no less than 6.0 in each component</p>
                    </div>
                    
                    <div class="requirement-section">
                        <h3>Alternative Qualifications</h3>
                        <p>We welcome applications from students with equivalent qualifications. Please contact us for more information.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanels = document.querySelectorAll('.tab-panel');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanels.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>