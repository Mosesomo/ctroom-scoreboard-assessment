<?php
// File: admin.php
// Admin panel for user management

require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

// Require admin authentication
requireAdmin();

// Get database connection
$pdo = getDBConnection();

// Get all users
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Get all participants
$participants = getAllParticipants($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>Admin Panel</h1>
            </div>
            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Scoreboard</a></li>
                <li><a href="admin.php" class="active">Admin Panel</a></li>
                <li><a href="#" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="admin-panel">
            <div class="panel-header card">
                <div class="header-content">
                    <h2>User Management</h2>
                    <p class="text-secondary">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
            </div>

            <div class="admin-sections">
                <!-- User Management Section -->
                <div class="section users-section card">
                    <h3>Manage Users</h3>
                    <form id="add-user-form" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" required placeholder="Enter username">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required placeholder="Enter password">
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" required>
                                <option value="">Select role...</option>
                                <option value="judge">Judge</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Add User</button>
                    </form>

                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><span class="badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo timeAgo($user['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-secondary delete-user" data-id="<?php echo $user['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Participant Management Section -->
                <div class="section participants-section card">
                    <h3>Manage Participants</h3>
                    <form id="add-participant-form" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" required placeholder="Enter participant name">
                        </div>

                        <div class="form-group">
                            <label for="identifier">Identifier</label>
                            <input type="text" name="identifier" id="identifier" required placeholder="Enter unique identifier">
                        </div>

                        <button type="submit" class="btn btn-primary">Add Participant</button>
                    </form>

                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Identifier</th>
                                    <th>Average Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['name']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['identifier']); ?></td>
                                    <td><?php echo isset($participant['average_score']) ? number_format($participant['average_score'], 2) : '0.00'; ?></td>
                                    <td>
                                        <button class="btn btn-secondary delete-participant" data-id="<?php echo $participant['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
