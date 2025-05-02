<?php
class TransactionItem {
    // Database connection and table name
    private $conn;
    private $table_name = "transaction_items";
    
    // Object properties
    public $item_id;
    public $transaction_id;
    public $book_id;
    public $quantity;
    public $price_per_unit;
    public $total_price;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create transaction item
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET transaction_id=:transaction_id, book_id=:book_id, 
                    quantity=:quantity, price_per_unit=:price_per_unit, total_price=:total_price";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->price_per_unit = htmlspecialchars(strip_tags($this->price_per_unit));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));
        
        // Bind values
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":price_per_unit", $this->price_per_unit);
        $stmt->bindParam(":total_price", $this->total_price);
        
        // Execute query
        if($stmt->execute()) {
            $this->item_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read all transaction items for a specific transaction
    public function getByTransaction() {
        $query = "SELECT ti.*, b.title, b.author 
                 FROM " . $this->table_name . " ti
                 JOIN books b ON ti.book_id = b.book_id
                 WHERE ti.transaction_id = ?
                 ORDER BY ti.item_id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->transaction_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single transaction item
    public function readOne() {
        $query = "SELECT ti.*, b.title, b.author 
                 FROM " . $this->table_name . " ti
                 JOIN books b ON ti.book_id = b.book_id
                 WHERE ti.item_id = ?
                 LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->item_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->item_id = $row['item_id'];
            $this->transaction_id = $row['transaction_id'];
            $this->book_id = $row['book_id'];
            $this->quantity = $row['quantity'];
            $this->price_per_unit = $row['price_per_unit'];
            $this->total_price = $row['total_price'];
            
            return true;
        }
        
        return false;
    }
    
    // Update transaction item
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET quantity=:quantity, price_per_unit=:price_per_unit, total_price=:total_price
                WHERE item_id=:item_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->price_per_unit = htmlspecialchars(strip_tags($this->price_per_unit));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));
        
        // Bind values
        $stmt->bindParam(':item_id', $this->item_id);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':price_per_unit', $this->price_per_unit);
        $stmt->bindParam(':total_price', $this->total_price);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete transaction item
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        
        // Bind parameter
        $stmt->bindParam(1, $this->item_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete transaction items by transaction ID
    public function deleteByTransaction() {
        $query = "DELETE FROM " . $this->table_name . " WHERE transaction_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        
        // Bind parameter
        $stmt->bindParam(1, $this->transaction_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get total books sold today
    public function getTotalBooksSold() {
        $query = "SELECT SUM(ti.quantity) as total_books
                 FROM " . $this->table_name . " ti
                 JOIN transactions t ON ti.transaction_id = t.transaction_id
                 WHERE DATE(t.transaction_date) = CURDATE() AND t.status = 'completed'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_books'] ? $row['total_books'] : 0;
    }
    
    // Get total books sold for a specific date range
    public function getTotalBooksSoldByDateRange($start_date, $end_date) {
        $query = "SELECT SUM(ti.quantity) as total_books
                 FROM " . $this->table_name . " ti
                 JOIN transactions t ON ti.transaction_id = t.transaction_id
                 WHERE DATE(t.transaction_date) BETWEEN ? AND ? AND t.status = 'completed'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_books'] ? $row['total_books'] : 0;
    }
    
    // Get bestselling books for today
    public function getBestsellingBooks($limit = 5) {
        $query = "SELECT ti.book_id, b.title, b.author, SUM(ti.quantity) as total_sold
                 FROM " . $this->table_name . " ti
                 JOIN transactions t ON ti.transaction_id = t.transaction_id
                 JOIN books b ON ti.book_id = b.book_id
                 WHERE DATE(t.transaction_date) = CURDATE() AND t.status = 'completed'
                 GROUP BY ti.book_id
                 ORDER BY total_sold DESC
                 LIMIT " . $limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get bestselling books for a specific date range
    public function getBestsellingBooksByDateRange($start_date, $end_date, $limit = 10) {
        $query = "SELECT ti.book_id, b.title, b.author, SUM(ti.quantity) as total_sold
                 FROM " . $this->table_name . " ti
                 JOIN transactions t ON ti.transaction_id = t.transaction_id
                 JOIN books b ON ti.book_id = b.book_id
                 WHERE DATE(t.transaction_date) BETWEEN ? AND ? AND t.status = 'completed'
                 GROUP BY ti.book_id
                 ORDER BY total_sold DESC
                 LIMIT " . $limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        return $stmt;
    }
}
?>