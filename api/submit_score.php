<?php
// File: api/submit_score.php
// API endpoint for submitting scores

header('Content-Type: application/json');

require_once '../includes/functions.php';
require_once '../config/database.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Received score submission request");

// Log all POST data
error_log("Raw POST data: " . file_get_contents('php://input'));
error_log("POST array: " . print_r($_POST, true));
error_log("Files array: " . print_r($_FILES, true));

try {
    // Get database connection
    $pdo = getDBConnection();

    // Log received data
    error_log("POST data: " . print_r($_POST, true));

    // Validate required fields
    if (!isset($_POST['judge_name']) || !isset($_POST['participant_id']) || !isset($_POST['score'])) {
        throw new Exception('Missing required fields');
    }

    // Get judge ID or create new judge
    $judgeName = sanitizeInput($_POST['judge_name']);
    
    // First try to get existing judge
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role = 'judge'");
    $stmt->execute([':username' => $judgeName]);
    $judge = $stmt->fetch();
    
    if ($judge) {
        $judgeId = $judge['id'];
        error_log("Found existing judge with ID: $judgeId");
    } else {
        // Create new judge if doesn't exist
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'judge')");
        $stmt->execute([
            ':username' => $judgeName,
            ':password' => password_hash('temp123', PASSWORD_DEFAULT)
        ]);
        $judgeId = $pdo->lastInsertId();
        error_log("Created new judge with ID: $judgeId");
    }

    // Validate input
    $participantId = (int)$_POST['participant_id'];
    $score = (float)$_POST['score'];
    
    error_log("Processing score submission - Judge ID: $judgeId, Participant ID: $participantId, Score: $score");
    
    // Validate score
    if (!validateScore($score)) {
        throw new Exception('Invalid score value - must be between 0 and 100');
    }
    
    // Submit score
    if (submitScore($pdo, $judgeId, $participantId, $score)) {
        // For AJAX requests, return JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'message' => 'Score submitted successfully'
            ]);
        } else {
            // For regular form submissions, redirect back with success message
            header('Location: ../judge.php?success=1');
            exit;
        }
    } else {
        throw new Exception('Failed to submit score to database');
    }
    
} catch (Exception $e) {
    error_log("Error in submit_score.php: " . $e->getMessage());
    
    // For AJAX requests, return JSON error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        // For regular form submissions, redirect back with error message
        header('Location: ../judge.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}
