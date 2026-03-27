<?php
session_start();
require_once '../../includes/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: main.php');
    exit();
}

// Get form data
$student_name = cleanInput($_POST['student_name'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$programme_id = isset($_POST['programme_id']) ? (int)$_POST['programme_id'] : 0;
$qualification = cleanInput($_POST['academic_qualification'] ?? '');
$receive_updates = isset($_POST['receive_updates']) ? 'yes' : 'no';

$errors = [];

// Validation
if (empty($student_name)) {
    $errors[] = 'Please enter your full name';
}

if (empty($email)) {
    $errors[] = 'Please enter your email address';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if ($programme_id <= 0) {
    $errors[] = 'Please select a programme';
} else {
    // Check if programme exists and is published
    $check = mysqli_query($conn, "SELECT ProgrammeID FROM Programmes WHERE ProgrammeID = $programme_id AND Status = 'published'");
    if (mysqli_num_rows($check) == 0) {
        $errors[] = 'Selected programme is not available';
    }
}

if (empty($qualification)) {
    $errors[] = 'Please select your academic qualification';
}

if ($receive_updates !== 'yes') {
    $errors[] = 'You must consent to receive updates';
}

// If no errors, save to database
if (empty($errors)) {
    // Check for duplicate registration
    $check_dup = mysqli_prepare($conn, "SELECT InterestID FROM InterestedStudents WHERE ProgrammeID = ? AND Email = ? AND Status = 'active'");
    mysqli_stmt_bind_param($check_dup, "is", $programme_id, $email);
    mysqli_stmt_execute($check_dup);
    $dup_result = mysqli_stmt_get_result($check_dup);
    
    if (mysqli_num_rows($dup_result) > 0) {
        $errors[] = 'You have already registered interest in this programme';
    } else {
        // Insert new registration with ALL fields
        $stmt = mysqli_prepare($conn, "INSERT INTO InterestedStudents (ProgrammeID, StudentName, AcademicQualification, Email, ReceiveUpdates) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issss", $programme_id, $student_name, $qualification, $email, $receive_updates);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: thankyou.php');
            exit();
        } else {
            $errors[] = 'Error saving your registration. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check_dup);
}

// If errors, store in session and redirect back
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: main.php');
    exit();
}
?>
