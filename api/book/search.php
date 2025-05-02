<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and book model
include_once '../../config/database.php';
include_once '../../models/Book.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize book object
$book = new Book($db);

// Get keywords from request
$keywords = isset($_GET["q"]) ? $_GET["q"] : "";

// Search for books
$stmt = $book->search($keywords);
$num = $stmt->rowCount();

// Check if any books found
if($num > 0) {
    // Books array
    $books_arr = array();
    $books_arr["books"] = array();
    
    // Retrieve results
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $book_item = array(
            "book_id" => $book_id,
            "title" => $title,
            "author" => $author,
            "isbn" => $isbn,
            "price" => $price,
            "stock_qty" => $stock_qty,
            "low_stock_threshold" => $low_stock_threshold,
            "in_stock" => $stock_qty > 0,
            "low_stock" => $stock_qty <= $low_stock_threshold && $stock_qty > 0
        );
        
        array_push($books_arr["books"], $book_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Return JSON response
    echo json_encode($books_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Return no books found message
    echo json_encode(array("message" => "No books found matching your search."));
}
?>