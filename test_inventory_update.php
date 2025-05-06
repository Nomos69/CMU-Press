<?php
// Testing script for inventory updates

// Include database and required models
include_once 'config/database.php';
include_once 'models/Book.php';
include_once 'includes/logger.php';

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Book ID to test
$book_id = 1;
$quantity = 1;

echo "<h2>Inventory Update Test</h2>";

// Get current book data
$book = new Book($db);
$book->book_id = $book_id;

if ($book->getById()) {
    echo "<p>Before update: Book #{$book->book_id} ({$book->title}) has {$book->stock_qty} in stock</p>";
    
    // Decrease stock
    $result = $book->updateStock($quantity);
    
    // Check result
    if ($result) {
        // Get updated book data
        $book->getById();
        echo "<p>After update: Stock decreased to {$book->stock_qty}</p>";
        echo "<p>Update was successful</p>";
    } else {
        echo "<p>Failed to update stock</p>";
    }
} else {
    echo "<p>Book with ID {$book_id} not found</p>";
}

// Also test the API endpoint directly
echo "<h2>Testing API Endpoint</h2>";

$apiUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api/inventory/update_after_sale.php";
echo "<p>API URL: {$apiUrl}</p>";

$data = json_encode([
    'transaction_id' => 12345,
    'items' => [
        [
            'book_id' => $book_id,
            'quantity' => 1,
            'title' => 'Test Book'
        ]
    ]
]);

// Make API request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Status Code: {$httpCode}</p>";

if ($error) {
    echo "<p>cURL Error: {$error}</p>";
} else {
    echo "<p>Response:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Parse JSON response
    $responseData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p>JSON decoded successfully</p>";
        
        if ($responseData['success']) {
            echo "<p>API reported success</p>";
            
            // Get current book data again to verify
            $book = new Book($db);
            $book->book_id = $book_id;
            if ($book->getById()) {
                echo "<p>Final stock check: Book #{$book->book_id} ({$book->title}) now has {$book->stock_qty} in stock</p>";
            }
        } else {
            echo "<p>API reported failure: " . $responseData['message'] . "</p>";
        }
    } else {
        echo "<p>JSON decode error: " . json_last_error_msg() . "</p>";
    }
}

// Check the inventory log
echo "<h2>Inventory Log</h2>";
$logFile = 'logs/inventory.log';
if (file_exists($logFile)) {
    echo "<p>Log file exists</p>";
    $logContent = file_get_contents($logFile);
    echo "<pre>" . htmlspecialchars($logContent) . "</pre>";
} else {
    echo "<p>Log file not found at: {$logFile}</p>";
}
?> 