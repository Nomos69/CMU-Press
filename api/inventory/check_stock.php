<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and required models
include_once '../../config/database.php';
include_once '../../models/Book.php';
include_once '../../includes/logger.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Check if book_id was provided
if(isset($_GET['book_id'])) {
    // Get book by ID
    $book = new Book($db);
    $book->book_id = $_GET['book_id'];
    
    if($book->getById()) {
        // Book found, return stock info
        http_response_code(200);
        echo json_encode(array(
            "book_id" => $book->book_id,
            "title" => $book->title,
            "stock_qty" => $book->stock_qty,
            "low_stock_threshold" => $book->low_stock_threshold,
            "status" => $book->stock_qty <= 0 ? "out_of_stock" : 
                        ($book->stock_qty <= $book->low_stock_threshold ? "low_stock" : "in_stock")
        ));
        
        // Log this check for auditing
        Logger::logInventoryUpdate(
            $book->book_id,
            'checked',
            0,
            $book->stock_qty,
            $book->stock_qty,
            'API: check_stock'
        );
    } else {
        // Book not found
        http_response_code(404);
        echo json_encode(array("message" => "Book not found."));
    }
} else {
    // List all books with low stock or out of stock
    $book = new Book($db);
    $low_stock = $book->getLowStock();
    $low_stock_count = $low_stock->rowCount();
    
    // Check if any books found
    if($low_stock_count > 0) {
        // Books array
        $books_arr = array();
        $books_arr["records"] = array();
        $books_arr["count"] = $low_stock_count;
        
        // Retrieve table contents
        while($row = $low_stock->fetch(PDO::FETCH_ASSOC)) {
            $book_item = array(
                "book_id" => $row['book_id'],
                "title" => $row['title'],
                "author" => $row['author'],
                "stock_qty" => $row['stock_qty'],
                "low_stock_threshold" => $row['low_stock_threshold'],
                "status" => $row['stock_qty'] <= 0 ? "out_of_stock" : "low_stock"
            );
            
            array_push($books_arr["records"], $book_item);
        }
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Show books data in JSON format
        echo json_encode($books_arr);
    } else {
        // No books found with low stock
        http_response_code(200);
        echo json_encode(array(
            "message" => "No books with low stock found.",
            "count" => 0,
            "records" => array()
        ));
    }
}
?> 