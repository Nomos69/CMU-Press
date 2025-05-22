<?php
// Require authentication
requireLogin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Load user model
require_once 'models/User.php';
$user = new User($db);

// Check if admin for certain settings
$isAdmin = $_SESSION['role'] === 'admin';

// Get current user details
$currentUserId = $_SESSION['user_id'];
$currentUser = [
    'user_id' => $currentUserId,
    'username' => $_SESSION['username'],
    'name' => $_SESSION['name'],
    'role' => $_SESSION['role']
];

// Get all users if admin
$users = [];
if ($isAdmin) {
    $stmt = $db->prepare("SELECT * FROM users ORDER BY name ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get system settings
// $taxRate = defined('TAX_RATE') ? TAX_RATE : 0.08; // Default to 8%
// $appName = defined('APP_NAME') ? APP_NAME : 'Bookstore POS';
// $appVersion = defined('APP_VERSION') ? APP_VERSION : '1.0.0';

// Process password change
$passwordChanged = false;
$passwordError = '';
$userCreated = false;
$userError = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $userName = $_POST['user_name'] ?? '';
    $userUsername = $_POST['user_username'] ?? '';
    $userPassword = $_POST['user_password'] ?? '';
    $userConfirmPassword = $_POST['user_confirm_password'] ?? '';
    $userRole = $_POST['user_role'] ?? '';
    
    if (empty($userName) || empty($userUsername) || empty($userPassword) || empty($userConfirmPassword) || empty($userRole)) {
        $userError = 'All user fields are required';
    } else if ($userPassword !== $userConfirmPassword) {
        $userError = 'Passwords do not match';
    } else {
        // Create new user
        $newUser = new User($db);
        $newUser->name = $userName;
        $newUser->username = $userUsername;
        $newUser->password = $userPassword;
        $newUser->role = $userRole;
        
        if ($newUser->create()) {
            $userCreated = true;
        } else {
            if ($newUser->usernameExists()) {
                $userError = 'Username already exists';
            } else {
                $userError = 'Failed to create user';
            }
        }
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = 'All password fields are required';
    } else if ($newPassword !== $confirmPassword) {
        $passwordError = 'New password and confirmation do not match';
    } else {
        // Verify current password and change to new one
        $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bindParam(1, $currentUserId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bindParam(1, $hashedPassword);
            $stmt->bindParam(2, $currentUserId);
            
            if ($stmt->execute()) {
                $passwordChanged = true;
            } else {
                $passwordError = 'Failed to update password';
            }
        } else {
            $passwordError = 'Current password is incorrect';
        }
    }
}
?>

<section id="settings" class="tab-content active">
    <div class="container">
        <div class="left-column">
            <div class="card">
                <div class="card-header">
                    <h2>Account Settings</h2>
                </div>
                <div class="card-body">
                    <div class="settings-section">
                        <h3>Profile Information</h3>
                        <div class="profile-info">
                            <div class="profile-field">
                                <span class="field-label">Username:</span>
                                <span class="field-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            </div>
                            <div class="profile-field">
                                <span class="field-label">Name:</span>
                                <span class="field-value"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                            </div>
                            <div class="profile-field">
                                <span class="field-label">Role:</span>
                                <span class="field-value"><?php echo ucfirst(htmlspecialchars($currentUser['role'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Change Password</h3>
                        <?php if ($passwordChanged): ?>
                            <div class="alert alert-success">Password changed successfully!</div>
                        <?php endif; ?>
                        
                        <?php if ($passwordError): ?>
                            <div class="alert alert-danger"><?php echo $passwordError; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" class="password-form">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>            <div class="card">
                <div class="card-header">
                    <h2>User Management</h2>
                    <button id="add-user-btn" class="btn-primary"><i class="fas fa-plus"></i> Add User</button>
                </div>
                <div class="card-body">
                    <?php if ($userCreated): ?>
                        <div class="alert alert-success">User was created successfully!</div>
                    <?php endif; ?>
                    
                    <?php if ($userError): ?>
                        <div class="alert alert-danger"><?php echo $userError; ?></div>
                    <?php endif; ?>
                    
                    <div class="users-table-wrapper">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="actions">
                                        <button class="edit-user-btn" data-id="<?php echo $user['user_id']; ?>"><i class="fas fa-edit"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="right-column">
                <div class="card-body">
                    <?php if ($isAdmin): ?>
                    <div class="settings-section">
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>About</h2>
                </div>
                <div class="card-body">
                    <div class="about-content">
                        <p><strong><?php echo htmlspecialchars($appName); ?></strong>CMU Press ISMS is a system designed to manage book inventory, sales, and reports.</p>
                        <p>Version: <?php echo htmlspecialchars($appVersion); ?></p>
                        <p><strong>Developed by:</strong><br>
                        Rhimar Clifford Capunong <br>
                        Audebos Hyro Busaco<br>
                        Juhn Saint Sanchez </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- User Add/Edit Modal Template -->
<template id="user-template">
    <form method="post" action="" class="user-form">
        <div class="form-group">
            <label for="user_name">Full Name*</label>
            <input type="text" id="user_name" name="user_name" placeholder="Enter full name" required>
        </div>
        <div class="form-group">
            <label for="user_username">Username*</label>
            <input type="text" id="user_username" name="user_username" placeholder="Enter username" required>
        </div>
        <div class="form-group password-field">
            <label for="user_password">Password*</label>
            <input type="password" id="user_password" name="user_password" placeholder="Enter password" required>
        </div>
        <div class="form-group password-field">
            <label for="user_confirm_password">Confirm Password*</label>
            <input type="password" id="user_confirm_password" name="user_confirm_password" placeholder="Confirm password" required>
        </div>
        <div class="form-group">
            <label for="user_role">Role*</label>
            <select id="user_role" name="user_role" required>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <p class="form-note">* Required fields</p>
        <button type="submit" name="create_user" class="btn-primary">Create User</button>
    </form>
</template>

<script src="assets/js/settings.js"></script>