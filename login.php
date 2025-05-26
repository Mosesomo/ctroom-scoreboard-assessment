<?php
// File: login.php
// User login page

require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                $pdo = getDBConnection();
                if (loginUser($pdo, $username, $password)) {
                    // Redirect based on role
                    if (hasRole('admin')) {
                        redirect('admin.php');
                    } elseif (hasRole('judge')) {
                        redirect('judge.php');
                    } else {
                        redirect('index.php');
                    }
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (Exception $e) {
                $error = 'Login failed. Please try again.';
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// Handle URL parameters
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'login_required':
            $error = 'Please log in to access that page.';
            break;
        case 'access_denied':
            $error = 'Access denied. Insufficient permissions.';
            break;
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'logout':
            $success = 'You have been logged out successfully.';
            break;
        case 'registered':
            $success = 'Account created successfully. You can now log in.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Judge Scoreboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>Judge Scoreboard</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Scoreboard</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="card login-container">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p class="text-secondary">Enter your credentials to access the judge panel</p>
            </div>

            <?php if ($error): ?>
                <div class="notification error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="notification success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>

                <button type="submit" name="login" class="btn btn-primary">Sign In</button>
            </form>

            <div class="back-link">
                <a href="index.php" class="btn btn-secondary">&larr; Back to Scoreboard</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Judge Scoreboard. All rights reserved.</p>
    </footer>
</body>
</html>
