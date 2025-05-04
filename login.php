<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Initialize variables
$error_message = '';
$success_message = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember_me = isset($_POST['remember_me']) ? true : false;
    
    // Validate form data
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Initialize database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Initialize authentication
        $auth = new Auth($db);
        
        // Attempt to login
        if ($auth->login($username, $password)) {
            // Set remember me cookie if checked
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                
                // In a real application, you would save the token to database
                // associated with the user's ID for verification
            }
            
            // Redirect to home page
            header("Location: index.php");
            exit;
        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMU Press</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="logo">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/logo.png"></span>
        </div>
        <h1>Login to CMU Press</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Remember me</label>
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>
        <div class="footer">
            &copy; <?php echo date('Y'); ?> CMU Press
        </div>
    </div>
</body>
</html>