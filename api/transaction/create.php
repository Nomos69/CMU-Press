<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection only
include_once '../../config/database.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Create database connection
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

// Check if data is complete
if(
    !empty($data->items) &&
    !empty($data->total_amount) &&
    !empty($data->user_id)
){
    // First, validate all items have sufficient stock
    $insufficient_items = array();
    foreach($data->items as $item){
        if(empty($item->book_id) || empty($item->quantity) || empty($item->price)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create transaction. Item data is incomplete."]);
            exit;
        }
        $book_id = htmlspecialchars(strip_tags($item->book_id));
        $stmt = $db->prepare("SELECT stock_qty, title FROM books WHERE book_id = :book_id");
        $stmt->bindParam(":book_id", $book_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row || $row['stock_qty'] < $item->quantity){
            $insufficient_items[] = array(
                "book_id" => $item->book_id,
                "title" => $row ? $row['title'] : '',
                "requested" => $item->quantity,
                "available" => $row ? $row['stock_qty'] : 0
            );
        }
    }
    if(!empty($insufficient_items)){
        http_response_code(400);
        echo json_encode([
            "message" => "Transaction failed due to insufficient stock for some items.",
            "insufficient_items" => $insufficient_items
        ]);
        exit;
    }
    try {
        $db->beginTransaction();
        // Insert transaction
        $sql = "INSERT INTO transactions (user_id, customer_id, total_amount, payment_method, status) VALUES (:user_id, :customer_id, :total_amount, :payment_method, 'completed')";
        $stmt = $db->prepare($sql);
        $user_id = htmlspecialchars(strip_tags($data->user_id));
        $customer_id = isset($data->customer_id) ? htmlspecialchars(strip_tags($data->customer_id)) : null;
        $total_amount = floatval($data->total_amount);
        $payment_method = isset($data->payment_method) ? htmlspecialchars(strip_tags($data->payment_method)) : 'cash';
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->bindParam(":total_amount", $total_amount);
        $stmt->bindParam(":payment_method", $payment_method);
        if($stmt->execute()){
            $transaction_id = $db->lastInsertId();
            $items_added = 0;
            foreach($data->items as $item){
                $book_id = htmlspecialchars(strip_tags($item->book_id));
                $quantity = intval($item->quantity);
                $price = floatval($item->price);
                // Insert transaction item
                $sql = "INSERT INTO transaction_items (transaction_id, book_id, quantity, price) VALUES (:transaction_id, :book_id, :quantity, :price)";
                $stmt2 = $db->prepare($sql);
                $stmt2->bindParam(":transaction_id", $transaction_id);
                $stmt2->bindParam(":book_id", $book_id);
                $stmt2->bindParam(":quantity", $quantity);
                $stmt2->bindParam(":price", $price);
                if($stmt2->execute()){
                    $items_added++;
                    // Update book stock
                    $stmt3 = $db->prepare("UPDATE books SET stock_qty = stock_qty - :quantity WHERE book_id = :book_id");
                    $stmt3->bindParam(":quantity", $quantity);
                    $stmt3->bindParam(":book_id", $book_id);
                    if(!$stmt3->execute()) {
                        throw new Exception("Failed to update stock for book ID: " . $book_id);
                    }
                } else {
                    throw new Exception("Failed to create transaction item for book ID: " . $book_id);
                }
            }
            if($items_added == count($data->items)){
                $db->commit();
                http_response_code(201);
                echo json_encode([
                    "message" => "Transaction was created successfully.",
                    "transaction_id" => $transaction_id
                ]);
            } else {
                throw new Exception("Not all items were added to the transaction.");
            }
        } else {
            throw new Exception("Unable to create transaction.");
        }
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(503);
        echo json_encode(["message" => "Transaction failed: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to create transaction. Data is incomplete."]);
}
?>