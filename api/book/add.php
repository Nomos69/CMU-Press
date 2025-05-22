<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection only
include_once '../../config/database.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Initialize response array
$response = array();

// Check if required data is present
if (
    !empty($data->title) &&
    !empty($data->author) &&
    !empty($data->price) &&
    isset($data->stock_qty)
) {
    // Create database connection (procedural)
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["message" => "Database connection failed."]);
        exit;
    }

    // Sanitize input
    $title = htmlspecialchars(strip_tags($data->title));
    $author = htmlspecialchars(strip_tags($data->author));
    $isbn = isset($data->isbn) ? htmlspecialchars(strip_tags($data->isbn)) : null;
    $price = htmlspecialchars(strip_tags($data->price));
    $stock_qty = htmlspecialchars(strip_tags($data->stock_qty));
    $low_stock_threshold = isset($data->low_stock_threshold) ? htmlspecialchars(strip_tags($data->low_stock_threshold)) : 5;
    $college = isset($data->college) ? htmlspecialchars(strip_tags($data->college)) : null;

    // Prepare SQL
    $sql = "INSERT INTO books (title, author, isbn, price, stock_qty, low_stock_threshold, college) VALUES (:title, :author, :isbn, :price, :stock_qty, :low_stock_threshold, :college)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":author", $author);
    $stmt->bindParam(":isbn", $isbn);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":stock_qty", $stock_qty);
    $stmt->bindParam(":low_stock_threshold", $low_stock_threshold);
    $stmt->bindParam(":college", $college);

    if ($stmt->execute()) {
        $response = array(
            "message" => "Book added successfully.",
            "book_id" => $db->lastInsertId()
        );
        http_response_code(201); // Created
    } else {
        $response = array(
            "message" => "Unable to add book."
        );
        http_response_code(503); // Service unavailable
    }
} else {
    // Set error response for incomplete data
    $response = array(
        "message" => "Unable to add book. Data is incomplete."
    );
    http_response_code(400); // Bad request
}

// Return response as JSON
echo json_encode($response);
?>
