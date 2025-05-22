<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection only
include_once '../../config/database.php';

// Get book ID from URL
$book_id = isset($_GET['book_id']) ? $_GET['book_id'] : die(json_encode(["message" => "Missing book ID parameter"]));

// Create database connection
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

$book_id = htmlspecialchars(strip_tags($book_id));
$stmt = $db->prepare("SELECT * FROM books WHERE book_id = :book_id");
$stmt->bindParam(":book_id", $book_id);
$stmt->execute();
if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(200);
    echo json_encode([
        "book_id" => $row['book_id'],
        "title" => $row['title'],
        "author" => $row['author'],
        "current_stock" => $row['stock_qty'],
        "low_stock_threshold" => $row['low_stock_threshold']
    ]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Book not found"]);
}
?> 