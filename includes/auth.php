<?php
/**
 * Authentication Helper Class
 * Handles user authentication, login, and session management
 */

class Auth {
    private $conn;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return bool True if authenticated, false otherwise
     */
    public function login($username, $password) {
        // Sanitize inputs
        $username = htmlspecialchars(strip_tags($username));
        
        // Query to get user
        $query = "SELECT user_id, username, password, name, role FROM users WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool True if admin, false otherwise
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Get current user data
     * 
     * @return array User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['name'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
    }
    
    /**
     * Register new user
     * 
     * @param string $username Username
     * @param string $password Password
     * @param string $name Full name
     * @param string $role Role (admin or staff)
     * @return bool True if registered, false otherwise
     */
    public function register($username, $password, $name, $role = 'staff') {
        // Sanitize inputs
        $username = htmlspecialchars(strip_tags($username));
        $name = htmlspecialchars(strip_tags($name));
        $role = htmlspecialchars(strip_tags($role));
        
        // Check if username already exists
        $query = "SELECT user_id FROM users WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return false; // Username already exists
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $query = "INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $passwordHash);
        $stmt->bindParam(3, $name);
        $stmt->bindParam(4, $role);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool True if changed, false otherwise
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get current password hash
        $query = "SELECT password FROM users WHERE user_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if (password_verify($currentPassword, $row['password'])) {
                // Hash new password
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password
                $query = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $passwordHash);
                $stmt->bindParam(2, $userId);
                
                if ($stmt->execute()) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get all users
     * 
     * @return array Array of users
     */
    public function getAllUsers() {
        $query = "SELECT user_id, username, name, role, created_at FROM users ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>