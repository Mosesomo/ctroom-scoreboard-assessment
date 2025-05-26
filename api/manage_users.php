<?php
// File: api/manage_users.php
// API endpoint for user management

header('Content-Type: application/json');

require_once '../includes/functions.php';
require_once '../config/database.php';

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
            
            // Get available roles from database
            $rolesStmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'role'");
            $rolesData = $rolesStmt->fetch();
            preg_match("/^enum\(\'(.*)\'\)$/", $rolesData['Type'], $matches);
            $validRoles = explode("','", $matches[1]);
            
            // Validate role
            if (!in_array($role, $validRoles)) {
                throw new Exception('Invalid role. Must be one of: ' . implode(', ', $validRoles));
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
                $participantId = $pdo->lastInsertId();
                echo json_encode([
                    'success' => true,
                    'message' => 'Participant created successfully',
                    'participant_id' => $participantId
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
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
