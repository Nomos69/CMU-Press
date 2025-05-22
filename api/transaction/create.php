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
include_once '../../includes/logger.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->items) && 
    !empty($data->payment_method) && 
    !empty($data->subtotal) && 
    !empty($data->tax) && 
    isset($data->discount) && 
    !empty($data->total) && 
    !empty($data->user_id)
) {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize objects
    $transaction = new Transaction($db);
    $transaction_item = new TransactionItem($db);
    $book = new Book($db);
    
    // Set transaction properties
    $transaction->customer_id = !empty($data->customer_id) ? $data->customer_id : null;
    $transaction->user_id = $data->user_id;
    $transaction->status = !empty($data->status) ? $data->status : "completed";
    $transaction->payment_method = $data->payment_method;
    $transaction->subtotal = $data->subtotal;
    $transaction->tax = $data->tax;
    $transaction->discount = $data->discount;
    $transaction->total = $data->total;
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Create the transaction
        if($transaction->create()) {
            // Log transaction creation
            Logger::logTransaction(
                $transaction->transaction_id,
                'created',
                [
                    'status' => $transaction->status,
                    'total' => $transaction->total,
                    'items_count' => count($data->items)
                ]
            );
            
            // Transaction created, now add items
            $transaction_items_created = true;
            
            // Loop through each item
            foreach($data->items as $item) {
                // Set transaction item properties
                $transaction_item->transaction_id = $transaction->transaction_id;
                $transaction_item->book_id = $item->book_id;
                $transaction_item->quantity = $item->quantity;
                $transaction_item->price_per_unit = $item->price;
                $transaction_item->total_price = $item->price * $item->quantity;
                
                // Create transaction item
                if(!$transaction_item->create()) {
                    $transaction_items_created = false;
                    break;
                }
                
                // Update book stock if transaction is completed
                if($transaction->status === "completed") {
                    // Create a new Book object for each item to avoid reusing the same object
                    $book = new Book($db);
                    $book->book_id = $item->book_id;
                    
                    // Get the book data before updating
                    if($book->getById()) {
                        // Log this for debugging
                        error_log("Transaction #{$transaction->transaction_id}: Processing stock update for book #{$item->book_id}, quantity: {$item->quantity}, current stock: {$book->stock_qty}");
                        
                        // Direct stock update - This is critical!
                        if(!$book->updateStock($item->quantity)) {
                            // Rollback transaction and return error
                            $db->rollBack();
                            Logger::logTransaction(
                                $transaction->transaction_id,
                                'failed',
                                ['reason' => "Quantity is Greater than the Stock Quantity for book #{$item->book_id}"]
                            );
                            http_response_code(400);
                            echo json_encode(array("message" => "Quantity is Greater than the Stock Quantity"));
                            exit;
                        } else {
                            // Log successful update
                            error_log("Transaction #{$transaction->transaction_id}: Successfully updated stock for book #{$item->book_id}");
                        }
                    } else {
                        error_log("Transaction #{$transaction->transaction_id}: Book #{$item->book_id} not found when updating stock");
                    }
                }
            }
            
            // Check if all transaction items were created
            if($transaction_items_created) {
                // Commit transaction
                $db->commit();
                
                // Log successful transaction completion
                Logger::logTransaction(
                    $transaction->transaction_id,
                    'completed',
                    [
                        'status' => $transaction->status,
                        'total' => $transaction->total
                    ]
                );
                
                // Set response code - 201 created
                http_response_code(201);
                
                // Return success message
                echo json_encode(array(
                    "message" => "Transaction was created successfully.",
                    "transaction_id" => $transaction->transaction_id
                ));
            } else {
                // Rollback transaction
                $db->rollBack();
                
                // Log transaction failure
                Logger::logTransaction(
                    $transaction->transaction_id,
                    'failed',
                    ['reason' => 'Unable to create transaction items']
                );
                
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Return error message
                echo json_encode(array("message" => "Unable to create transaction items."));
            }
        } else {
            // Rollback transaction
            $db->rollBack();
            
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Return error message
            echo json_encode(array("message" => "Unable to create transaction."));
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
    echo json_encode(array("message" => "Unable to create transaction. Data is incomplete."));
}
?>