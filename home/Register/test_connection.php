<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/config.php';

echo "<h3>Testing Database Connection (MySQLi)...</h3>";

// Test MySQLi connection
if ($conn) {
    echo "✅ Database connected successfully!<br><br>";
    
    // Show current database
    $result = mysqli_query($conn, "SELECT DATABASE() AS dbname");
    $row = mysqli_fetch_assoc($result);
    echo "<b>Current Database:</b> " . $row['dbname'] . "<br><br>";
    
    // Show all tables
    echo "<b>Tables in database:</b><br>";
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        echo " - " . $row[0] . "<br>";
    }
    
    // Show programmes data
    echo "<br><b>Programmes:</b><br>";
    $result = mysqli_query($conn, "SELECT ProgrammeID, ProgrammeName, Status FROM Programmes");
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "ID: " . $row['ProgrammeID'] . " - " . $row['ProgrammeName'] . " (" . $row['Status'] . ")<br>";
        }
    } else {
        echo "No programmes found.<br>";
    }
    
    // Check InterestedStudents table structure
    echo "<br><b>InterestedStudents Table Structure:</b><br>";
    $result = mysqli_query($conn, "DESCRIBE InterestedStudents");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Field: " . $row['Field'] . " - Type: " . $row['Type'] . "<br>";
    }
    
    // Count total registrations
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM InterestedStudents");
    $row = mysqli_fetch_assoc($result);
    echo "<br><b>Total Registrations:</b> " . $row['total'] . "<br>";
    
} else {
    echo "❌ Connection failed: " . mysqli_connect_error() . "<br>";
}
?>
