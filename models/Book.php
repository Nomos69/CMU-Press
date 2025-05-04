<?php
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
    
    // Search books (non-deleted only)
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?
                  ORDER BY title ASC";
        
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
    
    // Get low stock books (non-deleted only)
    public function getLowStock() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE stock_qty <= low_stock_threshold 
                  ORDER BY stock_qty ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update stock quantity
    public function updateStock($quantity) {
        $query = "UPDATE " . $this->table_name . " 
                  SET stock_qty = stock_qty - ? 
                  WHERE book_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $quantity = htmlspecialchars(strip_tags($quantity));
        
        // Bind values
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $this->book_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
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