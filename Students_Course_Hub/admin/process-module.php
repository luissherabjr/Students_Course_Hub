<?php
/**
 * Process Module - Handle Add/Edit operations for modules with image upload
 */

require_once '../includes/config.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . 'admin/modules.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token';
    header('Location: ' . BASE_PATH . 'admin/modules.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    
    // ============================================
    // GET FORM DATA
    // ============================================
    $moduleName = cleanInput($_POST['moduleName']);
    $description = cleanInput($_POST['description']);
    $moduleLeader = !empty($_POST['moduleLeader']) ? (int)$_POST['moduleLeader'] : null;
    $status = cleanInput($_POST['status']);
    $imageAlt = cleanInput($_POST['imageAlt'] ?? '');
    
    // ============================================
    // HANDLE IMAGE UPLOAD
    // ============================================
    $image = null;
    
    // Check if file was uploaded
    if (isset($_FILES['module_image']) && $_FILES['module_image']['error'] == 0) {
        $upload_dir = dirname(__DIR__) . '/uploads/modules/';
        
        // Create directory if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file = $_FILES['module_image'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_name = $file['name'];
        
        // Allowed file types
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file_tmp);
        
        // Validate file
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 2 * 1024 * 1024) { // 2MB max
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_filename = 'module_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $destination)) {
                    $image = BASE_PATH . 'uploads/modules/' . $new_filename;
                } else {
                    $_SESSION['error'] = 'Failed to upload image';
                    header('Location: ' . BASE_PATH . 'admin/modules.php');
                    exit();
                }
            } else {
                $_SESSION['error'] = 'File too large. Max size 2MB';
                header('Location: ' . BASE_PATH . 'admin/modules.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WebP';
            header('Location: ' . BASE_PATH . 'admin/modules.php');
            exit();
        }
    } 
    // If no file uploaded, check for URL
    elseif (!empty($_POST['image'])) {
        $image = cleanInput($_POST['image']);
    }
    
    // ============================================
    // VALIDATION
    // ============================================
    if (empty($moduleName) || empty($description)) {
        $_SESSION['error'] = 'Module name and description are required';
        header('Location: ' . BASE_PATH . 'admin/modules.php');
        exit();
    }
    
    if (strlen($moduleName) > 150) {
        $_SESSION['error'] = 'Module name is too long (max 150 characters)';
        header('Location: ' . BASE_PATH . 'admin/modules.php');
        exit();
    }
    
    // ============================================
    // ADD NEW MODULE
    // ============================================
    if ($action === 'add') {
        // Get next ModuleID
        $result = mysqli_query($conn, "SELECT MAX(ModuleID) as max_id FROM Modules");
        $row = mysqli_fetch_assoc($result);
        $next_id = ($row['max_id'] ?? 0) + 1;
        
        // Prepare statement with or without image
        if ($image === null) {
            $stmt = mysqli_prepare($conn, "INSERT INTO Modules (ModuleID, ModuleName, ModuleLeaderID, Description, Status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isiss", $next_id, $moduleName, $moduleLeader, $description, $status);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO Modules (ModuleID, ModuleName, ModuleLeaderID, Description, Image, ImageAlt, Status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isissis", $next_id, $moduleName, $moduleLeader, $description, $image, $imageAlt, $status);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Module added successfully';
            logSecurityEvent('Module added: ' . $moduleName);
        } else {
            $_SESSION['error'] = 'Error adding module: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    
    // ============================================
    // EDIT EXISTING MODULE
    // ============================================
    else if ($action === 'edit') {
        $id = (int)$_POST['id'];
        
        // First check if module exists
        $check_stmt = mysqli_prepare($conn, "SELECT ModuleID FROM Modules WHERE ModuleID = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            $_SESSION['error'] = 'Module not found';
            header('Location: ' . BASE_PATH . 'admin/modules.php');
            exit();
        }
        mysqli_stmt_close($check_stmt);
        
        // Update module with or without image
        if ($image === null) {
            $stmt = mysqli_prepare($conn, "UPDATE Modules SET ModuleName = ?, ModuleLeaderID = ?, Description = ?, Status = ? WHERE ModuleID = ?");
            mysqli_stmt_bind_param($stmt, "sisis", $moduleName, $moduleLeader, $description, $status, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE Modules SET ModuleName = ?, ModuleLeaderID = ?, Description = ?, Image = ?, ImageAlt = ?, Status = ? WHERE ModuleID = ?");
            mysqli_stmt_bind_param($stmt, "sissssi", $moduleName, $moduleLeader, $description, $image, $imageAlt, $status, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Module updated successfully';
            logSecurityEvent('Module updated: ' . $moduleName);
        } else {
            $_SESSION['error'] = 'Error updating module: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

header('Location: ' . BASE_PATH . 'admin/modules.php');
exit();
?>