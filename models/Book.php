<?php
// Include logger utility
include_once __DIR__ . '/../includes/logger.php';

class Book {
    // Database connection and table name
    private $conn;
    private $table_name = "books";
    
    // Object properties
    public $book_id;
    public $title;
    public $author;
    public $isbn;
    public $price;
    public $stock_qty;
    public $low_stock_threshold;
    public $college;
    public $created_at;
    public $updated_at;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all books (non-deleted only)
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get a single book
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE book_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->book_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->isbn = $row['isbn'];
            $this->price = $row['price'];
            $this->stock_qty = $row['stock_qty'];
            $this->low_stock_threshold = $row['low_stock_threshold'];
            $this->college = $row['college'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Create book
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET title=:title, author=:author, isbn=:isbn, 
                    price=:price, stock_qty=:stock_qty, low_stock_threshold=:low_stock_threshold,
                    college=:college";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_qty = htmlspecialchars(strip_tags($this->stock_qty));
        $this->low_stock_threshold = htmlspecialchars(strip_tags($this->low_stock_threshold));
        $this->college = htmlspecialchars(strip_tags($this->college));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_qty", $this->stock_qty);
        $stmt->bindParam(":low_stock_threshold", $this->low_stock_threshold);
        $stmt->bindParam(":college", $this->college);
        
        // Execute query
        if($stmt->execute()) {
            $this->book_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update book
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title=:title, author=:author, isbn=:isbn, 
                    price=:price, stock_qty=:stock_qty, low_stock_threshold=:low_stock_threshold,
                    college=:college
                WHERE book_id=:book_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_qty = htmlspecialchars(strip_tags($this->stock_qty));
        $this->low_stock_threshold = htmlspecialchars(strip_tags($this->low_stock_threshold));
        $this->college = htmlspecialchars(strip_tags($this->college));
        
        // Bind values
        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_qty", $this->stock_qty);
        $stmt->bindParam(":low_stock_threshold", $this->low_stock_threshold);
        $stmt->bindParam(":college", $this->college);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete book (now calls softDelete for compatibility)
    public function delete() {
        // Permanently delete the book from database
        $query = "DELETE FROM " . $this->table_name . " WHERE book_id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        
        // Bind value
        $stmt->bindParam(1, $this->book_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Search books (non-deleted only)
     * @param {string} keywords Search keywords
     * @return PDOStatement
     */
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE title LIKE :keywords 
                  OR author LIKE :keywords 
                  OR isbn LIKE :keywords
                  OR book_id = :exact_id
                  ORDER BY 
                    CASE 
                        WHEN title LIKE :exact_match THEN 1
                        WHEN author LIKE :exact_match THEN 2
                        WHEN isbn = :exact_keywords THEN 3
                        ELSE 4
                    END,
                    title ASC";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize keywords
        $keywords = htmlspecialchars(strip_tags($keywords));
        $likeKeywords = "%{$keywords}%";
        $exactMatch = $keywords;
        
        // Bind variables
        $stmt->bindParam(':keywords', $likeKeywords);
        $stmt->bindParam(':exact_match', $exactMatch);
        $stmt->bindParam(':exact_keywords', $keywords);
        $stmt->bindParam(':exact_id', $keywords);
        
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get low stock books (non-deleted only)
    public function getLowStock() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE stock_qty <= low_stock_threshold 
                  ORDER BY stock_qty ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update stock quantity (decrease)
    public function updateStock($quantity) {
        // Direct file logging for debugging
        $logFile = __DIR__ . '/../logs/debug.log';
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Starting updateStock for Book ID {$this->book_id}, quantity: {$quantity}\n", FILE_APPEND);
        
        // First get current stock to check if we have enough
        $check_query = "SELECT stock_qty FROM " . $this->table_name . " WHERE book_id = ? LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->book_id);
        
        if (!$check_stmt->execute()) {
            error_log("Failed to retrieve current stock for Book ID {$this->book_id}");
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Failed to retrieve current stock for Book ID {$this->book_id}\n", FILE_APPEND);
            return false;
        }
        
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            error_log("Book ID {$this->book_id} not found when updating stock");
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Book ID {$this->book_id} not found when updating stock\n", FILE_APPEND);
            return false;
        }
        
        $current_stock = intval($row['stock_qty']);
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Current stock: {$current_stock}\n", FILE_APPEND);
        
        // Validate quantity is a positive number
        $quantity = abs(intval($quantity));
        
        // Prevent stock from going negative
        if($current_stock < $quantity) {
            // If there's not enough stock, set to 0
            $new_stock = 0;
            error_log("Warning: Book ID {$this->book_id} stock reduced to 0 (tried to remove $quantity from $current_stock)");
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Warning: Book ID {$this->book_id} stock reduced to 0 (tried to remove $quantity from $current_stock)\n", FILE_APPEND);
        } else {
            $new_stock = $current_stock - $quantity;
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Calculated new stock: {$new_stock}\n", FILE_APPEND);
        }
        
        // Update the stock directly with the calculated value
        $query = "UPDATE " . $this->table_name . " 
                  SET stock_qty = ? 
                  WHERE book_id = ?";
        
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " SQL query: {$query} with params: [{$new_stock}, {$this->book_id}]\n", FILE_APPEND);
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize book_id
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        
        // Bind values
        $stmt->bindParam(1, $new_stock);
        $stmt->bindParam(2, $this->book_id);
        
        // Execute query and log the result
        if($stmt->execute()) {
            // Update the object's property to reflect the change
            $this->stock_qty = $new_stock;
            
            // Log the stock change
            error_log("Book ID {$this->book_id} stock updated: $current_stock -> $new_stock (removed $quantity)");
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . " SUCCESS: Book ID {$this->book_id} stock updated: $current_stock -> $new_stock (removed $quantity)\n", FILE_APPEND);
            
            // Try to log using the logger
            try {
                // Use the logger utility for detailed logging
                Logger::logInventoryUpdate(
                    $this->book_id,
                    'decreased',
                    $quantity,
                    $current_stock,
                    $new_stock,
                    'API: updateStock'
                );
            } catch (Exception $e) {
                file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Logger exception: {$e->getMessage()}\n", FILE_APPEND);
            }
            
            return true;
        }
        
        error_log("Failed to update stock for Book ID {$this->book_id}");
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " FAILURE: Failed to update stock for Book ID {$this->book_id}\n", FILE_APPEND);
        
        // Try to get any SQL error
        if ($stmt->errorInfo() && isset($stmt->errorInfo()[2])) {
            $error = $stmt->errorInfo()[2];
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . " SQL Error: {$error}\n", FILE_APPEND);
        }
        
        return false;
    }
    
    // Increase stock quantity
    public function increaseStock($quantity) {
        // First get current stock
        $check_query = "SELECT stock_qty FROM " . $this->table_name . " WHERE book_id = ? LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->book_id);
        
        if (!$check_stmt->execute()) {
            error_log("Failed to retrieve current stock for Book ID {$this->book_id}");
            return false;
        }
        
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            error_log("Book ID {$this->book_id} not found when increasing stock");
            return false;
        }
        
        $current_stock = intval($row['stock_qty']);
        
        // Validate quantity is a positive number
        $quantity = abs(intval($quantity));
        
        // Calculate new stock
        $new_stock = $current_stock + $quantity;
        
        // Update the stock directly with the calculated value
        $query = "UPDATE " . $this->table_name . " 
                  SET stock_qty = ? 
                  WHERE book_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize book_id
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        
        // Bind values
        $stmt->bindParam(1, $new_stock);
        $stmt->bindParam(2, $this->book_id);
        
        // Execute query and log the result
        if($stmt->execute()) {
            // Update the object's property to reflect the change
            $this->stock_qty = $new_stock;
            
            // Log the stock change
            error_log("Book ID {$this->book_id} stock updated: $current_stock -> $new_stock (added $quantity)");
            
            // Use the logger utility for detailed logging
            Logger::logInventoryUpdate(
                $this->book_id,
                'increased',
                $quantity,
                $current_stock,
                $new_stock,
                'API: increaseStock'
            );
            
            return true;
        }
        
        error_log("Failed to increase stock for Book ID {$this->book_id}");
        return false;
    }
    
    // Count total books (non-deleted only)
    public function count() {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total_count'];
    }
    
    // Get all unique colleges
    public function getAllColleges() {
        $query = "SELECT DISTINCT college FROM " . $this->table_name . " WHERE college IS NOT NULL ORDER BY college ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get books by college
    public function getByCollege($college) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE college = ? ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $college);
        $stmt->execute();
        
        return $stmt;
    }
}
?>