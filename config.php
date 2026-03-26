<?php
/**
 * Configuration File - Database connection and core functions
 * Handles security, sessions, and common utilities
 */

// ============================================
// SESSION MANAGEMENT
// ============================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validate session (prevent session hijacking)
if (isset($_SESSION['ip_address']) && isset($_SESSION['user_agent'])) {
    if ($_SESSION['ip_address'] !== getUserIP() || $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        session_destroy();
        header('Location: /students_course_hub/auth/login.php');
        exit();
    }
}

// Session timeout - 30 minutes (1800 seconds)
$timeout = 1800;
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
    session_destroy();
    header('Location: /students_course_hub/auth/login.php?timeout=1');
    exit();
}

// Refresh login time on activity
if (isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}

// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_course_hub');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Set timezone
date_default_timezone_set('Europe/London');

// ============================================
// SITE URL CONFIGURATION
// ============================================

define('BASE_URL', 'http://localhost/students_course_hub/');
define('BASE_PATH', '/students_course_hub/');

// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if user is staff
 */
function isStaff() {
    return hasRole('staff');
}

/**
 * Safe redirect - prevents header injection
 */
function redirect($url) {
    $url = str_replace(["\r", "\n", "\r\n"], '', $url);
    header("Location: " . $url);
    exit();
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Sanitize for display - prevents XSS attacks
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ============================================
// PASSWORD HASHING FUNCTIONS
// ============================================

/**
 * Hash a password using bcrypt (recommended algorithm)
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    // Use bcrypt with cost factor 10 (good balance of security and performance)
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against its hash
 * @param string $password Plain text password
 * @param string $hash Hashed password from database
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if password needs rehashing (for upgrading security)
 * @param string $hash Hashed password
 * @return bool True if needs rehash
 */
function needsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}

/**
 * Clean input for database - prevents SQL injection
 */
function cleanInput($data) {
    global $conn;
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Generate CSRF token for forms
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user's IP address safely
 */
function getUserIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

// ============================================
// LOGIN ATTEMPT TRACKING
// ============================================

function trackLoginAttempt($username) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $_SESSION['login_attempts'][] = [
        'username' => $username,
        'time' => time(),
        'ip' => getUserIP()
    ];
    
    if (count($_SESSION['login_attempts']) > 10) {
        array_shift($_SESSION['login_attempts']);
    }
}

function isLoginBlocked() {
    if (!isset($_SESSION['login_attempts'])) {
        return false;
    }
    
    $recent_attempts = 0;
    $fifteen_minutes_ago = time() - (15 * 60);
    
    foreach ($_SESSION['login_attempts'] as $attempt) {
        if ($attempt['time'] > $fifteen_minutes_ago) {
            $recent_attempts++;
        }
    }
    
    return $recent_attempts >= 5;
}

function clearLoginAttempts() {
    $_SESSION['login_attempts'] = [];
}

function logSecurityEvent($message) {
    $log_dir = dirname(__DIR__) . '/logs';
    $log_file = $log_dir . '/security.log';
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = getUserIP();
    $user = $_SESSION['user_username'] ?? 'guest';
    $log_message = "[$timestamp] IP: $ip - User: $user - $message" . PHP_EOL;
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// ============================================
// USER INFO FUNCTIONS
// ============================================

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getCurrentStaffId() {
    return $_SESSION['staff_id'] ?? 0;
}
?>