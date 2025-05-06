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

// Get book ID from URL
$book_id = isset($_GET['book_id']) ? $_GET['book_id'] : die(json_encode(array("message" => "Missing book ID parameter")));

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Book object
$book = new Book($db);
$book->book_id = $book_id;

// Get book data
if($book->getById()) {
    // Book found, return current stock quantity
    http_response_code(200);
    echo json_encode(array(
        "book_id" => $book->book_id,
        "title" => $book->title,
        "author" => $book->author,
        "current_stock" => $book->stock_qty,
        "low_stock_threshold" => $book->low_stock_threshold
    ));
} else {
    // Book not found
    http_response_code(404);
    echo json_encode(array("message" => "Book not found"));
}
?> 