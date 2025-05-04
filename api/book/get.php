<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and book model
include_once '../../config/database.php';
include_once '../../models/Book.php';

// Get book id
$book_id = isset($_GET['id']) ? $_GET['id'] : null;

// Check if book id is provided
if($book_id) {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Create book object
    $book = new Book($db);
    $book->book_id = $book_id;
    
    // Get book
    if($book->getById()) {
        // Create book array
        $book_arr = array(
            "book_id" => $book->book_id,
            "title" => $book->title,
            "author" => $book->author,
            "isbn" => $book->isbn,
            "price" => $book->price,
            "stock_qty" => $book->stock_qty,
            "low_stock_threshold" => $book->low_stock_threshold,
            "college" => $book->college,
            "created_at" => $book->created_at,
            "updated_at" => $book->updated_at
        );
        
        http_response_code(200); // OK
        echo json_encode($book_arr);
    } else {
        // Set error response
        http_response_code(404); // Not found
        echo json_encode(array("message" => "Book not found."));
    }
} else {
    // Set error response for missing book id
    http_response_code(400); // Bad request
    echo json_encode(array("message" => "Book ID is required."));
}
?>
