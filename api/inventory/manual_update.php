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
include_once '../../includes/logger.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if required data is present
if(
    !empty($data->book_id) && 
    isset($data->quantity) && 
    !empty($data->operation) && 
    in_array($data->operation, ['set', 'increase', 'decrease'])
) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize Book object
    $book = new Book($db);
    $book->book_id = $data->book_id;
    
    // Get book data to check current stock
    if($book->getById()) {
        $current_stock = $book->stock_qty;
        $success = false;
        $message = "";
        $new_stock = 0;
        
        // Begin database transaction
        $db->beginTransaction();
        
        try {
            // Perform the requested operation
            switch($data->operation) {
                case 'set':
                    // Set stock to specific value
                    $new_stock = max(0, intval($data->quantity)); // Ensure not negative
                    $book->stock_qty = $new_stock;
                    $success = $book->update();
                    $operation = "set";
                    break;
                    
                case 'decrease':
                    // Check if there's enough stock
                    if($current_stock < intval($data->quantity)) {
                        // Not enough stock available
                        $message = "Warning: Insufficient stock. Available: {$current_stock}, Requested: {$data->quantity}";
                        error_log($message);
                        
                        // Still allow the decrease but set to 0
                        $new_stock = 0;
                        $book->stock_qty = $new_stock;
                        $success = $book->update();
                        
                        // Add additional context to the message
                        $message .= ". Stock set to 0.";
                    } else {
                        // Decrease stock
                        $success = $book->updateStock($data->quantity);
                    }
                    $operation = "decreased";
                    break;
                    
                case 'increase':
                    // Increase stock
                    $success = $book->increaseStock($data->quantity);
                    $operation = "increased";
                    break;
            }
            
            // Validate the operation was successful
            if($success) {
                // Get updated book data
                $book->getById();
                $new_stock = $book->stock_qty;
                
                // Log this manual update
                Logger::logInventoryUpdate(
                    $book->book_id,
                    "manual-{$operation}",
                    $data->quantity,
                    $current_stock,
                    $new_stock,
                    isset($data->context) ? $data->context : 'API: manual_update'
                );
                
                // Commit transaction
                $db->commit();
                
                // Set response code - 200 success
                http_response_code(200);
                
                // Return success message with before/after stock values
                echo json_encode(array(
                    "message" => !empty($message) ? $message : "Stock {$operation} successfully.",
                    "book_id" => $book->book_id,
                    "title" => $book->title,
                    "previous_stock" => $current_stock,
                    "quantity_changed" => $data->quantity,
                    "new_stock" => $new_stock,
                    "success" => true
                ));
            } else {
                // Roll back if unsuccessful
                $db->rollBack();
                
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Return error message
                echo json_encode(array(
                    "message" => $message ?: "Unable to update stock.",
                    "book_id" => $book->book_id,
                    "title" => $book->title,
                    "current_stock" => $current_stock,
                    "success" => false
                ));
            }
        } catch(Exception $e) {
            // Roll back on exception
            $db->rollBack();
            
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Return error message
            echo json_encode(array(
                "message" => "Error: " . $e->getMessage(),
                "book_id" => $book->book_id,
                "title" => $book->title,
                "current_stock" => $current_stock
            ));
        }
    } else {
        // Book not found
        http_response_code(404);
        
        // Return error message
        echo json_encode(array("message" => "Book not found."));
    }
} else {
    // Missing required data
    http_response_code(400);
    
    // Return error message
    echo json_encode(array(
        "message" => "Missing required data. Required: book_id, quantity, operation (set/increase/decrease)."
    ));
}
?> 