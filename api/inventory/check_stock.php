<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection only
include_once '../../config/database.php';

// Create database connection
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

if(isset($_GET['book_id'])) {
    $book_id = htmlspecialchars(strip_tags($_GET['book_id']));
    $stmt = $db->prepare("SELECT * FROM books WHERE book_id = :book_id");
    $stmt->bindParam(":book_id", $book_id);
    $stmt->execute();
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(200);
        echo json_encode([
            "book_id" => $row['book_id'],
            "title" => $row['title'],
            "stock_qty" => $row['stock_qty'],
            "low_stock_threshold" => $row['low_stock_threshold'],
            "status" => $row['stock_qty'] <= 0 ? "out_of_stock" : ($row['stock_qty'] <= $row['low_stock_threshold'] ? "low_stock" : "in_stock")
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Book not found."]);
    }
} else {
    // List all books with low stock or out of stock
    $stmt = $db->prepare("SELECT * FROM books WHERE stock_qty <= low_stock_threshold ORDER BY stock_qty ASC");
    $stmt->execute();
    $low_stock_count = $stmt->rowCount();
    if($low_stock_count > 0) {
        $books_arr = ["records" => [], "count" => $low_stock_count];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $book_item = [
                "book_id" => $row['book_id'],
                "title" => $row['title'],
                "author" => $row['author'],
                "stock_qty" => $row['stock_qty'],
                "low_stock_threshold" => $row['low_stock_threshold'],
                "status" => $row['stock_qty'] <= 0 ? "out_of_stock" : "low_stock"
            ];
            $books_arr["records"][] = $book_item;
        }
        http_response_code(200);
        echo json_encode($books_arr);
    } else {
        http_response_code(200);
        echo json_encode([
            "message" => "No books with low stock found.",
            "count" => 0,
            "records" => []
        ]);
    }
}
?> 