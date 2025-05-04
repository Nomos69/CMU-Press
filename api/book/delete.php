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

// Include database and book model
include_once '../../config/database.php';
include_once '../../models/Book.php';

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
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Create book object
    $book = new Book($db);
    $book->book_id = $book_id;
    
    // Check if book exists
    if(!$book->getById()) {
        $response = array(
            "message" => "Book not found."
        );
        http_response_code(404); // Not found
    }
    // Delete the book from database
    else if($book->delete()) {
        // Set response
        $response = array(
            "message" => "Book deleted successfully."
        );
        http_response_code(200); // OK
    } else {
        // Set error response
        $response = array(
            "message" => "Unable to delete book."
        );
        http_response_code(503); // Service unavailable
    }
} else {
    // Set error response for missing book id
    $response = array(
        "message" => "Unable to delete book. Book ID is required."
    );
    http_response_code(400); // Bad request
}

// Return response as JSON
echo json_encode($response);
?> 