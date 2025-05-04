<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and user model
include_once '../../config/database.php';
include_once '../../models/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->user_id) &&
    !empty($data->name) &&
    !empty($data->username) && 
    !empty($data->role)
){
    // Set user property values
    $user->user_id = $data->user_id;
    $user->name = $data->name;
    $user->username = $data->username;
    $user->role = $data->role;
    
    // Update the user
    if($user->update()){
        // Set response code - 200 ok
        http_response_code(200);
        
        // Tell the user
        echo json_encode(array("success" => true, "message" => "User was updated successfully."));
    }
    else{
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array("success" => false, "message" => "Unable to update user."));
    }
}
else{
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array("success" => false, "message" => "Unable to update user. Data is incomplete."));
} 