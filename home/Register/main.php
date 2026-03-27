<?php
session_start();
require_once '../../includes/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get all published programmes for dropdown
$programmes_query = "SELECT ProgrammeID, ProgrammeName FROM Programmes WHERE Status = 'published' ORDER BY ProgrammeName";
$programmes_result = mysqli_query($conn, $programmes_query);
$programmes = [];
while ($row = mysqli_fetch_assoc($programmes_result)) {
    $programmes[] = $row;
}

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Interest</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>

<div class="container">
    <div class="form-box">
        <h2>Register Your Interest</h2>

        <p>Please fill out the form below to register your interest in our programmes.</p>
        

        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <ul class="error">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="../index.php" class="home-btn">Return to Home</a>
        <?php endif; ?>

        <form action="process_registration.php" method="POST">
            <label for="student_name">FullName *</label>
            <input type="text" id="student_name" name="student_name" placeholder="e.g. Lionel Andes Messi" required>

            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" placeholder="e.g. lionel.messi@example.com" required>

            <label for="academic_qualification">Select Your Current Academic Qualification *</label>
            <select id="academic_qualification" name="academic_qualification" required>
                <option value="">-- Select Qualification --</option>
                <option value="High School">High School</option>
                <option value="Undergraduate">Undergraduate</option>
                <option value="Bachelor">Bachelor's Degree</option>
                <option value="Master">Master's Degree</option>
            </select>

            <label for="programme_id">Select Programme</label>
            <select id="programme_id" name="programme_id" required>
                <option value="">-- Select Programme --</option>
                <?php foreach ($programmes as $programme): ?>
                    <option value="<?php echo $programme['ProgrammeID']; ?>">
                        <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="receive_updates" required>
                    <span>I consent to receive updates about the programme via email.</span>
                </label>
            </div>

            <button type="submit" class="submit-btn">Complete Registration</button>
            <a class="cancel-btn" href='../index.php'>Cancel</a>
        </form>
    </div>
</div>

</body>
</html>
