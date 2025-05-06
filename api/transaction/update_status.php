<?php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and required models
include_once '../../config/database.php';
include_once '../../models/Transaction.php';
include_once '../../models/TransactionItem.php';
include_once '../../models/Book.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->transaction_id) && 
    !empty($data->status)
) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Initialize transaction object
        $transaction = new Transaction($db);
        $transaction->transaction_id = $data->transaction_id;
        
        // Get current transaction data
        if($transaction->readOne()) {
            // Check for status change
            $oldStatus = $transaction->status;
            $newStatus = $data->status;
            
            // Only proceed if status is actually changing
            if($oldStatus !== $newStatus) {
                // Update transaction status
                $transaction->status = $newStatus;
                
                if($transaction->updateStatus()) {
                    // Handle inventory updates based on status change
                    $transaction_item = new TransactionItem($db);
                    $transaction_item->transaction_id = $transaction->transaction_id;
                    
                    // Get all items for this transaction
                    $transaction_items = $transaction_item->getByTransaction();
                    $items_processed = true;
                    
                    // Process inventory changes based on status transitions
                    if($oldStatus !== "completed" && $newStatus === "completed") {
                        // Status changed TO completed - decrease inventory
                        error_log("Transaction #{$transaction->transaction_id} status changed from {$oldStatus} to {$newStatus} - decreasing inventory");
                        
                        while($row = $transaction_items->fetch(PDO::FETCH_ASSOC)) {
                            $book = new Book($db);
                            $book->book_id = $row['book_id'];
                            
                            if($book->getById()) {
                                // Log before updating
                                error_log("Book #{$row['book_id']} current stock: {$book->stock_qty}, will decrease by {$row['quantity']}");
                                
                                // Decrease stock by quantity sold - DIRECT UPDATE FOR RELIABILITY
                                if(!$book->updateStock($row['quantity'])) {
                                    $items_processed = false;
                                    error_log("Failed to update stock for book ID: " . $row['book_id']);
                                    break;
                                }
                                
                                // Get updated stock
                                $book->getById();
                                error_log("Book #{$row['book_id']} new stock after decrease: {$book->stock_qty}");
                            } else {
                                $items_processed = false;
                                error_log("Book not found with ID: " . $row['book_id']);
                                break;
                            }
                        }
                    } 
                    else if($oldStatus === "completed" && $newStatus !== "completed") {
                        // Status changed FROM completed - increase inventory (return items to stock)
                        error_log("Transaction #{$transaction->transaction_id} status changed from {$oldStatus} to {$newStatus} - returning inventory");
                        
                        while($row = $transaction_items->fetch(PDO::FETCH_ASSOC)) {
                            $book = new Book($db);
                            $book->book_id = $row['book_id'];
                            
                            if($book->getById()) {
                                // Log before updating
                                error_log("Book #{$row['book_id']} current stock: {$book->stock_qty}, will increase by {$row['quantity']}");
                                
                                // Add the quantity back to stock using the increaseStock method - DIRECT UPDATE FOR RELIABILITY
                                if(!$book->increaseStock($row['quantity'])) {
                                    $items_processed = false;
                                    error_log("Failed to restore stock for book ID: " . $row['book_id']);
                                    break;
                                }
                                
                                // Get updated stock
                                $book->getById();
                                error_log("Book #{$row['book_id']} new stock after increase: {$book->stock_qty}");
                            } else {
                                $items_processed = false;
                                error_log("Book not found with ID: " . $row['book_id']);
                                break;
                            }
                        }
                    }
                    
                    if($items_processed) {
                        // All items processed, commit transaction
                        $db->commit();
                        
                        // Set response code - 200 success
                        http_response_code(200);
                        
                        // Return success message
                        echo json_encode(array(
                            "message" => "Transaction status updated successfully.",
                            "transaction_id" => $transaction->transaction_id,
                            "status" => $transaction->status
                        ));
                    } else {
                        // Problem processing items, rollback
                        $db->rollBack();
                        
                        // Set response code - 503 service unavailable
                        http_response_code(503);
                        
                        // Return error message
                        echo json_encode(array("message" => "Error updating inventory."));
                    }
                } else {
                    // Rollback transaction
                    $db->rollBack();
                    
                    // Set response code - 503 service unavailable
                    http_response_code(503);
                    
                    // Return error message
                    echo json_encode(array("message" => "Unable to update transaction status."));
                }
            } else {
                // Status not changing, just return success
                $db->commit();
                
                // Set response code - 200 success
                http_response_code(200);
                
                // Return success message
                echo json_encode(array(
                    "message" => "No change in transaction status.",
                    "transaction_id" => $transaction->transaction_id,
                    "status" => $transaction->status
                ));
            }
        } else {
            // Transaction not found
            $db->rollBack();
            
            // Set response code - 404 not found
            http_response_code(404);
            
            // Return error message
            echo json_encode(array("message" => "Transaction not found."));
        }
    } catch (Exception $e) {
        // Rollback transaction
        $db->rollBack();
        
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Return error message
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Return error message
    echo json_encode(array("message" => "Unable to update transaction status. Data is incomplete."));
}
?> 