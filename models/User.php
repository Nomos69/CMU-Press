<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // Object properties
    public $user_id;
    public $username;
    public $password;
    public $name;
    public $role;
    public $created_at;
    public $updated_at;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all users
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get single user by ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->username = $row['username'];
            $this->name = $row['name'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Create user
    public function create() {
        // Check if username already exists
        if ($this->usernameExists()) {
            return false;
        }
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET username=:username, password=:password, name=:name, role=:role";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":role", $this->role);
        
        // Execute query
        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update user
    public function update() {
        // Check if username already exists (except for current user)
        if ($this->usernameExists(true)) {
            return false;
        }
        
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                SET username=:username, name=:name, role=:role
                WHERE user_id=:user_id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":role", $this->role);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete user
    public function delete() {
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        // Bind value
        $stmt->bindParam(1, $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Change user password
    public function changePassword() {
        // Query to update password
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE user_id = :user_id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind values
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Check if username exists
    public function usernameExists($excludeCurrentUser = false) {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE username = ?";
        
        // If updating, exclude current user
        if ($excludeCurrentUser) {
            $query .= " AND user_id != ?";
        }
        
        $query .= " LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->username = htmlspecialchars(strip_tags($this->username));
        
        // Bind values
        $stmt->bindParam(1, $this->username);
        
        // If updating, exclude current user
        if ($excludeCurrentUser) {
            $stmt->bindParam(2, $this->user_id);
        }
        
        // Execute query
        $stmt->execute();
        
        // Check if username exists
        if ($stmt->rowCount() > 0) {
            return true;
        }
        
        return false;
    }
    
    // Verify password
    public function verifyPassword($password) {
        // Query to get password hash
        $query = "SELECT password FROM " . $this->table_name . " WHERE user_id = ? LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return password_verify($password, $row['password']);
        }
        
        return false;
    }
    
    // Count users
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>