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

if(
    !empty($data->customer_id) &&
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->phone)
){
    $db = null;
    try {
        $db = (new Database())->getConnection();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode(["success" => false, "message" => "Database connection failed."]);
        exit;
    }
    $customer_id = htmlspecialchars(strip_tags($data->customer_id));
    $name = htmlspecialchars(strip_tags($data->name));
    $email = htmlspecialchars(strip_tags($data->email));
    $phone = htmlspecialchars(strip_tags($data->phone));
    $stmt = $db->prepare("UPDATE customers SET name = :name, email = :email, phone = :phone WHERE customer_id = :customer_id");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":customer_id", $customer_id);
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Customer was updated successfully."]);
    } else {
        http_response_code(503);
        echo json_encode(["success" => false, "message" => "Unable to update customer."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Unable to update customer. Data is incomplete."]);
}
?>
