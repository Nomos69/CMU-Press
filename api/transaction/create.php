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
                    $book->book_id = $item->book_id;
                    $book->updateStock($item->quantity);
                }
            }
            
            // Check if all transaction items were created
            if($transaction_items_created) {
                // Commit transaction
                $db->commit();
                
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