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

if(!empty($data->request_id) && !empty($data->title) && !empty($data->requested_by) && !empty($data->status)) {
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["message" => "Database connection failed."]);
        exit;
    }
    $request_id = htmlspecialchars(strip_tags($data->request_id));
    $title = htmlspecialchars(strip_tags($data->title));
    $author = !empty($data->author) ? htmlspecialchars(strip_tags($data->author)) : "";
    $requested_by = htmlspecialchars(strip_tags($data->requested_by));
    $priority = !empty($data->priority) ? htmlspecialchars(strip_tags($data->priority)) : "medium";
    $quantity = !empty($data->quantity) && $data->quantity > 0 ? intval($data->quantity) : 1;
    $status = htmlspecialchars(strip_tags($data->status));
    // Get original status
    $stmt = $db->prepare("SELECT status FROM book_requests WHERE request_id = :request_id");
    $stmt->bindParam(":request_id", $request_id);
    $stmt->execute();
    $original_status = null;
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $original_status = $row['status'];
    }
    // Update the book request
    $sql = "UPDATE book_requests SET title = :title, author = :author, requested_by = :requested_by, priority = :priority, quantity = :quantity, status = :status WHERE request_id = :request_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":author", $author);
    $stmt->bindParam(":requested_by", $requested_by);
    $stmt->bindParam(":priority", $priority);
    $stmt->bindParam(":quantity", $quantity);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":request_id", $request_id);
    if($stmt->execute()) {
        $response = ["message" => "Book request updated successfully."];
        // If the status is being changed to 'fulfilled', add the book to inventory
        if($status === 'fulfilled' && $original_status !== 'fulfilled') {
            $sql = "INSERT INTO books (title, author, isbn, price, stock_qty, low_stock_threshold, college) VALUES (:title, :author, NULL, 0.00, :quantity, 5, NULL)";
            $stmt2 = $db->prepare($sql);
            $stmt2->bindParam(":title", $title);
            $stmt2->bindParam(":author", $author);
            $stmt2->bindParam(":quantity", $quantity);
            if($stmt2->execute()) {
                $response["book_added"] = true;
                $response["book_id"] = $db->lastInsertId();
                $response["message"] .= " Book has been added to inventory.";
            } else {
                $response["book_added"] = false;
                $response["message"] .= " Note: Failed to add book to inventory.";
            }
        }
        http_response_code(200);
        echo json_encode($response);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to update book request."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to update book request. Data is incomplete."]);
}
?> 