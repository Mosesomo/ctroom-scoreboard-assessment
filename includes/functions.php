<?php
// File: includes/functions.php
// Helper functions for Judge Scoreboard Application

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate score (must be between 0 and 100)
 * @param float $score
 * @return bool
 */
function validateScore($score) {
    return is_numeric($score) && $score >= 0 && $score <= 100;
}

/**
 * Format score for display
 * @param float $score
 * @return string
 */
function formatScore($score) {
    return number_format($score, 2);
}

/**
 * Get all participants from database
 * @param PDO $pdo
 * @return array
 */
function getAllParticipants($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, identifier FROM participants ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching participants: " . $e->getMessage());
        return [];
    }
}

/**
 * Get scoreboard data with rankings
 * @param PDO $pdo
 * @param int $limit Number of records per page
 * @param int $offset Starting position
 * @return array
 */
function getScoreboardData($pdo, $limit = null, $offset = 0) {
    try {
        $sql = "SELECT p.id, p.name, p.identifier,
                       COALESCE(AVG(s.score), 0) as average_score,
                       COALESCE(SUM(s.score), 0) as total_score,
                       COUNT(s.id) as judge_count,
                       MAX(s.submitted_at) as last_updated
                FROM participants p
                LEFT JOIN scores s ON p.id = s.participant_id
                GROUP BY p.id, p.name, p.identifier
                ORDER BY average_score DESC, total_score DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->query($sql);
        }
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add ranking and format scores
        foreach($results as $index => &$row) {
            $row['rank'] = $offset + $index + 1;
            $row['average_score'] = (float)$row['average_score'];
            $row['total_score'] = (float)$row['total_score'];
            $row['judge_count'] = (int)$row['judge_count'];
        }
        
        return $results;
    } catch(PDOException $e) {
        error_log("Error fetching scoreboard: " . $e->getMessage());
        return [];
    }
}

/**
 * Submit a score for a participant by a judge
 * @param PDO $pdo
 * @param int $judgeId
 * @param int $participantId
 * @param float $score
 * @return bool
 */
function submitScore($pdo, $judgeId, $participantId, $score) {
    try {
        // First check if score exists
        $stmt = $pdo->prepare("SELECT id FROM scores WHERE judge_id = :judge_id AND participant_id = :participant_id");
        $stmt->execute([
            ':judge_id' => $judgeId,
            ':participant_id' => $participantId
        ]);
        $existingScore = $stmt->fetch();
        
        if ($existingScore) {
            // Update existing score
            $sql = "UPDATE scores SET score = :score, submitted_at = CURRENT_TIMESTAMP 
                    WHERE judge_id = :judge_id AND participant_id = :participant_id";
        } else {
            // Insert new score
            $sql = "INSERT INTO scores (judge_id, participant_id, score) 
                    VALUES (:judge_id, :participant_id, :score)";
        }
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':judge_id' => $judgeId,
            ':participant_id' => $participantId,
            ':score' => $score
        ]);
    } catch(PDOException $e) {
        error_log("Error submitting score: " . $e->getMessage());
        return false;
    }
}

/**
 * Get scores submitted by a specific judge
 * @param PDO $pdo
 * @param int $judgeId
 * @return array
 */
function getJudgeScores($pdo, $judgeId) {
    try {
        $sql = "SELECT s.*, p.name as participant_name, p.identifier 
                FROM scores s 
                JOIN participants p ON s.participant_id = p.id 
                WHERE s.judge_id = :judge_id 
                ORDER BY s.submitted_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':judge_id' => $judgeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching judge scores: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user by username
 * @param PDO $pdo
 * @param string $username
 * @return array|null
 */
function getUserByUsername($pdo, $username) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}

/**
 * Create a new user
 * @param PDO $pdo
 * @param string $username
 * @param string $password
 * @param string $role
 * @return bool
 */
function createUser($pdo, $username, $password, $role = 'judge') {
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        return $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Create a new participant
 * @param PDO $pdo
 * @param string $name
 * @param string $identifier
 * @return bool
 */
function createParticipant($pdo, $name, $identifier) {
    try {
        $stmt = $pdo->prepare("INSERT INTO participants (name, identifier) VALUES (:name, :identifier)");
        return $stmt->execute([
            ':name' => $name,
            ':identifier' => $identifier
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Generate a random secure password
 * @param int $length
 * @return string
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Log activity (simple logging function)
 * @param string $message
 * @param string $level
 */
function logActivity($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    error_log($logMessage, 3, __DIR__ . '/../logs/app.log');
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user has specific role
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Require specific role
 * @param string $role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        http_response_code(403);
        die('Access denied. Insufficient permissions.');
    }
}

/**
 * Get time ago string
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    if (empty($datetime)) {
        return 'Never';
    }
    
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}

/**
 * Get score color based on score value
 * @param float $score
 * @return string
 */
function getScoreColor($score) {
    if ($score >= 90) {
        return 'success';
    } elseif ($score >= 75) {
        return 'primary';
    } elseif ($score >= 60) {
        return 'warning';
    } else {
        return 'danger';
    }
}
?>