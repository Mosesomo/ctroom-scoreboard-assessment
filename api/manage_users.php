<?php
// File: api/manage_users.php
// API endpoint for user management

header('Content-Type: application/json');

require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin authentication
requireAdmin();

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get action type
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_user':
            // Validate input
            $username = sanitizeInput($_POST['username']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            // Validate role
            if (!in_array($role, ['judge', 'admin'])) {
                throw new Exception('Invalid role');
            }
            
            // Create user
            if (createUser($pdo, $username, $password, $role)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User created successfully'
                ]);
            } else {
                throw new Exception('Failed to create user');
            }
            break;
            
        case 'delete_user':
            // Validate input
            $userId = (int)$_POST['user_id'];
            
            // Delete user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            if ($stmt->execute([':id' => $userId])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete user');
            }
            break;
            
        case 'add_participant':
            // Validate input
            $name = sanitizeInput($_POST['name']);
            $identifier = sanitizeInput($_POST['identifier']);
            
            // Create participant
            if (createParticipant($pdo, $name, $identifier)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Participant created successfully'
                ]);
            } else {
                throw new Exception('Failed to create participant');
            }
            break;
            
        case 'delete_participant':
            // Validate input
            $participantId = (int)$_POST['participant_id'];
            
            // Delete participant
            $stmt = $pdo->prepare("DELETE FROM participants WHERE id = :id");
            if ($stmt->execute([':id' => $participantId])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Participant deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete participant');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Error managing users: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
