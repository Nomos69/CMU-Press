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

if(
    !empty($data->book_id) && 
    isset($data->quantity) && 
    !empty($data->operation) && 
    in_array($data->operation, ['set', 'increase', 'decrease'])
) {
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["message" => "Database connection failed."]);
        exit;
    }
    $book_id = htmlspecialchars(strip_tags($data->book_id));
    $quantity = intval($data->quantity);
    $operation = $data->operation;
    $db->beginTransaction();
    try {
        // Get current stock
        $stmt = $db->prepare("SELECT stock_qty, title FROM books WHERE book_id = :book_id");
        $stmt->bindParam(":book_id", $book_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row) {
            http_response_code(404);
            echo json_encode(["message" => "Book not found."]);
            exit;
        }
        $current_stock = $row['stock_qty'];
        $title = $row['title'];
        $success = false;
        $message = "";
        $new_stock = 0;
        switch($operation) {
            case 'set':
                $new_stock = max(0, $quantity);
                $stmt2 = $db->prepare("UPDATE books SET stock_qty = :new_stock WHERE book_id = :book_id");
                $stmt2->bindParam(":new_stock", $new_stock);
                $stmt2->bindParam(":book_id", $book_id);
                $success = $stmt2->execute();
                $operation_label = "set";
                break;
            case 'decrease':
                if($current_stock < $quantity) {
                    $message = "Warning: Insufficient stock. Available: {$current_stock}, Requested: {$quantity}. Stock set to 0.";
                    $new_stock = 0;
                    $stmt2 = $db->prepare("UPDATE books SET stock_qty = 0 WHERE book_id = :book_id");
                    $stmt2->bindParam(":book_id", $book_id);
                    $success = $stmt2->execute();
                } else {
                    $new_stock = $current_stock - $quantity;
                    $stmt2 = $db->prepare("UPDATE books SET stock_qty = stock_qty - :quantity WHERE book_id = :book_id");
                    $stmt2->bindParam(":quantity", $quantity);
                    $stmt2->bindParam(":book_id", $book_id);
                    $success = $stmt2->execute();
                }
                $operation_label = "decreased";
                break;
            case 'increase':
                $stmt2 = $db->prepare("UPDATE books SET stock_qty = stock_qty + :quantity WHERE book_id = :book_id");
                $stmt2->bindParam(":quantity", $quantity);
                $stmt2->bindParam(":book_id", $book_id);
                $success = $stmt2->execute();
                $operation_label = "increased";
                break;
        }
        // Get updated stock
        $stmt3 = $db->prepare("SELECT stock_qty FROM books WHERE book_id = :book_id");
        $stmt3->bindParam(":book_id", $book_id);
        $stmt3->execute();
        $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
        $new_stock = $row3 ? $row3['stock_qty'] : $new_stock;
        if($success) {
            $db->commit();
            http_response_code(200);
            echo json_encode([
                "message" => $message ?: "Stock {$operation_label} successfully.",
                "book_id" => $book_id,
                "title" => $title,
                "previous_stock" => $current_stock,
                "quantity_changed" => $quantity,
                "new_stock" => $new_stock,
                "success" => true
            ]);
        } else {
            $db->rollBack();
            http_response_code(503);
            echo json_encode([
                "message" => $message ?: "Unable to update stock.",
                "book_id" => $book_id,
                "title" => $title,
                "current_stock" => $current_stock,
                "success" => false
            ]);
        }
    } catch(Exception $e) {
        $db->rollBack();
        http_response_code(503);
        echo json_encode([
            "message" => "Error: " . $e->getMessage(),
            "book_id" => $book_id,
            "title" => isset($title) ? $title : null,
            "current_stock" => isset($current_stock) ? $current_stock : null
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        "message" => "Missing required data. Required: book_id, quantity, operation (set/increase/decrease)."
    ]);
}
?> 