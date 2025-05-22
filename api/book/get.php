<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection only
include_once '../../config/database.php';

// Get book id
$book_id = isset($_GET['id']) ? $_GET['id'] : null;

// Check if book id is provided
if($book_id) {
    // Create database connection (procedural)
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["message" => "Database connection failed."]);
        exit;
    }
    $book_id = htmlspecialchars(strip_tags($book_id));
    $stmt = $db->prepare("SELECT * FROM books WHERE book_id = :book_id LIMIT 1");
    $stmt->bindParam(":book_id", $book_id);
    $stmt->execute();
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(200); // OK
        echo json_encode($row);
    } else {
        // Set error response
        http_response_code(404); // Not found
        echo json_encode(array("message" => "Book not found."));
    }
} else {
    // Set error response for missing book id
    http_response_code(400); // Bad request
    echo json_encode(array("message" => "Book ID is required."));
}
?>
