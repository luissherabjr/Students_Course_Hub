<?php
require_once '../includes/config.php';

// Check if user is logged in and is staff
if (!isLoggedIn() || !hasRole('staff')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$staff_id = $_SESSION['staff_id'] ?? 0;
$programme_id = isset($_GET['programme_id']) ? (int)$_GET['programme_id'] : 0;

if ($staff_id == 0 || $programme_id == 0) {
    header('Location: ' . BASE_URL . 'staff/my-programmes.php');
    exit();
}

// Verify this programme belongs to the staff
$check_query = "SELECT ProgrammeID, ProgrammeName FROM Programmes WHERE ProgrammeID = $programme_id AND ProgrammeLeaderID = $staff_id";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    header('Location: ' . BASE_URL . 'staff/my-programmes.php');
    exit();
}

$programme = mysqli_fetch_assoc($check_result);

// Get students interested in this programme
$query = "SELECT i.StudentName, i.Email, i.RegisteredAt, i.Status, i.WithdrawnAt,
                 p.ProgrammeName, l.LevelName
          FROM InterestedStudents i
          JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
          JOIN Levels l ON p.LevelID = l.LevelID
          WHERE i.ProgrammeID = $programme_id
          ORDER BY i.RegisteredAt DESC";
$result = mysqli_query($conn, $query);

// Set filename
$filename = 'programme_' . $programme_id . '_students_' . date('Y-m-d') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, [
    'Student Name',
    'Email',
    'Programme',
    'Level',
    'Registration Date',
    'Status',
    'Withdrawn Date'
]);

// Add data rows
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['StudentName'],
            $row['Email'],
            $row['ProgrammeName'],
            $row['LevelName'],
            $row['RegisteredAt'],
            $row['Status'],
            $row['WithdrawnAt'] ?? ''
        ]);
    }
} else {
    // Add a message if no data
    fputcsv($output, ['No students found for this programme']);
}

fclose($output);
exit();
?>