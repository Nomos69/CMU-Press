<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include necessary files
include_once '../../config/database.php';
include_once '../../models/BookRequest.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize book request object
$book_request = new BookRequest($db);

// Query book requests
$stmt = $book_request->getAll();
$num = $stmt->rowCount();

// Check if any book requests found
if($num > 0) {
    // Book requests array
    $requests_arr = array();
    $requests_arr["records"] = array();
    
    // Retrieve table contents
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $request_item = array(
            "request_id" => $request_id,
            "title" => $title,
            "author" => $author,
            "requested_by" => $requested_by,
            "request_date" => $request_date,
            "priority" => $priority,
            "quantity" => $quantity,
            "status" => $status
        );
        
        array_push($requests_arr["records"], $request_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show book requests data in json format
    echo json_encode($requests_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user no book requests found
    echo json_encode(array("message" => "No book requests found."));
}
?>
