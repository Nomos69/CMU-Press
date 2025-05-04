<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and book model
include_once '../../config/database.php';
include_once '../../models/Book.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Create book object
$book = new Book($db);

// Query books
$stmt = $book->getAll();
$num = $stmt->rowCount();

// Check if any books found
if($num > 0) {
    // Books array
    $books_arr = array();
    $books_arr["records"] = array();
    
    // Retrieve table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $book_item = array(
            "book_id" => $book_id,
            "title" => $title,
            "author" => $author,
            "isbn" => $isbn,
            "price" => $price,
            "stock_qty" => $stock_qty,
            "low_stock_threshold" => $low_stock_threshold,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        
        array_push($books_arr["records"], $book_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show books data in json format
    echo json_encode($books_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user no products found
    echo json_encode(
        array("message" => "No books found.")
    );
}
?>
