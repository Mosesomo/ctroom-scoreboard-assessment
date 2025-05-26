<?php
// File: api/get_scores.php
// API endpoint for fetching scoreboard data

header('Content-Type: application/json');

require_once '../includes/functions.php';
require_once '../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get scoreboard data
    $scores = getScoreboardData($pdo);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'scores' => $scores
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching scores: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching scoreboard data'
    ]);
}
