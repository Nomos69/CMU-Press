<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and book request model
include_once '../../config/database.php';
include_once '../../models/BookRequest.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->title) && 
    !empty($data->requested_by)
) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize book request object
    $book_request = new BookRequest($db);
    
    // Set book request properties
    $book_request->title = $data->title;
    $book_request->author = !empty($data->author) ? $data->author : "";
    $book_request->requested_by = $data->requested_by;
    $book_request->priority = !empty($data->priority) ? $data->priority : "medium";
    $book_request->quantity = !empty($data->quantity) && $data->quantity > 0 ? $data->quantity : 1;
    $book_request->status = "pending";
    
    // Create the book request
    if($book_request->create()) {
        // Set response code - 201 created
        http_response_code(201);
        
        // Return success message
        echo json_encode(array(
            "message" => "Book request was created successfully.",
            "request_id" => $book_request->request_id
        ));
    } else {
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Return error message
        echo json_encode(array("message" => "Unable to create book request."));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Return error message
    echo json_encode(array("message" => "Unable to create book request. Data is incomplete."));
}
?>