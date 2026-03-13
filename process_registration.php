<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

$student_name = trim($_POST['student_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$programme_id = trim($_POST['programme_id'] ?? '');

$errors = [];

if ($student_name === '') {
    $errors[] = "Student name is required.";
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required.";
}

if ($programme_id === '') {
    $errors[] = "Please select a programme.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: main.php");
    exit;
}

try {
    $pdo = getConnection();

    $checkSql = "SELECT COUNT(*) FROM interestedstudents WHERE ProgrammeID = :programme_id AND Email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
        ':programme_id' => $programme_id,
        ':email' => $email
    ]);

    if ($checkStmt->fetchColumn() > 0) {
        $_SESSION['errors'] = ["You have already registered for this programme."];
        header("Location: main.php");
        exit;
    }


    $sql = "INSERT INTO interestedstudents (ProgrammeID, StudentName, Email)
            VALUES (:programme_id, :student_name, :email)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':programme_id' => $programme_id,
        ':student_name' => $student_name,
        ':email' => $email
    ]);

    header("Location: thankyou.php");
exit;

    $_SESSION['success'] = "";
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
}

