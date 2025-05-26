<?php
// File: judge.php
// Judge scoring interface

require_once 'includes/functions.php';
require_once 'config/database.php';

// Get database connection
$pdo = getDBConnection();

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get all participants
$participants = getAllParticipants($pdo);

// Get total scores count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM scores");
$totalScores = $totalStmt->fetchColumn();
$totalPages = ceil($totalScores / $perPage);

// Get paginated scores
$stmt = $pdo->prepare("SELECT s.*, p.name as participant_name, u.username as judge_name 
                     FROM scores s 
                     JOIN participants p ON s.participant_id = p.id 
                     JOIN users u ON s.judge_id = u.id 
                     ORDER BY s.submitted_at DESC
                     LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$allScores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Score Submission Panel</title>
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
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-chart-line me-1"></i> Scoreboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="judge.php">
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
            <div class="row g-4">
                <!-- Score Submission Card -->
                <div class="col-lg-6">
                    <div class="card shadow border-0 h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h3 class="h5 mb-0 text-primary">
                                <i class="fas fa-pen-to-square me-2"></i>Submit New Score
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>Score submitted successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>

                            <form id="score-form" class="score-form" action="api/submit_score.php" method="POST">
                                <div class="mb-4">
                                    <label for="judge_name" class="form-label fw-medium">
                                        <i class="fas fa-user-tie me-1"></i>Judge Name
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control" name="judge_name" id="judge_name" required 
                                               placeholder="Enter your name">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="participant" class="form-label fw-medium">
                                        <i class="fas fa-user-group me-1"></i>Participant
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-list text-muted"></i>
                                        </span>
                                        <select class="form-select" name="participant_id" id="participant" required>
                                            <option value="">Choose a participant...</option>
                                            <?php foreach ($participants as $participant): ?>
                                            <option value="<?php echo $participant['id']; ?>">
                                                <?php echo htmlspecialchars($participant['name']); ?> 
                                                (<?php echo htmlspecialchars($participant['identifier']); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="score" class="form-label fw-medium">
                                        <i class="fas fa-star me-1"></i>Score (0-100)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-percent text-muted"></i>
                                        </span>
                                        <input type="number" class="form-control" name="score" id="score" 
                                               min="0" max="100" step="0.01" required
                                               placeholder="Enter score between 0 and 100">
                                    </div>
                                    <div class="mt-2">
                                        <input type="range" class="form-range" min="0" max="100" step="1" id="scoreRange">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Score
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Scores History Card -->
                <div class="col-lg-6">
                    <div class="card shadow border-0 h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0 text-primary">
                                <i class="fas fa-history me-2"></i>Submitted Scores
                            </h3>
                            <span class="badge bg-primary rounded-pill">
                                <i class="fas fa-list-check me-1"></i><?php echo count($allScores); ?>
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive rounded">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">JUDGE</th>
                                            <th>PARTICIPANT</th>
                                            <th>SCORE</th>
                                            <th class="pe-4">SUBMITTED</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allScores as $score): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user-tie text-primary"></i>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($score['judge_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($score['participant_name']); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getScoreColor($score['score']); ?> rounded-pill">
                                                    <?php echo number_format($score['score'], 2); ?>
                                                </span>
                                            </td>
                                            <td class="pe-4">
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i><?php echo timeAgo($score['submitted_at']); ?>
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
            </div>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-white border-top">
        <div class="container text-center">
            <span class="text-muted">&copy; <?php echo date('Y'); ?> Judge Scoreboard. All rights reserved.</span>
        </div>
    </footer>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>