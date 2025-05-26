<?php
// File: admin.php
// Admin panel for user management

require_once 'includes/functions.php';
require_once 'config/database.php';

// Get database connection
$pdo = getDBConnection();

// Get all users
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Get all participants
$participants = getAllParticipants($pdo);

// Get available roles from database
$rolesStmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'role'");
$rolesData = $rolesStmt->fetch();
preg_match("/^enum\(\'(.*)\'\)$/", $rolesData['Type'], $matches);
$roles = explode("','", $matches[1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users & Participants</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-tachometer-alt me-2"></i>Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Scoreboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="judge.php">
                            <i class="fas fa-clipboard-check me-1"></i> Submit Scores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">
                            <i class="fas fa-users-cog me-1"></i> Manage Users
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4" style="margin-top: 70px;">
        <div class="container">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0 text-primary">
                    <i class="fas fa-users-cog me-2"></i>User Management
                </h1>
            </div>

            <div class="row g-4">
                <!-- User Management Card -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title fs-6 mb-0">
                                <i class="fas fa-user-shield me-2 text-primary"></i>User Accounts
                            </h5>
                        </div>
                        <div class="card-body px-4">
                            <!-- Add User Form -->
                            <form id="add-user-form" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="username" class="form-label small fw-medium">Username</label>
                                        <input type="text" class="form-control form-control-sm" id="username" name="username" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="password" class="form-label small fw-medium">Password</label>
                                        <input type="password" class="form-control form-control-sm" id="password" name="password" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="role" class="form-label small fw-medium">Role</label>
                                        <select class="form-select form-select-sm" id="role" name="role" required>
                                            <option value="">Select role</option>
                                            <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo htmlspecialchars($role); ?>">
                                                <?php echo ucfirst(htmlspecialchars($role)); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus-circle me-1"></i> Add User
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Users Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Created</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-muted small"></i>
                                                    </div>
                                                    <span class="small"><?php echo htmlspecialchars($user['username']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?> small">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td class="small"><?php echo timeAgo($user['created_at']); ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-danger delete-user py-0 px-2" 
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span class="d-none d-md-inline ms-1">Delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Participant Management Card -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title fs-6 mb-0">
                                <i class="fas fa-user-friends me-2 text-primary"></i>Participants
                            </h5>
                        </div>
                        <div class="card-body px-4">
                            <!-- Add Participant Form -->
                            <form id="add-participant-form" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label small fw-medium">Name</label>
                                        <input type="text" class="form-control form-control-sm" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="identifier" class="form-label small fw-medium">Identifier</label>
                                        <input type="text" class="form-control form-control-sm" id="identifier" name="identifier" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus-circle me-1"></i> Add Participant
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Participants Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Identifier</th>
                                            <th>Avg Score</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants as $participant): ?>
                                        <tr>
                                            <td class="small"><?php echo htmlspecialchars($participant['name']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($participant['identifier']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getScoreColor($participant['average_score']); ?> text-dark small">
                                                    <?php echo number_format($participant['average_score'], 2); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-danger delete-participant py-0 px-2" 
                                                        data-id="<?php echo $participant['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($participant['name']); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span class="d-none d-md-inline ms-1">Delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">Are you sure you want to delete this item?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>