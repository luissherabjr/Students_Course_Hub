<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $programme_id = (int)$_POST['programme_id'];
    $module_id = (int)$_POST['module_id'];
    $year = (int)$_POST['year'];
    
    // Check if already exists
    $check = mysqli_query($conn, "SELECT * FROM ProgrammeModules WHERE ProgrammeID = $programme_id AND ModuleID = $module_id");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = 'This module is already assigned to the programme';
    } else {
        $query = "INSERT INTO ProgrammeModules (ProgrammeID, ModuleID, Year) VALUES ($programme_id, $module_id, $year)";
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Module added to programme successfully';
        } else {
            $_SESSION['error'] = 'Error adding module: ' . mysqli_error($conn);
        }
    }
    
    header('Location: ' . BASE_PATH . 'admin/programme-modules.php?id=' . $programme_id);
    exit();
}

header('Location: ' . BASE_PATH . 'admin/programmes.php');
exit();
?>