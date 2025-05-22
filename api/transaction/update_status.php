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

if(!empty($data->transaction_id) && !empty($data->status)) {
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
        $transaction_id = htmlspecialchars(strip_tags($data->transaction_id));
        $newStatus = htmlspecialchars(strip_tags($data->status));
        // Get current transaction data
        $stmt = $db->prepare("SELECT status FROM transactions WHERE transaction_id = :transaction_id");
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $oldStatus = $row['status'];
            if($oldStatus !== $newStatus) {
                // Update transaction status
                $stmt2 = $db->prepare("UPDATE transactions SET status = :status WHERE transaction_id = :transaction_id");
                $stmt2->bindParam(":status", $newStatus);
                $stmt2->bindParam(":transaction_id", $transaction_id);
                if($stmt2->execute()) {
                    // Get all items for this transaction
                    $stmt3 = $db->prepare("SELECT book_id, quantity FROM transaction_items WHERE transaction_id = :transaction_id");
                    $stmt3->bindParam(":transaction_id", $transaction_id);
                    $stmt3->execute();
                    $items_processed = true;
                    if($oldStatus !== "completed" && $newStatus === "completed") {
                        // Decrease inventory
                        while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                            $stmt4 = $db->prepare("UPDATE books SET stock_qty = stock_qty - :quantity WHERE book_id = :book_id");
                            $stmt4->bindParam(":quantity", $row3['quantity']);
                            $stmt4->bindParam(":book_id", $row3['book_id']);
                            if(!$stmt4->execute()) {
                                $items_processed = false;
                                break;
                            }
                        }
                    } else if($oldStatus === "completed" && $newStatus !== "completed") {
                        // Return inventory
                        while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                            $stmt4 = $db->prepare("UPDATE books SET stock_qty = stock_qty + :quantity WHERE book_id = :book_id");
                            $stmt4->bindParam(":quantity", $row3['quantity']);
                            $stmt4->bindParam(":book_id", $row3['book_id']);
                            if(!$stmt4->execute()) {
                                $items_processed = false;
                                break;
                            }
                        }
                    }
                    if($items_processed) {
                        $db->commit();
                        http_response_code(200);
                        echo json_encode([
                            "message" => "Transaction status updated successfully.",
                            "transaction_id" => $transaction_id,
                            "status" => $newStatus
                        ]);
                    } else {
                        $db->rollBack();
                        http_response_code(503);
                        echo json_encode(["message" => "Error updating inventory."]);
                    }
                } else {
                    $db->rollBack();
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to update transaction status."]);
                }
            } else {
                $db->commit();
                http_response_code(200);
                echo json_encode([
                    "message" => "No change in transaction status.",
                    "transaction_id" => $transaction_id,
                    "status" => $oldStatus
                ]);
            }
        } else {
            $db->rollBack();
            http_response_code(404);
            echo json_encode(["message" => "Transaction not found."]);
        }
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(503);
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to update transaction status. Data is incomplete."]);
}
?> 