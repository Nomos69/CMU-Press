<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection only
include_once '../../config/database.php';

// Initialize response array
$response = array();

// Get book id - check both GET and POST methods
$book_id = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $book_id = isset($_GET['id']) ? $_GET['id'] : null;
} else {
    // For POST method
    $book_id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
}

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
    // Check if book exists
    $stmt = $db->prepare("SELECT book_id FROM books WHERE book_id = :book_id");
    $stmt->bindParam(":book_id", $book_id);
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        $response = array("message" => "Book not found.");
        http_response_code(404); // Not found
    } else {
        // Delete the book from database
        $stmt = $db->prepare("DELETE FROM books WHERE book_id = :book_id");
        $stmt->bindParam(":book_id", $book_id);
        if($stmt->execute()) {
            $response = array("message" => "Book deleted successfully.");
            http_response_code(200); // OK
        } else {
            $response = array("message" => "Unable to delete book.");
            http_response_code(503); // Service unavailable
        }
    }
} else {
    // Set error response for missing book id
    $response = array("message" => "Unable to delete book. Book ID is required.");
    http_response_code(400); // Bad request
}

// Return response as JSON
echo json_encode($response);
?> 