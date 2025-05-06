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
    !empty($data->items) && 
    is_array($data->items) &&
    !empty($data->transaction_id)
) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Begin database transaction
    $db->beginTransaction();
    
    try {
        $results = array();
        $success = true;
        
        // Process each book item
        foreach($data->items as $item) {
            if(empty($item->book_id) || empty($item->quantity)) {
                continue; // Skip items with missing data
            }
            
            // Initialize Book object
            $book = new Book($db);
            $book->book_id = $item->book_id;
            
            // Get current book data
            if($book->getById()) {
                $current_stock = $book->stock_qty;
                $quantity = intval($item->quantity);
                
                // Log the operation
                error_log("Updating inventory after sale: Book ID {$book->book_id}, Quantity: {$quantity}, Current stock: {$current_stock}");
                
                // Update stock
                $result = array(
                    "book_id" => $book->book_id,
                    "title" => $book->title,
                    "previous_stock" => $current_stock
                );
                
                // Check if there's enough stock
                if($current_stock < $quantity) {
                    // Not enough stock, set to 0
                    $book->stock_qty = 0;
                    $isUpdated = $book->update();
                    $new_stock = 0;
                    
                    $result["message"] = "Insufficient stock. Stock set to 0.";
                    $result["warning"] = true;
                } else {
                    // Decrease stock
                    $isUpdated = $book->updateStock($quantity);
                    $new_stock = $current_stock - $quantity;
                    
                    $result["message"] = "Stock updated successfully";
                    $result["warning"] = false;
                }
                
                if($isUpdated) {
                    // Get updated stock for reporting
                    $book->getById();
                    $result["new_stock"] = $book->stock_qty;
                    
                    // Log the inventory change with transaction context
                    Logger::logInventoryUpdate(
                        $book->book_id,
                        'sale-decrease',
                        $quantity,
                        $current_stock,
                        $book->stock_qty,
                        "Transaction ID: {$data->transaction_id}"
                    );
                } else {
                    $success = false;
                    $result["error"] = "Failed to update stock";
                }
                
                $results[] = $result;
            } else {
                // Book not found
                $results[] = array(
                    "book_id" => $item->book_id,
                    "error" => "Book not found",
                    "success" => false
                );
                $success = false;
            }
        }
        
        if($success) {
            // Commit the transaction
            $db->commit();
            
            // Set response code - 200 success
            http_response_code(200);
            
            // Return success response
            echo json_encode(array(
                "message" => "Inventory updated successfully after sale",
                "transaction_id" => $data->transaction_id,
                "results" => $results,
                "success" => true
            ));
        } else {
            // Rollback the transaction
            $db->rollBack();
            
            // Set response code - 500 error
            http_response_code(500);
            
            // Return error response
            echo json_encode(array(
                "message" => "Failed to update inventory after sale",
                "transaction_id" => $data->transaction_id,
                "results" => $results,
                "success" => false
            ));
        }
    } catch (Exception $e) {
        // Rollback the transaction on exception
        $db->rollBack();
        
        // Set response code - 500 error
        http_response_code(500);
        
        // Return error response
        echo json_encode(array(
            "message" => "Error: " . $e->getMessage(),
            "success" => false
        ));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Return error response
    echo json_encode(array(
        "message" => "Invalid request data. Required: items (array) and transaction_id",
        "success" => false
    ));
}
?> 