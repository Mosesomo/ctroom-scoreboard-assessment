<?php
// File: index.php
// Main scoreboard display page

require_once 'includes/functions.php';
require_once 'config/database.php';

// Get database connection
$pdo = getDBConnection();

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total participants count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM participants");
$totalParticipants = $totalStmt->fetchColumn();
$totalPages = ceil($totalParticipants / $perPage);

// Get paginated scoreboard data
$scores = getScoreboardData($pdo, $perPage, $offset);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Scoreboard - Live Rankings</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-trophy me-2"></i>Judge Scoreboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-chart-line me-1"></i> Scoreboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="judge.php">
                            <i class="fas fa-clipboard-check me-1"></i> Submit Scores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">
                            <i class="fas fa-users-cog me-1"></i> Manage Users
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4" style="margin-top: 70px;">
        <div class="container">
            <div class="card shadow border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 py-3">
                    <div>
                        <h2 class="h4 mb-0 text-primary">
                            <i class="fas fa-ranking-star me-2"></i>Live Rankings
                        </h2>
                        <small class="text-muted">Auto-updates every 5 seconds</small>
                    </div>
                    <div class="spinner-border spinner-border-sm text-primary d-none" role="status" id="updateSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive rounded">
                        <table id="scoreboard" class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">RANK</th>
                                    <th>PARTICIPANT</th>
                                    <th>AVG SCORE</th>
                                    <th>JUDGES</th>
                                    <th>TOTAL</th>
                                    <th class="pe-4">UPDATED</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scores as $index => $score): ?>
                                <tr class="score-row <?php echo $index < 3 ? 'top-' . ($index + 1) : ''; ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <span class="rank-number d-flex align-items-center justify-content-center">
                                                <?php echo $index + 1; ?>
                                            </span>
                                            <?php if ($index < 3): ?>
                                            <i class="fas fa-crown ms-2 text-<?php 
                                                echo $index === 0 ? 'warning' : 
                                                     ($index === 1 ? 'secondary' : 'danger'); 
                                            ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($score['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($score['identifier']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-<?php echo getScoreColor($score['average_score']); ?>">
                                            <?php echo number_format($score['average_score'], 2); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="fas fa-user-tie me-1"></i><?php echo $score['judge_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo number_format($score['total_score'], 2); ?></div>
                                    </td>
                                    <td class="pe-4">
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i><?php echo timeAgo($score['last_updated']); ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-white border-top">
        <div class="container text-center">
            <span class="text-muted">&copy; <?php echo date('Y'); ?> Judge Scoreboard. All rights reserved.</span>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>