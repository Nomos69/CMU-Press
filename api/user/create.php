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
    !empty($data->name) &&
    !empty($data->username) &&
    !empty($data->password) &&
    !empty($data->role)
){
    $name = htmlspecialchars(strip_tags($data->name));
    $username = htmlspecialchars(strip_tags($data->username));
    $password = htmlspecialchars(strip_tags($data->password));
    $role = htmlspecialchars(strip_tags($data->role));
    // Check if username already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Username already exists."]);
        exit;
    }
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (name, username, password, role) VALUES (:name, :username, :password, :role)");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":role", $role);
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "User was created successfully.",
            "user_id" => $db->lastInsertId()
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["success" => false, "message" => "Unable to create user."]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Unable to create user. Data is incomplete."
    ]);
}
?>
