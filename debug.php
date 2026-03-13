<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

echo "<h3>Testing database...</h3>";

try {
    $pdo = getConnection();
    echo "Connected OK<br><br>";

    $stmt = $pdo->query("SHOW DATABASES");
    echo "<b>Databases:</b><br>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Database'] . "<br>";
    }

    echo "<br><b>Current database:</b><br>";
    $stmt = $pdo->query("SELECT DATABASE() AS dbname");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['dbname'] . "<br><br>";

    echo "<b>Tables:</b><br>";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "<br>";
    }

    echo "<br><b>Programmes data:</b><br>";
    $stmt = $pdo->query("SELECT ProgrammeID, ProgrammeName FROM Programmes");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($rows);
    echo "</pre>";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>