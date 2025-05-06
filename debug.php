<?php
// Debug and testing page for CMU-Press

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database and models
include_once 'config/database.php';
include_once 'models/Book.php';
include_once 'includes/logger.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Header
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CMU-Press Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .result { background-color: #f5f5f5; padding: 10px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap; }
        button, input[type="submit"] { background-color: #4CAF50; color: white; padding: 8px 15px; border: none; cursor: pointer; margin: 5px 0; }
        input[type="text"], input[type="number"] { padding: 8px; margin: 5px 0; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>CMU-Press Debug Tools</h1>';

// Book inventory section
echo '<div class="section">
    <h2>Book Inventory</h2>';

// Display books in the database
$book = new Book($db);
$stmt = $book->getAll();
$num = $stmt->rowCount();

if ($num > 0) {
    echo '<table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>';
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        echo '<tr>
            <td>' . $book_id . '</td>
            <td>' . $title . '</td>
            <td>' . $stock_qty . '</td>
            <td>
                <form method="post" style="display:inline-block;">
                    <input type="hidden" name="book_id" value="' . $book_id . '">
                    <input type="hidden" name="action" value="decrease">
                    <input type="number" name="quantity" value="1" min="1" max="' . $stock_qty . '" style="width:60px;">
                    <input type="submit" value="Decrease" name="update_stock">
                </form>
                <form method="post" style="display:inline-block;">
                    <input type="hidden" name="book_id" value="' . $book_id . '">
                    <input type="hidden" name="action" value="increase">
                    <input type="number" name="quantity" value="1" min="1" style="width:60px;">
                    <input type="submit" value="Increase" name="update_stock">
                </form>
            </td>
        </tr>';
    }
    
    echo '</table>';
} else {
    echo '<p>No books found in the database.</p>';
}

// Process stock update form
if (isset($_POST['update_stock'])) {
    $book_id = $_POST['book_id'];
    $quantity = $_POST['quantity'];
    $action = $_POST['action'];
    
    $book = new Book($db);
    $book->book_id = $book_id;
    
    if ($book->getById()) {
        $old_stock = $book->stock_qty;
        
        if ($action == 'decrease') {
            $result = $book->updateStock($quantity);
        } else {
            $result = $book->increaseStock($quantity);
        }
        
        if ($result) {
            // Get updated book data
            $book->getById();
            echo '<div class="result">Stock ' . $action . 'd successfully! Book #' . $book_id . ' (' . $book->title . '): ' . $old_stock . ' → ' . $book->stock_qty . '</div>';
        } else {
            echo '<div class="result">Error updating stock!</div>';
        }
    } else {
        echo '<div class="result">Book not found!</div>';
    }
}

echo '</div>';

// Test API section
echo '<div class="section">
    <h2>Test API Endpoints</h2>
    
    <h3>1. Direct Database Update</h3>
    <form method="post">
        <label>Book ID: <input type="text" name="direct_book_id" value="1"></label><br>
        <label>Quantity: <input type="text" name="direct_quantity" value="1"></label><br>
        <input type="submit" name="test_direct_update" value="Test Direct Update">
    </form>';

if (isset($_POST['test_direct_update'])) {
    $book_id = $_POST['direct_book_id'];
    $quantity = $_POST['direct_quantity'];
    
    $book = new Book($db);
    $book->book_id = $book_id;
    
    if ($book->getById()) {
        $old_stock = $book->stock_qty;
        
        // Direct update
        $result = $book->updateStock($quantity);
        
        if ($result) {
            // Get updated book data
            $book->getById();
            echo '<div class="result">Direct update successful! Book #' . $book_id . ' (' . $book->title . '): ' . $old_stock . ' → ' . $book->stock_qty . '</div>';
        } else {
            echo '<div class="result">Error with direct update!</div>';
        }
    } else {
        echo '<div class="result">Book not found!</div>';
    }
}

echo '<h3>2. Manual Update API Endpoint</h3>
    <form method="post">
        <label>Book ID: <input type="text" name="api_book_id" value="1"></label><br>
        <label>Quantity: <input type="text" name="api_quantity" value="1"></label><br>
        <input type="submit" name="test_manual_update_api" value="Test Manual Update API">
    </form>';

if (isset($_POST['test_manual_update_api'])) {
    $book_id = $_POST['api_book_id'];
    $quantity = $_POST['api_quantity'];
    
    // Call the manual update API
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/inventory/manual_update.php';
    $data = array(
        'book_id' => $book_id,
        'quantity' => $quantity,
        'operation' => 'decrease',
        'context' => 'Debug test'
    );
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        echo '<div class="result">Error calling API!</div>';
    } else {
        echo '<div class="result">API Response:<br>' . htmlspecialchars($result) . '</div>';
    }
}

echo '<h3>3. Transaction Update API Endpoint</h3>
    <form method="post">
        <label>Book ID: <input type="text" name="trans_book_id" value="1"></label><br>
        <label>Quantity: <input type="text" name="trans_quantity" value="1"></label><br>
        <input type="submit" name="test_transaction_api" value="Test Transaction Update API">
    </form>';

if (isset($_POST['test_transaction_api'])) {
    $book_id = $_POST['trans_book_id'];
    $quantity = $_POST['trans_quantity'];
    
    // Call the transaction update API
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/inventory/update_after_sale.php';
    $data = array(
        'transaction_id' => 12345,
        'items' => array(
            array(
                'book_id' => $book_id,
                'quantity' => $quantity,
                'title' => 'Test Book'
            )
        )
    );
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        echo '<div class="result">Error calling API!</div>';
    } else {
        echo '<div class="result">API Response:<br>' . htmlspecialchars($result) . '</div>';
    }
}

echo '</div>';

// Check debug log
echo '<div class="section">
    <h2>Debug Log</h2>';

$debug_log = 'logs/debug.log';
if (file_exists($debug_log)) {
    $log_content = file_get_contents($debug_log);
    echo '<div class="result">' . htmlspecialchars($log_content) . '</div>';
} else {
    echo '<div class="result">Debug log file not found at: ' . $debug_log . '</div>';
}

echo '</div>';

// Check inventory log
echo '<div class="section">
    <h2>Inventory Log</h2>';

$inventory_log = 'logs/inventory.log';
if (file_exists($inventory_log)) {
    $log_content = file_get_contents($inventory_log);
    echo '<div class="result">' . htmlspecialchars($log_content) . '</div>';
} else {
    echo '<div class="result">Inventory log file not found at: ' . $inventory_log . '</div>';
}

echo '</div>';

// Footer
echo '</body>
</html>';
?> 