<?php
// File: index.php
// Main scoreboard display page

require_once 'includes/functions.php';
require_once 'config/database.php';

// Get database connection
$pdo = getDBConnection();

// Get initial scoreboard data
$scores = getScoreboardData($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Scoreboard - Live Rankings</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>Judge Scoreboard</h1>
            </div>
            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Scoreboard</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (hasRole('judge')): ?>
                        <li><a href="judge.php">Judge Panel</a></li>
                    <?php endif; ?>
                    <?php if (hasRole('admin')): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="#" class="logout-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="scoreboard-container card">
            <div class="scoreboard-header">
                <div class="header-content">
                    <h2>Live Rankings</h2>
                    <p class="text-secondary">Auto-updates every 5 seconds</p>
                </div>
            </div>

            <div class="table-container">
                <table id="scoreboard" class="scoreboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Participant</th>
                            <th>Average Score</th>
                            <th>Judges</th>
                            <th>Total Score</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scores as $index => $score): ?>
                        <tr class="score-row <?php echo $index < 3 ? 'top-' . ($index + 1) : ''; ?>">
                            <td class="rank">
                                <span class="rank-number"><?php echo $index + 1; ?></span>
                            </td>
                            <td class="name"><?php echo htmlspecialchars($score['name']); ?></td>
                            <td class="score">
                                <span class="score-value"><?php echo number_format($score['average_score'], 2); ?></span>
                            </td>
                            <td class="judges">
                                <span class="judge-count"><?php echo $score['judge_count']; ?></span>
                            </td>
                            <td class="total">
                                <span class="total-score"><?php echo number_format($score['total_score'], 2); ?></span>
                            </td>
                            <td class="updated">
                                <span class="time-ago"><?php echo timeAgo($score['last_updated']); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Judge Scoreboard. All rights reserved.</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
