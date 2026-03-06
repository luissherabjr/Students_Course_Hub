<?php
$pageTitle = "Register Interest - Student Course Hub";
require_once '../includes/config.php';
include '../includes/header.php';

$programme_id = isset($_GET['programme_id']) ? (int)$_GET['programme_id'] : 0;
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $programme_id = (int)($_POST['programme_id'] ?? 0);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Basic validation
    if (empty($name) || empty($email) || empty($programme_id)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Here we'll later insert into database
        $success = true;
    }
}
?>

<div class="container">
    <div class="interest-form-container">
        <?php if ($success): ?>
            <div class="success-message">
                <h2>Thank you for your interest!</h2>
                <p>We have received your registration. You will receive updates about this programme via email.</p>
                <a href="programmes.php" class="btn btn-primary">Browse More Programmes</a>
            </div>
        <?php else: ?>
            <div class="form-header">
                <h1>Register Your Interest</h1>
                <p>Fill in your details to receive information about this programme.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register-interest.php" class="interest-form">
                <input type="hidden" name="programme_id" value="<?php echo $programme_id; ?>">
                
                <div class="programme-summary">
                    <h3>Programme: BSc Computer Science</h3>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                           class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="form-control">
                    <small>We'll send programme updates to this email</small>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                           class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="newsletter" value="1" checked>
                        I'd like to receive newsletters and updates about other programmes
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        I agree to the <a href="#">privacy policy</a> and consent to my data being stored *
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Submit Interest</button>
                    <a href="programme-detail.php?id=<?php echo $programme_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>