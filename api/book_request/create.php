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

if(!empty($data->title) && !empty($data->requested_by)) {
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["message" => "Database connection failed."]);
        exit;
    }
    $title = htmlspecialchars(strip_tags($data->title));
    $author = !empty($data->author) ? htmlspecialchars(strip_tags($data->author)) : "";
    $requested_by = htmlspecialchars(strip_tags($data->requested_by));
    $priority = !empty($data->priority) ? htmlspecialchars(strip_tags($data->priority)) : "medium";
    $quantity = !empty($data->quantity) && $data->quantity > 0 ? intval($data->quantity) : 1;
    $status = "pending";
    $sql = "INSERT INTO book_requests (title, author, requested_by, priority, quantity, status) VALUES (:title, :author, :requested_by, :priority, :quantity, :status)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":author", $author);
    $stmt->bindParam(":requested_by", $requested_by);
    $stmt->bindParam(":priority", $priority);
    $stmt->bindParam(":quantity", $quantity);
    $stmt->bindParam(":status", $status);
    if($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "message" => "Book request was created successfully.",
            "request_id" => $db->lastInsertId()
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to create book request."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to create book request. Data is incomplete."]);
}
?>