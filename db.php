<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "student_course_hub";
$username = "root";
$password = "";

function getConnection() {
    global $host, $dbname, $username, $password;

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

function getProgrammes() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT ProgrammeID, ProgrammeName FROM programmes ORDER BY ProgrammeName");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}