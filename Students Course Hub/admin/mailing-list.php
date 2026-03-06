<?php
$pageTitle = "Mailing Lists - Admin";
require_once '../includes/config.php';
include 'admin-header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <!-- Same sidebar -->
    </aside>
    
    <main class="admin-content">
        <h1>Mailing Lists</h1>
        
        <div class="mailing-list-controls">
            <div class="programme-selector">
                <label for="select-programme">Select Programme:</label>
                <select id="select-programme" class="form-control">
                    <option value="">All Programmes</option>
                    <option value="1" selected>BSc Computer Science</option>
                    <option value="2">BSc Cyber Security</option>
                    <option value="3">MSc Data Science</option>
                </select>
            </div>
            
            <div class="export-options">
                <button class="btn btn-primary" onclick="exportList('csv')">Export as CSV</button>
                <button class="btn btn-primary" onclick="exportList('excel')">Export as Excel</button>
                <button class="btn btn-primary" onclick="exportList('pdf')">Export as PDF</button>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-cards">
            <div class="stat-card small">
                <h4>Total Interested</h4>
                <p class="stat-number">45</p>
            </div>
            <div class="stat-card small">
                <h4>New This Week</h4>
                <p class="stat-number">8</p>
            </div>
            <div class="stat-card small">
                <h4>Open Rate</h4>
                <p class="stat-number">68%</p>
            </div>
        </div>
        
        <!-- Mailing List Table -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Programme</th>
                    <th>Newsletter</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="select-item"></td>
                    <td>2025-02-09</td>
                    <td>John Smith</td>
                    <td>john.s@email.com</td>
                    <td>07700 123456</td>
                    <td>BSc Computer Science</td>
                    <td>✅</td>
                    <td><span class="badge badge-new">New</span></td>
                    <td>
                        <button class="btn-small" onclick="sendReminder(1)">Remind</button>
                        <button class="btn-small btn-danger" onclick="removeInterest(1)">Remove</button>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="select-item"></td>
                    <td>2025-02-08</td>
                    <td>Emma Wilson</td>
                    <td>emma.w@email.com</td>
                    <td>07700 789012</td>
                    <td>MSc Data Science</td>
                    <td>✅</td>
                    <td><span class="badge badge-contacted">Contacted</span></td>
                    <td>
                        <button class="btn-small" onclick="sendReminder(2)">Remind</button>
                        <button class="btn-small btn-danger" onclick="removeInterest(2)">Remove</button>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="select-item"></td>
                    <td>2025-02-07</td>
                    <td>Michael Brown</td>
                    <td>michael.b@email.com</td>
                    <td>07700 345678</td>
                    <td>BSc Cyber Security</td>
                    <td>❌</td>
                    <td><span class="badge badge-optout">Opted Out</span></td>
                    <td>
                        <button class="btn-small btn-danger" onclick="removeInterest(3)">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action">
                <option value="">Bulk Actions</option>
                <option value="export">Export Selected</option>
                <option value="remind">Send Reminder</option>
                <option value="delete">Remove Selected</option>
            </select>
            <button class="btn btn-secondary">Apply</button>
        </div>
        
        <!-- Email Campaign Section -->
        <div class="email-campaign">
            <h2>Send Email Campaign</h2>
            <form class="email-form">
                <div class="form-group">
                    <label for="email-subject">Subject:</label>
                    <input type="text" id="email-subject" class="form-control" 
                           value="Open Day: BSc Computer Science - March 15th">
                </div>
                
                <div class="form-group">
                    <label for="email-content">Message:</label>
                    <textarea id="email-content" class="form-control" rows="8">Dear [Name],

We're pleased to invite you to our upcoming Open Day for BSc Computer Science on March 15th, 2025. This is a great opportunity to:

• Meet our programme leaders and current students
• Tour our facilities and labs
• Learn about our curriculum and modules
• Get answers to your questions

Please register for the event here: [Link]

Best regards,
Admissions Team</textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="send_to_all"> Send to all interested students
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="send_to_selected"> Send only to selected students
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Send Email</button>
            </form>
        </div>
    </main>
</div>

<script>
function exportList(format) {
    alert(`Exporting mailing list as ${format} (demo)`);
}

function sendReminder(id) {
    alert(`Reminder sent to student ${id} (demo)`);
}

function removeInterest(id) {
    if (confirm('Remove this student from the mailing list?')) {
        alert(`Student ${id} removed (demo)`);
    }
}

// Select all functionality
document.getElementById('select-all').addEventListener('change', function(e) {
    document.querySelectorAll('.select-item').forEach(cb => {
        cb.checked = e.target.checked;
    });
});
</script>

<?php include 'admin-footer.php'; ?>