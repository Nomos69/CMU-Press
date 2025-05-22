<?php

// Set headers and handle CORS
function set_api_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST, GET, DELETE, PUT, OPTIONS"); // Add all common methods
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Get database connection
function get_db_connection() {
    include_once dirname(__FILE__) . '/../config/database.php'; // Adjust path as needed
    $db = null;
    try {
        $database = new Database();
        $db = $database->getConnection();
    } catch (Exception $e) {
        send_json_response(503, ["message" => "Database connection failed."]);
        exit;
    }
    return $db;
}

// Get JSON posted data
function get_json_input() {
    return json_decode(file_get_contents("php://input"));
}

// Send JSON response
function send_json_response($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit; // Exit after sending response
}

// Sanitize input string
function sanitize_input($data) {
    return htmlspecialchars(strip_tags($data));
}

?> 