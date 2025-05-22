<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection only
include_once '../../config/database.php';

// Create database connection (procedural)
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

// Query books
$stmt = $db->prepare("SELECT * FROM books ORDER BY title ASC");
$stmt->execute();
$num = $stmt->rowCount();

// Check if any books found
if($num > 0) {
    // Books array
    $books_arr = array();
    $books_arr["records"] = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($books_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($books_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No books found."));
}
?>
