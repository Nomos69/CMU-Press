<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection only
include_once '../../config/database.php';

$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

$stmt = $db->prepare("SELECT * FROM book_requests WHERE status = 'pending' ORDER BY request_date DESC");
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0) {
    $requests_arr = array();
    $requests_arr["records"] = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($requests_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($requests_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No pending book requests found."]);
}
?>
