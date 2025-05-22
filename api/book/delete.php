<?php
// Include API utilities
include_once dirname(__FILE__) . '/../../includes/api_utilities.php';

// Set headers and handle CORS
set_api_headers();

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
    $db = get_db_connection();

    $book_id = sanitize_input($book_id);

    // Check if book exists
    $stmt = $db->prepare("SELECT book_id FROM books WHERE book_id = :book_id");
    $stmt->bindParam(":book_id", $book_id);
    $stmt->execute();

    if($stmt->rowCount() == 0) {
        send_json_response(404, ["message" => "Book not found."]); // Not found
    } else {
        // Delete the book from database
        $stmt = $db->prepare("DELETE FROM books WHERE book_id = :book_id");
        $stmt->bindParam(":book_id", $book_id);

        if($stmt->execute()) {
            send_json_response(200, ["message" => "Book deleted successfully."]); // OK
        } else {
            send_json_response(503, ["message" => "Unable to delete book."]); // Service unavailable
        }
    }
} else {
    // Set error response for missing book id
    send_json_response(400, ["message" => "Unable to delete book. Book ID is required."]); // Bad request
}

// No need for manual echo and exit here as send_json_response handles it
?> 