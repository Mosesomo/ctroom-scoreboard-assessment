<?php
// File: judge.php
// Judge scoring interface

require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

// Require judge authentication
requireJudge();

// Get database connection
$pdo = getDBConnection();

// Get current judge's scores
$judgeScores = getJudgeScores($pdo, $_SESSION['user_id']);

// Get all participants
$participants = getAllParticipants($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Panel - Score Submission</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>Judge Panel</h1>
            </div>
            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Scoreboard</a></li>
                <li><a href="judge.php" class="active">Judge Panel</a></li>
                <li><a href="#" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="judge-panel">
            <div class="panel-header card">
                <div class="header-content">
                    <h2>Submit Scores</h2>
                    <p class="text-secondary">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
            </div>

            <div class="scoring-section card">
                <form id="score-form" class="score-form">
                    <input type="hidden" name="judge_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="participant">Select Participant</label>
                        <select name="participant_id" id="participant" required>
                            <option value="">Choose a participant...</option>
                            <?php foreach ($participants as $participant): ?>
                            <option value="<?php echo $participant['id']; ?>">
                                <?php echo htmlspecialchars($participant['name']); ?> 
                                (<?php echo htmlspecialchars($participant['identifier']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="score">Score (0-100)</label>
                        <input type="number" name="score" id="score" min="0" max="100" step="0.01" required
                               placeholder="Enter score between 0 and 100">
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Score</button>
                </form>
            </div>

            <div class="previous-scores card">
                <h3>Your Previous Scores</h3>
                <div class="table-container">
                    <table class="scores-table">
                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Score</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($judgeScores as $score): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($score['participant_name']); ?></td>
                                <td><span class="score"><?php echo number_format($score['score'], 2); ?></span></td>
                                <td><?php echo timeAgo($score['submitted_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Judge Scoreboard. All rights reserved.</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
