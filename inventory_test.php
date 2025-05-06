<?php
/**
 * Inventory Update Test
 * This script tests all inventory update methods to ensure they work correctly
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include_once 'config/database.php';
include_once 'models/Book.php';
include_once 'includes/logger.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Helper function to display results
function printResult($title, $result) {
    echo "<div style='margin-bottom: 20px;'>";
    echo "<h3>{$title}</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars(print_r($result, true));
    echo "</pre>";
    echo "</div>";
}

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Inventory Update Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; margin-top: 10px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Inventory Update Test</h1>";

// Test 1: Direct method test
echo "<h2>Test 1: Direct Method Test</h2>";

// Get a book to test with
$book = new Book($db);
$book_id = 1; // Use book ID 1 for testing
$book->book_id = $book_id;

if ($book->getById()) {
    $original_stock = $book->stock_qty;
    $test_quantity = 1;
    
    // Display original stock
    echo "<p>Book: <strong>{$book->title}</strong> (ID: {$book->book_id})</p>";
    echo "<p>Original stock: <strong>{$original_stock}</strong></p>";
    
    // Test updateStock method (decrease)
    echo "<h3>Testing updateStock() - Decrease</h3>";
    $decrease_result = $book->updateStock($test_quantity);
    
    // Refresh book data
    $book->getById();
    $after_decrease = $book->stock_qty;
    
    echo "<p>Decrease result: " . ($decrease_result ? "<span class='success'>Success</span>" : "<span class='error'>Failed</span>") . "</p>";
    echo "<p>Stock after decrease: <strong>{$after_decrease}</strong> (Should be " . ($original_stock - $test_quantity) . ")</p>";
    
    // Test increaseStock method (increase)
    echo "<h3>Testing increaseStock() - Increase</h3>";
    $increase_result = $book->increaseStock($test_quantity);
    
    // Refresh book data
    $book->getById();
    $after_increase = $book->stock_qty;
    
    echo "<p>Increase result: " . ($increase_result ? "<span class='success'>Success</span>" : "<span class='error'>Failed</span>") . "</p>";
    echo "<p>Stock after increase: <strong>{$after_increase}</strong> (Should be back to {$original_stock})</p>";
    
    // Check if we're back to where we started
    if ($after_increase == $original_stock) {
        echo "<p class='success'>Test complete: Stock returned to original value.</p>";
    } else {
        echo "<p class='error'>Test failed: Stock did not return to original value.</p>";
    }
} else {
    echo "<p class='error'>Error: Could not find book with ID {$book_id}.</p>";
}

// Test 2: API Test - Manual Update
echo "<h2>Test 2: API Test - Manual Update</h2>";

if ($book->getById()) {
    $api_original_stock = $book->stock_qty;
    $api_test_quantity = 1;
    
    echo "<p>Original stock before API test: <strong>{$api_original_stock}</strong></p>";
    
    // Use the manual update API
    $manual_update_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api/inventory/manual_update.php";
    echo "<p>API URL: {$manual_update_url}</p>";
    
    $manual_test_data = array(
        'book_id' => $book_id,
        'quantity' => $api_test_quantity,
        'operation' => 'decrease',
        'context' => 'Inventory Test'
    );
    
    // Setup request
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($manual_test_data)
        )
    );
    
    $context = stream_context_create($options);
    $manual_result = file_get_contents($manual_update_url, false, $context);
    
    if ($manual_result === FALSE) {
        echo "<p class='error'>Error calling manual update API.</p>";
    } else {
        echo "<p>Manual API Response:</p>";
        echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($manual_result);
        echo "</pre>";
        
        // Refresh book data
        $book->getById();
        $after_api_decrease = $book->stock_qty;
        
        echo "<p>Stock after API decrease: <strong>{$after_api_decrease}</strong> (Should be " . ($api_original_stock - $api_test_quantity) . ")</p>";
        
        // Now increase it back using the API
        $manual_test_data['operation'] = 'increase';
        
        $options['http']['content'] = json_encode($manual_test_data);
        $context = stream_context_create($options);
        $manual_increase_result = file_get_contents($manual_update_url, false, $context);
        
        if ($manual_increase_result === FALSE) {
            echo "<p class='error'>Error calling manual increase API.</p>";
        } else {
            echo "<p>Manual Increase API Response:</p>";
            echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
            echo htmlspecialchars($manual_increase_result);
            echo "</pre>";
            
            // Refresh book data
            $book->getById();
            $after_api_increase = $book->stock_qty;
            
            echo "<p>Stock after API increase: <strong>{$after_api_increase}</strong> (Should be back to {$api_original_stock})</p>";
            
            // Check if we're back to where we started
            if ($after_api_increase == $api_original_stock) {
                echo "<p class='success'>API Test complete: Stock returned to original value.</p>";
            } else {
                echo "<p class='error'>API Test failed: Stock did not return to original value.</p>";
            }
        }
    }
} else {
    echo "<p class='error'>Error: Could not find book with ID {$book_id} for API test.</p>";
}

// Test 3: After-Sale API Test
echo "<h2>Test 3: After-Sale API Test</h2>";

if ($book->getById()) {
    $sale_original_stock = $book->stock_qty;
    $sale_test_quantity = 1;
    
    echo "<p>Original stock before sale API test: <strong>{$sale_original_stock}</strong></p>";
    
    // Use the after-sale update API
    $sale_update_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api/inventory/update_after_sale.php";
    echo "<p>API URL: {$sale_update_url}</p>";
    
    $sale_test_data = array(
        'transaction_id' => 99999, // Test transaction ID
        'items' => array(
            array(
                'book_id' => $book_id,
                'quantity' => $sale_test_quantity,
                'title' => 'Test Book'
            )
        )
    );
    
    // Setup request
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($sale_test_data)
        )
    );
    
    $context = stream_context_create($options);
    $sale_result = file_get_contents($sale_update_url, false, $context);
    
    if ($sale_result === FALSE) {
        echo "<p class='error'>Error calling after-sale update API.</p>";
    } else {
        echo "<p>After-Sale API Response:</p>";
        echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($sale_result);
        echo "</pre>";
        
        // Refresh book data
        $book->getById();
        $after_sale_decrease = $book->stock_qty;
        
        echo "<p>Stock after sale decrease: <strong>{$after_sale_decrease}</strong> (Should be " . ($sale_original_stock - $sale_test_quantity) . ")</p>";
        
        // Now increase it back using the manual update API
        $manual_test_data = array(
            'book_id' => $book_id,
            'quantity' => $sale_test_quantity,
            'operation' => 'increase',
            'context' => 'Inventory Test - After Sale Cleanup'
        );
        
        $options['http']['content'] = json_encode($manual_test_data);
        $context = stream_context_create($options);
        $manual_increase_result = file_get_contents($manual_update_url, false, $context);
        
        if ($manual_increase_result === FALSE) {
            echo "<p class='error'>Error when restoring stock after sale test.</p>";
        } else {
            // Refresh book data
            $book->getById();
            $after_sale_restore = $book->stock_qty;
            
            echo "<p>Stock after cleanup: <strong>{$after_sale_restore}</strong> (Should be back to {$sale_original_stock})</p>";
            
            // Check if we're back to where we started
            if ($after_sale_restore == $sale_original_stock) {
                echo "<p class='success'>After-Sale API Test complete: Stock returned to original value.</p>";
            } else {
                echo "<p class='error'>After-Sale API Test failed: Stock did not return to original value.</p>";
            }
        }
    }
} else {
    echo "<p class='error'>Error: Could not find book with ID {$book_id} for After-Sale API test.</p>";
}

// Check the logs
echo "<h2>Log Files</h2>";

// Debug log
$debug_log_path = 'logs/debug.log';
if (file_exists($debug_log_path)) {
    echo "<h3>Debug Log</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars(file_get_contents($debug_log_path));
    echo "</pre>";
} else {
    echo "<p>Debug log file not found: {$debug_log_path}</p>";
}

// Inventory log
$inventory_log_path = 'logs/inventory.log';
if (file_exists($inventory_log_path)) {
    echo "<h3>Inventory Log</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars(file_get_contents($inventory_log_path));
    echo "</pre>";
} else {
    echo "<p>Inventory log file not found: {$inventory_log_path}</p>";
}

// Footer
echo "<a href='debug.php'><button>Go to Debug Page</button></a>";
echo "</body></html>";
?> 