<?php
/**
 * Process Staff - Handle Add/Edit operations for staff with image upload
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . 'admin/staff.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token';
    header('Location: ' . BASE_PATH . 'admin/staff.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    
    // ============================================
    // GET FORM DATA
    // ============================================
    $name = cleanInput($_POST['name']);
    $title = cleanInput($_POST['title'] ?? '');
    $department = cleanInput($_POST['department'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $bio = cleanInput($_POST['bio'] ?? '');
    $photo = null;
    
    // ============================================
    // HANDLE IMAGE UPLOAD
    // ============================================
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $upload_dir = dirname(__DIR__) . '/uploads/staff/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file = $_FILES['profile_photo'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_name = $file['name'];
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file_tmp);
        
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 2 * 1024 * 1024) {
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_filename = 'staff_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $destination)) {
                    $photo = BASE_PATH . 'uploads/staff/' . $new_filename;
                } else {
                    $_SESSION['error'] = 'Failed to upload image';
                    header('Location: ' . BASE_PATH . 'admin/staff.php');
                    exit();
                }
            } else {
                $_SESSION['error'] = 'File too large. Max size 2MB';
                header('Location: ' . BASE_PATH . 'admin/staff.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WebP';
            header('Location: ' . BASE_PATH . 'admin/staff.php');
            exit();
        }
    } 
    elseif (!empty($_POST['photo_url'])) {
        $photo = cleanInput($_POST['photo_url']);
    }
    
    // ============================================
    // VALIDATION
    // ============================================
    if (empty($name)) {
        $_SESSION['error'] = 'Name is required';
        header('Location: ' . BASE_PATH . 'admin/staff.php');
        exit();
    }
    
    // ============================================
    // ADD NEW STAFF
    // ============================================
    if ($action === 'add') {
        $result = mysqli_query($conn, "SELECT MAX(StaffID) as max_id FROM Staff");
        $row = mysqli_fetch_assoc($result);
        $next_id = ($row['max_id'] ?? 0) + 1;
        
        if ($photo === null) {
            $stmt = mysqli_prepare($conn, "INSERT INTO Staff (StaffID, Name, Title, Department, Email, Bio) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssss", $next_id, $name, $title, $department, $email, $bio);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO Staff (StaffID, Name, Title, Department, Email, Bio, Photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssss", $next_id, $name, $title, $department, $email, $bio, $photo);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Staff member added successfully';
            logSecurityEvent('Staff added: ' . $name);
        } else {
            $_SESSION['error'] = 'Error adding staff: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    
    // ============================================
    // EDIT EXISTING STAFF
    // ============================================
    else if ($action === 'edit') {
        $id = (int)$_POST['id'];
        
        $check_stmt = mysqli_prepare($conn, "SELECT StaffID FROM Staff WHERE StaffID = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            $_SESSION['error'] = 'Staff member not found';
            header('Location: ' . BASE_PATH . 'admin/staff.php');
            exit();
        }
        mysqli_stmt_close($check_stmt);
        
        if ($photo === null) {
            $stmt = mysqli_prepare($conn, "UPDATE Staff SET Name = ?, Title = ?, Department = ?, Email = ?, Bio = ? WHERE StaffID = ?");
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $title, $department, $email, $bio, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE Staff SET Name = ?, Title = ?, Department = ?, Email = ?, Bio = ?, Photo = ? WHERE StaffID = ?");
            mysqli_stmt_bind_param($stmt, "ssssssi", $name, $title, $department, $email, $bio, $photo, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Staff member updated successfully';
            logSecurityEvent('Staff updated: ' . $name);
        } else {
            $_SESSION['error'] = 'Error updating staff: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

header('Location: ' . BASE_PATH . 'admin/staff.php');
exit();
?>