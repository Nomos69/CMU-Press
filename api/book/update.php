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
if(
    !empty($data->book_id)
) {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Create book object
    $book = new Book($db);
    
    // Set book properties
    $book->book_id = $data->book_id;
    
    // Check which fields are being updated
    if (isset($data->title)) $book->title = $data->title;
    if (isset($data->author)) $book->author = $data->author;
    if (isset($data->isbn)) $book->isbn = $data->isbn;
    if (isset($data->price)) $book->price = $data->price;
    if (isset($data->stock_qty)) $book->stock_qty = $data->stock_qty;
    if (isset($data->low_stock_threshold)) $book->low_stock_threshold = $data->low_stock_threshold;
    if (isset($data->college)) $book->college = $data->college;
    
    // Update the book
    if($book->update()) {
        // Set response
        $response = array(
            "message" => "Book updated successfully."
        );
        http_response_code(200); // OK
    } else {
        // Set error response
        $response = array(
            "message" => "Unable to update book."
        );
        http_response_code(503); // Service unavailable
    }
} else {
    // Set error response for incomplete data
    $response = array(
        "message" => "Unable to update book. Data is incomplete."
    );
    http_response_code(400); // Bad request
}

// Return response as JSON
echo json_encode($response);
?>
