<?php
class Transaction {
    // Database connection and table name
    private $conn;
    private $table_name = "transactions";
    
    // Object properties
    public $transaction_id;
    public $customer_id;
    public $user_id;
    public $status;
    public $payment_method;
    public $subtotal;
    public $tax;
    public $discount;
    public $total;
    public $transaction_date;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create transaction
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET customer_id=:customer_id, user_id=:user_id, status=:status, 
                    payment_method=:payment_method, subtotal=:subtotal, tax=:tax, 
                    discount=:discount, total=:total";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->subtotal = htmlspecialchars(strip_tags($this->subtotal));
        $this->tax = htmlspecialchars(strip_tags($this->tax));
        $this->discount = htmlspecialchars(strip_tags($this->discount));
        $this->total = htmlspecialchars(strip_tags($this->total));
        
        // Bind values
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":subtotal", $this->subtotal);
        $stmt->bindParam(":tax", $this->tax);
        $stmt->bindParam(":discount", $this->discount);
        $stmt->bindParam(":total", $this->total);
        
        // Execute query
        if($stmt->execute()) {
            $this->transaction_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read single transaction
    public function readOne() {
        // Query to read single record
        $query = "SELECT t.*, c.name as customer_name
                FROM " . $this->table_name . " t
                LEFT JOIN customers c ON t.customer_id = c.customer_id
                WHERE t.transaction_id = ?
                LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind transaction_id
        $stmt->bindParam(1, $this->transaction_id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if($row) {
            $this->transaction_id = $row['transaction_id'];
            $this->customer_id = $row['customer_id'];
            $this->user_id = $row['user_id'];
            $this->status = $row['status'];
            $this->payment_method = $row['payment_method'];
            $this->subtotal = $row['subtotal'];
            $this->tax = $row['tax'];
            $this->discount = $row['discount'];
            $this->total = $row['total'];
            $this->transaction_date = $row['transaction_date'];
            
            return true;
        }
        
        return false;
    }
    
    // Get recent transactions
    public function getRecent($limit = 10) {
        $query = "SELECT t.transaction_id, t.transaction_date, c.name as customer_name, 
                 COUNT(ti.item_id) as item_count, t.total, t.status
                 FROM " . $this->table_name . " t
                 LEFT JOIN customers c ON t.customer_id = c.customer_id
                 LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
                 GROUP BY t.transaction_id
                 ORDER BY t.transaction_date DESC
                 LIMIT " . $limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update transaction status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE transaction_id = :transaction_id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get day statistics
    public function getDayStats() {
        $query = "SELECT SUM(total) as total_sales, COUNT(*) as transaction_count
                 FROM " . $this->table_name . "
                 WHERE DATE(transaction_date) = CURDATE() AND status = 'completed'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get previous day statistics
    public function getPreviousDayStats() {
        $query = "SELECT SUM(total) as total_sales, COUNT(*) as transaction_count
                 FROM " . $this->table_name . "
                 WHERE DATE(transaction_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status = 'completed'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get transactions by date range
    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT t.transaction_id, t.transaction_date, c.name as customer_name, 
                 COUNT(ti.item_id) as item_count, t.total, t.status
                 FROM " . $this->table_name . " t
                 LEFT JOIN customers c ON t.customer_id = c.customer_id
                 LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
                 WHERE DATE(t.transaction_date) BETWEEN ? AND ?
                 GROUP BY t.transaction_id
                 ORDER BY t.transaction_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get transactions by customer
    public function getByCustomer() {
        $query = "SELECT t.transaction_id, t.transaction_date, 
                 COUNT(ti.item_id) as item_count, t.total, t.status
                 FROM " . $this->table_name . " t
                 LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
                 WHERE t.customer_id = ?
                 GROUP BY t.transaction_id
                 ORDER BY t.transaction_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->customer_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>