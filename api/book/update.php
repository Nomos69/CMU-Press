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
if (!empty($data->book_id)) {
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
    $book_id = htmlspecialchars(strip_tags($data->book_id));
    $fields = [];
    $params = [":book_id" => $book_id];
    if (isset($data->title)) { $fields[] = "title = :title"; $params[":title"] = htmlspecialchars(strip_tags($data->title)); }
    if (isset($data->author)) { $fields[] = "author = :author"; $params[":author"] = htmlspecialchars(strip_tags($data->author)); }
    if (isset($data->isbn)) { $fields[] = "isbn = :isbn"; $params[":isbn"] = htmlspecialchars(strip_tags($data->isbn)); }
    if (isset($data->price)) { $fields[] = "price = :price"; $params[":price"] = htmlspecialchars(strip_tags($data->price)); }
    if (isset($data->stock_qty)) { $fields[] = "stock_qty = :stock_qty"; $params[":stock_qty"] = htmlspecialchars(strip_tags($data->stock_qty)); }
    if (isset($data->low_stock_threshold)) { $fields[] = "low_stock_threshold = :low_stock_threshold"; $params[":low_stock_threshold"] = htmlspecialchars(strip_tags($data->low_stock_threshold)); }
    if (isset($data->college)) { $fields[] = "college = :college"; $params[":college"] = htmlspecialchars(strip_tags($data->college)); }

    if (count($fields) > 0) {
        $sql = "UPDATE books SET ".implode(", ", $fields)." WHERE book_id = :book_id";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if ($stmt->execute()) {
            $response = array("message" => "Book updated successfully.");
            http_response_code(200); // OK
        } else {
            $response = array("message" => "Unable to update book.");
            http_response_code(503); // Service unavailable
        }
    } else {
        $response = array("message" => "No fields to update.");
        http_response_code(400);
    }
} else {
    // Set error response for incomplete data
    $response = array("message" => "Unable to update book. Data is incomplete.");
    http_response_code(400); // Bad request
}

// Return response as JSON
echo json_encode($response);
?>
