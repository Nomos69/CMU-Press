<?php
class BookRequest {
    // Database connection and table name
    private $conn;
    private $table_name = "book_requests";
    
    // Object properties
    public $request_id;
    public $title;
    public $author;
    public $requested_by;
    public $request_date;
    public $priority;
    public $quantity;
    public $status;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create book request
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET title=:title, author=:author, requested_by=:requested_by, 
                    priority=:priority, quantity=:quantity, status=:status";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->requested_by = htmlspecialchars(strip_tags($this->requested_by));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":requested_by", $this->requested_by);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            $this->request_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Get all book requests
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY request_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get pending book requests
    public function getPending() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = 'pending' 
                  ORDER BY CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                  END, request_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get a single book request
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE request_id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->request_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->requested_by = $row['requested_by'];
            $this->request_date = $row['request_date'];
            $this->priority = $row['priority'];
            $this->quantity = $row['quantity'];
            $this->status = $row['status'];
            
            return true;
        }
        
        return false;
    }
    
    // Update book request status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE request_id = :request_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":request_id", $this->request_id);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update book request
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title = :title, author = :author, requested_by = :requested_by,
                    priority = :priority, quantity = :quantity, status = :status
                WHERE request_id = :request_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->requested_by = htmlspecialchars(strip_tags($this->requested_by));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":request_id", $this->request_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":requested_by", $this->requested_by);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete book request
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE request_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));
        
        // Bind value
        $stmt->bindParam(1, $this->request_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Count pending requests
    public function countPending() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = 'pending'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    // Count high priority pending requests
    public function countHighPriority() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = 'pending' AND priority = 'high'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    // Search book requests
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE title LIKE ? OR author LIKE ? OR requested_by LIKE ? 
                  ORDER BY request_date DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize keywords
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // Bind variables
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        
        $stmt->execute();
        
        return $stmt;
    }
}
?>