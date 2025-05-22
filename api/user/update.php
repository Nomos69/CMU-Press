<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection only
include_once '../../config/database.php';

// Get database connection
$db = null;
try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->user_id) &&
    !empty($data->name) &&
    !empty($data->username) && 
    !empty($data->role)
){
    $user_id = htmlspecialchars(strip_tags($data->user_id));
    $name = htmlspecialchars(strip_tags($data->name));
    $username = htmlspecialchars(strip_tags($data->username));
    $role = htmlspecialchars(strip_tags($data->role));
    $stmt = $db->prepare("UPDATE users SET name = :name, username = :username, role = :role WHERE user_id = :user_id");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":user_id", $user_id);
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "User was updated successfully."]);
    } else {
        http_response_code(503);
        echo json_encode(["success" => false, "message" => "Unable to update user."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Unable to update user. Data is incomplete."]);
}
?> 