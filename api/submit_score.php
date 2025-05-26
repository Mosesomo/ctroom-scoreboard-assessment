<?php
// File: api/submit_score.php
// API endpoint for submitting scores

header('Content-Type: application/json');

require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require judge authentication
requireJudge();

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
    // Validate input
    $judgeId = (int)$_POST['judge_id'];
    $participantId = (int)$_POST['participant_id'];
    $score = (float)$_POST['score'];
    
    // Verify judge ID matches session user
    if ($judgeId !== $_SESSION['user_id']) {
        throw new Exception('Invalid judge ID');
    }
    
    // Validate score
    if (!validateScore($score)) {
        throw new Exception('Invalid score value');
    }
    
    // Get database connection
    $pdo = getDBConnection();
    
    // Submit score
    if (submitScore($pdo, $judgeId, $participantId, $score)) {
        echo json_encode([
            'success' => true,
            'message' => 'Score submitted successfully'
        ]);
    } else {
        throw new Exception('Failed to submit score');
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Error submitting score: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
