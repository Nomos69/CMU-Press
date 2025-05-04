<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include necessary files
include_once '../../config/database.php';
include_once '../../models/BookRequest.php';
include_once '../../models/Book.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->request_id) && !empty($data->status)) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize book request object
    $book_request = new BookRequest($db);
    
    // Set properties
    $book_request->request_id = $data->request_id;
    
    // Get the request details before updating
    if($book_request->getById()) {
        $original_status = $book_request->status;
        $book_request->status = $data->status;
        
        // Update the request status
        if($book_request->updateStatus()) {
            $response = array("message" => "Book request status updated successfully.");
            
            // If the status is being changed to 'fulfilled', add the book to inventory
            if($data->status === 'fulfilled' && $original_status !== 'fulfilled') {
                // Initialize book object
                $book = new Book($db);
                
                // Set book properties
                $book->title = $book_request->title;
                $book->author = $book_request->author;
                $book->isbn = null; // ISBN needs to be set manually later
                $book->price = 0.00; // Price needs to be set manually later
                $book->stock_qty = $book_request->quantity;
                $book->low_stock_threshold = 5; // Default threshold
                
                // Create the book in inventory
                if($book->create()) {
                    $response["book_added"] = true;
                    $response["book_id"] = $book->book_id;
                    $response["message"] .= " Book has been added to inventory.";
                } else {
                    $response["book_added"] = false;
                    $response["message"] .= " Note: Failed to add book to inventory.";
                }
            }
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Return success message
            echo json_encode($response);
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Return error message
            echo json_encode(array("message" => "Unable to update book request status."));
        }
    } else {
        // Set response code - 404 not found
        http_response_code(404);
        
        // Return error message
        echo json_encode(array("message" => "Book request not found."));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Return error message
    echo json_encode(array("message" => "Unable to update book request status. Data is incomplete."));
}
?>
