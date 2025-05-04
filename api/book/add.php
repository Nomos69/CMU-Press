<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and book model
include_once '../../config/database.php';
include_once '../../models/Book.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Initialize response array
$response = array();

// Check if required data is present
if (
    !empty($data->title) &&
    !empty($data->author) &&
    !empty($data->price) &&
    isset($data->stock_qty)
) {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Create book object
    $book = new Book($db);
    
    // Set book properties
    $book->title = $data->title;
    $book->author = $data->author;
    $book->isbn = $data->isbn ?? null;
    $book->price = $data->price;
    $book->stock_qty = $data->stock_qty;
    $book->low_stock_threshold = $data->low_stock_threshold ?? 5;
    $book->college = $data->college ?? null;
    
    // Create the book
    if($book->create()) {
        // Set response
        $response = array(
            "message" => "Book added successfully.",
            "book_id" => $book->book_id
        );
        http_response_code(201); // Created
    } else {
        // Set error response
        $response = array(
            "message" => "Unable to add book."
        );
        http_response_code(503); // Service unavailable
    }
} else {
    // Set error response for incomplete data
    $response = array(
        "message" => "Unable to add book. Data is incomplete."
    );
    http_response_code(400); // Bad request
}

// Return response as JSON
echo json_encode($response);
?>
