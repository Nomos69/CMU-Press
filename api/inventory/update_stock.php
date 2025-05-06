<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and required models
include_once '../../config/database.php';
include_once '../../models/Book.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if required data is present
if(!empty($data->book_id) && isset($data->quantity) && isset($data->operation)) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize Book object
    $book = new Book($db);
    $book->book_id = $data->book_id;
    
    // Get book data to check current stock
    if($book->getById()) {
        $current_stock = $book->stock_qty;
        
        // Perform the requested operation
        $success = false;
        $message = "";
        
        switch($data->operation) {
            case 'decrease':
                $success = $book->updateStock($data->quantity);
                $operation = "decreased";
                break;
                
            case 'increase':
                $success = $book->increaseStock($data->quantity);
                $operation = "increased";
                break;
                
            default:
                $message = "Invalid operation. Use 'increase' or 'decrease'.";
                break;
        }
        
        // Get updated book data
        $book->getById();
        $new_stock = $book->stock_qty;
        
        if($success) {
            // Set response code - 200 success
            http_response_code(200);
            
            // Return success message with before/after stock values
            echo json_encode(array(
                "message" => "Stock {$operation} successfully.",
                "book_id" => $book->book_id,
                "title" => $book->title,
                "previous_stock" => $current_stock,
                "quantity_changed" => $data->quantity,
                "new_stock" => $new_stock
            ));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Return error message
            echo json_encode(array(
                "message" => $message ?: "Unable to update stock."
            ));
        }
    } else {
        // Book not found
        http_response_code(404);
        
        // Return error message
        echo json_encode(array("message" => "Book not found."));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Return error message
    echo json_encode(array("message" => "Unable to update stock. Data is incomplete."));
}
?> 