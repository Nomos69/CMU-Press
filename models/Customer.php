<?php
class Customer {
    // Database connection and table name
    private $conn;
    private $table_name = "customers";
    
    // Object properties
    public $customer_id;
    public $name;
    public $email;
    public $phone;
    public $created_at;
    public $updated_at;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create customer
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET name=:name, email=:email, phone=:phone";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        
        // Execute query
        if ($stmt->execute()) {
            $this->customer_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read all customers
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single customer
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE customer_id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->customer_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Update customer
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET name=:name, email=:email, phone=:phone
                WHERE customer_id=:customer_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        // Bind values
        $stmt->bindParam(':customer_id', $this->customer_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete customer
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE customer_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        
        // Bind parameter
        $stmt->bindParam(1, $this->customer_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Search customers
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? 
                  ORDER BY name ASC";
        
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
    
    // Count total customers
    public function count() {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_count'];
    }
    
    // Count new customers today
    public function countNewToday() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE(created_at) = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    // This method has been modified to count all new customers today
    // since the loyalty card field has been removed
    public function countNewLoyaltyToday() {
        return $this->countNewToday();
    }
    
    // Get customer by email
    public function getByEmail() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->customer_id = $row['customer_id'];
            $this->name = $row['name'];
            $this->phone = $row['phone'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Get customer by phone
    public function getByPhone() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE phone = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->phone);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->customer_id = $row['customer_id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Get customers (all customers, since loyalty card field was removed)
    public function getLoyaltyCustomers() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Count all customers (since loyalty card field was removed)
    public function countLoyaltyCustomers() {
        return $this->count();
    }
}
?>