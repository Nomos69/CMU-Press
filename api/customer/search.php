<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection only
include_once '../../config/database.php';

// Get search query
$q = isset($_GET['q']) ? $_GET['q'] : '';
$q = htmlspecialchars(strip_tags($q));
$likeQ = "%{$q}%";

// Create database connection
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

$sql = "SELECT * FROM customers WHERE name LIKE :q OR email LIKE :q OR phone LIKE :q ORDER BY name ASC";
$stmt = $db->prepare($sql);
$stmt->bindParam(':q', $likeQ);
$stmt->execute();
$results = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $results[] = $row;
}
if (count($results) > 0) {
    http_response_code(200);
    echo json_encode(["records" => $results]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No customers found."]);
}
?>
