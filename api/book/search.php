<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection only
include_once '../../config/database.php';

// Create database connection (procedural)
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

// Get keywords from request
$keywords = isset($_GET["q"]) ? $_GET["q"] : "";
$keywords = htmlspecialchars(strip_tags($keywords));
$likeKeywords = "%{$keywords}%";

// Search for books
$sql = "SELECT * FROM books WHERE title LIKE :keywords OR author LIKE :keywords OR isbn LIKE :keywords ORDER BY title ASC";
$stmt = $db->prepare($sql);
$stmt->bindParam(":keywords", $likeKeywords);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0) {
    $books_arr = array();
    $books_arr["books"] = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row["in_stock"] = $row["stock_qty"] > 0;
        $row["low_stock"] = $row["stock_qty"] <= $row["low_stock_threshold"] && $row["stock_qty"] > 0;
        array_push($books_arr["books"], $row);
    }
    http_response_code(200);
    echo json_encode($books_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No books found matching your search."));
}
?>