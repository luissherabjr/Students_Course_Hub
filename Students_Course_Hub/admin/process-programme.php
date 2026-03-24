<?php
/**
 * Process Programme - Handle Add/Edit operations for programmes with image upload
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . 'admin/programmes.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token';
    header('Location: ' . BASE_PATH . 'admin/programmes.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    
    // ============================================
    // GET FORM DATA
    // ============================================
    $programmeName = cleanInput($_POST['programmeName']);
    $levelId = (int)$_POST['levelId'];
    $programmeLeader = !empty($_POST['programmeLeader']) ? (int)$_POST['programmeLeader'] : null;
    $description = cleanInput($_POST['description']);
    $status = cleanInput($_POST['status']);
    $imageAlt = cleanInput($_POST['imageAlt'] ?? '');
    
    // ============================================
    // HANDLE IMAGE UPLOAD
    // ============================================
    $image = null;
    
    // Check if file was uploaded
    if (isset($_FILES['programme_image']) && $_FILES['programme_image']['error'] == 0) {
        $upload_dir = dirname(__DIR__) . '/uploads/programmes/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file = $_FILES['programme_image'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_name = $file['name'];
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file_tmp);
        
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 2 * 1024 * 1024) {
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_filename = 'programme_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $destination)) {
                    $image = BASE_PATH . 'uploads/programmes/' . $new_filename;
                } else {
                    $_SESSION['error'] = 'Failed to upload image';
                    header('Location: ' . BASE_PATH . 'admin/programmes.php');
                    exit();
                }
            } else {
                $_SESSION['error'] = 'File too large. Max size 2MB';
                header('Location: ' . BASE_PATH . 'admin/programmes.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WebP';
            header('Location: ' . BASE_PATH . 'admin/programmes.php');
            exit();
        }
    } 
    elseif (!empty($_POST['image'])) {
        $image = cleanInput($_POST['image']);
    }
    
    // ============================================
    // VALIDATION
    // ============================================
    if (empty($programmeName) || empty($description)) {
        $_SESSION['error'] = 'Programme name and description are required';
        header('Location: ' . BASE_PATH . 'admin/programmes.php');
        exit();
    }
    
    if ($levelId < 1 || $levelId > 2) {
        $_SESSION['error'] = 'Invalid level selected';
        header('Location: ' . BASE_PATH . 'admin/programmes.php');
        exit();
    }
    
    // ============================================
    // ADD NEW PROGRAMME
    // ============================================
    if ($action === 'add') {
        if ($image === null) {
            $stmt = mysqli_prepare($conn, "INSERT INTO Programmes (ProgrammeName, LevelID, ProgrammeLeaderID, Description, Status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siiss", $programmeName, $levelId, $programmeLeader, $description, $status);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO Programmes (ProgrammeName, LevelID, ProgrammeLeaderID, Description, Image, ImageAlt, Status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siissis", $programmeName, $levelId, $programmeLeader, $description, $image, $imageAlt, $status);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Programme added successfully';
            logSecurityEvent('Programme added: ' . $programmeName);
        } else {
            $_SESSION['error'] = 'Error adding programme: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    
    // ============================================
    // EDIT EXISTING PROGRAMME
    // ============================================
    else if ($action === 'edit') {
        $id = (int)$_POST['id'];
        
        $check_stmt = mysqli_prepare($conn, "SELECT ProgrammeID FROM Programmes WHERE ProgrammeID = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            $_SESSION['error'] = 'Programme not found';
            header('Location: ' . BASE_PATH . 'admin/programmes.php');
            exit();
        }
        mysqli_stmt_close($check_stmt);
        
        if ($image === null) {
            $stmt = mysqli_prepare($conn, "UPDATE Programmes SET ProgrammeName = ?, LevelID = ?, ProgrammeLeaderID = ?, Description = ?, Status = ? WHERE ProgrammeID = ?");
            mysqli_stmt_bind_param($stmt, "siissi", $programmeName, $levelId, $programmeLeader, $description, $status, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE Programmes SET ProgrammeName = ?, LevelID = ?, ProgrammeLeaderID = ?, Description = ?, Image = ?, ImageAlt = ?, Status = ? WHERE ProgrammeID = ?");
            mysqli_stmt_bind_param($stmt, "siissisi", $programmeName, $levelId, $programmeLeader, $description, $image, $imageAlt, $status, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Programme updated successfully';
            logSecurityEvent('Programme updated: ' . $programmeName);
        } else {
            $_SESSION['error'] = 'Error updating programme: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

header('Location: ' . BASE_PATH . 'admin/programmes.php');
exit();
?>