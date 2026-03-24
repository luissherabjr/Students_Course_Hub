<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Get filter from URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';
$where = "";
if ($filter == 'active') {
    $where = "WHERE i.Status = 'active'";
} elseif ($filter == 'withdrawn') {
    $where = "WHERE i.Status = 'withdrawn'";
}

// Query data with all necessary information
$query = "SELECT i.StudentName, i.Email, p.ProgrammeName, l.LevelName, 
                 i.RegisteredAt, i.Status, i.WithdrawnAt
          FROM InterestedStudents i
          JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
          JOIN Levels l ON p.LevelID = l.LevelID
          $where
          ORDER BY i.RegisteredAt DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Set headers for CSV download
$filename = 'mailing_list_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers with proper column names
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
    fputcsv($output, ['No records found for the selected filter']);
}

fclose($output);
exit();
?>