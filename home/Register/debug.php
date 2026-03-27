<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/config.php';

echo "<h3>Testing database...</h3>";

// Check connection
if ($conn) {
    echo "✅ Connected OK<br><br>";
} else {
    echo "❌ Connection failed: " . mysqli_connect_error() . "<br>";
    exit();
}

// Show current database
$result = mysqli_query($conn, "SELECT DATABASE() AS dbname");
$row = mysqli_fetch_assoc($result);
echo "<b>Current database:</b> " . $row['dbname'] . "<br><br>";

// Show all tables
echo "<b>Tables:</b><br>";
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    echo " - " . $row[0] . "<br>";
}

// Show programmes data
echo "<br><b>Programmes data:</b><br>";
$result = mysqli_query($conn, "SELECT ProgrammeID, ProgrammeName FROM Programmes");

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['ProgrammeID'] . " - Name: " . $row['ProgrammeName'] . "<br>";
    }
} else {
    echo "No programmes found.<br>";
}

// Check if interestedstudents table has AcademicQualification field
echo "<br><b>InterestedStudents table structure:</b><br>";
$result = mysqli_query($conn, "DESCRIBE InterestedStudents");
while ($row = mysqli_fetch_assoc($result)) {
    echo "Field: " . $row['Field'] . " - Type: " . $row['Type'] . "<br>";
}
?>
