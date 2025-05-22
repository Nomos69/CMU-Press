<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection only
include_once '../../config/database.php';

$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

// Fetch recent transactions (limit 20, most recent first)
$sql = "SELECT t.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone
        FROM transactions t
        LEFT JOIN customers c ON t.customer_id = c.customer_id
        ORDER BY t.created_at DESC
        LIMIT 20";
$stmt = $db->prepare($sql);
$stmt->execute();
$transactions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $transaction_id = $row['transaction_id'];
    // Fetch items for this transaction
    $stmt_items = $db->prepare("SELECT ti.*, b.title, b.author FROM transaction_items ti LEFT JOIN books b ON ti.book_id = b.book_id WHERE ti.transaction_id = :transaction_id");
    $stmt_items->bindParam(":transaction_id", $transaction_id);
    $stmt_items->execute();
    $items = [];
    while ($item = $stmt_items->fetch(PDO::FETCH_ASSOC)) {
        $items[] = $item;
    }
    $row['items'] = $items;
    $transactions[] = $row;
}
if (count($transactions) > 0) {
    http_response_code(200);
    echo json_encode(["records" => $transactions]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No recent transactions found."]);
}
?>