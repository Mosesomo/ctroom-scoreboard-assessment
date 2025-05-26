<?php
// File: includes/auth.php
// Authentication functions for Judge Scoreboard Application

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authenticate user login
 * @param PDO $pdo
 * @param string $username
 * @param string $password
 * @return bool
 */
function authenticateUser($pdo, $username, $password) {
    try {
        $user = getUserByUsername($pdo, $username);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            return true;
        }
        
        return false;
        
    } catch(Exception $e) {
        return false;
    }
}

/**
 * Logout user
 */
function logoutUser() {
    // Clear all session variables
    session_unset();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: login.php?message=logged_out');
    exit();
}

/**
 * Check if current session is valid
 * @return bool
 */
function isValidSession() {
    // Check if required session variables exist
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    // Check session timeout (optional - 2 hours)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
        return false;
    }
    
    return true;
}

/**
 * Get current user information
 * @return array|null
 */
function getCurrentUser() {
    if (!isValidSession()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

/**
 * Middleware to require login
 */
function requireAuth() {
    if (!isValidSession()) {
        // Store the current page to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        header('Location: login.php?error=login_required');
        exit();
    }
}

/**
 * Middleware to require admin role
 */
function requireAdmin() {
    requireAuth();
    
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied. Admin privileges required.');
    }
}

/**
 * Middleware to require judge role
 */
function requireJudge() {
    requireAuth();
    
    if ($_SESSION['role'] !== 'judge' && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied. Judge privileges required.');
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting for login attempts
 * @param string $identifier (IP or username)
 * @return bool
 */
function isRateLimited($identifier) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    $time_key = 'login_time_' . md5($identifier);
    
    if (!isset($_SESSION[$attempts_key])) {
        $_SESSION[$attempts_key] = 0;
        $_SESSION[$time_key] = time();
        return false;
    }
    
    // Reset counter if more than 15 minutes have passed
    if (time() - $_SESSION[$time_key] > 900) {
        $_SESSION[$attempts_key] = 0;
        $_SESSION[$time_key] = time();
        return false;
    }
    
    // Allow up to 5 attempts in 15 minutes
    return $_SESSION[$attempts_key] >= 5;
}

/**
 * Record login attempt
 * @param string $identifier
 * @param bool $success
 */
function recordLoginAttempt($identifier, $success = false) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    
    if ($success) {
        // Clear attempts on successful login
        unset($_SESSION[$attempts_key]);
    } else {
        // Increment failed attempts
        if (!isset($_SESSION[$attempts_key])) {
            $_SESSION[$attempts_key] = 0;
        }
        $_SESSION[$attempts_key]++;
    }
}

/**
 * Generate secure session ID
 */
function regenerateSessionId() {
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Check password strength
 * @param string $password
 * @return array
 */
function checkPasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return [
        'is_strong' => empty($errors),
        'errors' => $errors
    ];
}
?>