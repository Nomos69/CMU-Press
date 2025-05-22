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
    !empty($data->items) && 
    is_array($data->items) &&
    !empty($data->transaction_id)
) {
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["message" => "Database connection failed."]);
        exit;
    }
    $db->beginTransaction();
    try {
        $results = array();
        $success = true;
        foreach($data->items as $item) {
            if(empty($item->book_id) || empty($item->quantity)) {
                continue;
            }
            $book_id = htmlspecialchars(strip_tags($item->book_id));
            $quantity = intval($item->quantity);
            // Get current stock
            $stmt = $db->prepare("SELECT stock_qty, title FROM books WHERE book_id = :book_id");
            $stmt->bindParam(":book_id", $book_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row) {
                $current_stock = $row['stock_qty'];
                $title = $row['title'];
                $result = array(
                    "book_id" => $book_id,
                    "title" => $title,
                    "previous_stock" => $current_stock
                );
                if($current_stock < $quantity) {
                    // Not enough stock, set to 0
                    $stmt2 = $db->prepare("UPDATE books SET stock_qty = 0 WHERE book_id = :book_id");
                    $stmt2->bindParam(":book_id", $book_id);
                    $isUpdated = $stmt2->execute();
                    $new_stock = 0;
                    $result["message"] = "Insufficient stock. Stock set to 0.";
                    $result["warning"] = true;
                } else {
                    // Decrease stock
                    $stmt2 = $db->prepare("UPDATE books SET stock_qty = stock_qty - :quantity WHERE book_id = :book_id");
                    $stmt2->bindParam(":quantity", $quantity);
                    $stmt2->bindParam(":book_id", $book_id);
                    $isUpdated = $stmt2->execute();
                    $new_stock = $current_stock - $quantity;
                    $result["message"] = "Stock updated successfully";
                    $result["warning"] = false;
                }
                if($isUpdated) {
                    // Get updated stock for reporting
                    $stmt3 = $db->prepare("SELECT stock_qty FROM books WHERE book_id = :book_id");
                    $stmt3->bindParam(":book_id", $book_id);
                    $stmt3->execute();
                    $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
                    $result["new_stock"] = $row3 ? $row3['stock_qty'] : $new_stock;
                } else {
                    $success = false;
                    $result["error"] = "Failed to update stock";
                }
                $results[] = $result;
            } else {
                $results[] = array(
                    "book_id" => $book_id,
                    "error" => "Book not found",
                    "success" => false
                );
                $success = false;
            }
        }
        if($success) {
            $db->commit();
            http_response_code(200);
            echo json_encode(array(
                "message" => "Inventory updated successfully after sale",
                "transaction_id" => $data->transaction_id,
                "results" => $results,
                "success" => true
            ));
        } else {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(array(
                "message" => "Failed to update inventory after sale",
                "transaction_id" => $data->transaction_id,
                "results" => $results,
                "success" => false
            ));
        }
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array(
            "message" => "Error: " . $e->getMessage(),
            "success" => false
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Invalid request data. Required: items (array) and transaction_id",
        "success" => false
    ));
}
?> 