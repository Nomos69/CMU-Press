<?php
// Enable error reporting 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require authentication
if (function_exists('requireLogin')) {
    requireLogin();
} else {
    // Fallback check if function doesn't exist
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Check if database connection exists
if (!isset($db) || !$db) {
    // Try to create the database connection
    if (class_exists('Database')) {
        $database = new Database();
        $db = $database->getConnection();
    }
    
    // If still no database connection, show error
    if (!$db) {
        echo "Database connection error. Please check your database settings.";
        exit;
    }
}

// Make sure required models are loaded
if (!class_exists('Book')) {
    require_once 'models/Book.php';
}
if (!class_exists('Transaction')) {
    require_once 'models/Transaction.php';
}
if (!class_exists('TransactionItem')) {
    require_once 'models/TransactionItem.php';
}
if (!class_exists('BookRequest')) {
    require_once 'models/BookRequest.php';
}

// Get the next transaction ID
try {
    $query = "SELECT MAX(transaction_id) as max_id FROM transactions";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $next_transaction_id = isset($row['max_id']) ? $row['max_id'] + 1 : 5789;
} catch (PDOException $e) {
    echo "Database query error: " . $e->getMessage();
    $next_transaction_id = 5789; // Default if there's an error
}

// Initialize Transaction model for statistics
$transaction = new Transaction($db);

// Get inventory statistics
try {
    $books = new Book($db);
    $inventory_query = "SELECT 
        SUM(CASE WHEN stock_qty > low_stock_threshold THEN 1 ELSE 0 END) as in_stock,
        SUM(CASE WHEN stock_qty <= low_stock_threshold AND stock_qty > 0 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN stock_qty = 0 THEN 1 ELSE 0 END) as out_of_stock
        FROM books";
    $stmt = $db->prepare($inventory_query);
    $stmt->execute();
    $inventory_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle null values
    $inventory_stats['in_stock'] = $inventory_stats['in_stock'] ?? 0;
    $inventory_stats['low_stock'] = $inventory_stats['low_stock'] ?? 0;
    $inventory_stats['out_of_stock'] = $inventory_stats['out_of_stock'] ?? 0;
} catch (PDOException $e) {
    echo "Inventory query error: " . $e->getMessage();
    $inventory_stats = [
        'in_stock' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0
    ];
}

// Get low stock items
try {
    $low_stock_query = "SELECT * FROM books 
                        WHERE stock_qty <= low_stock_threshold AND stock_qty > 0 
                        ORDER BY stock_qty ASC LIMIT 3";
    $stmt = $db->prepare($low_stock_query);
    $stmt->execute();
    $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $low_stock_items = [];
}

// Get pending book requests
try {
    $book_requests = new BookRequest($db);
    $requests_query = "SELECT * FROM book_requests 
                      WHERE status = 'pending' 
                      ORDER BY 
                        CASE priority 
                            WHEN 'high' THEN 1 
                            WHEN 'medium' THEN 2 
                            WHEN 'low' THEN 3 
                        END, 
                        request_date DESC 
                      LIMIT 2";
    $stmt = $db->prepare($requests_query);
    $stmt->execute();
    $book_requests_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count pending requests
    $pending_count = $book_requests->countPending();
    
    // Get high priority book requests
    $high_priority_count = $book_requests->countHighPriority();
} catch (PDOException $e) {
    $book_requests_list = [];
    $pending_count = 0;
    $high_priority_count = 0;
}

// Get today's stats
try {
    $daily_stats = $transaction->getDayStats();
    $total_sales = $daily_stats['total_sales'] ? $daily_stats['total_sales'] : 0;
    $transaction_count = $daily_stats['transaction_count'] ? $daily_stats['transaction_count'] : 0;

    // Get books sold today
    $transaction_item = new TransactionItem($db);
    $books_sold = $transaction_item->getTotalBooksSold();
} catch (PDOException $e) {
    $total_sales = 0;
    $transaction_count = 0;
    $books_sold = 0;
}

// Get new customers today
try {
    $new_customers_query = "SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = CURDATE()";
    $stmt = $db->prepare($new_customers_query);
    $stmt->execute();
    $new_customers_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $new_customers = $new_customers_row['count'];

    // All customers are considered regular customers now
    $loyalty_customers = $new_customers;
} catch (PDOException $e) {
    $new_customers = 0;
    $loyalty_customers = 0;
}

// Get recent transactions
try {
    $recent_transactions = $transaction->getRecent(3);
    $recent_transactions_list = [];
    while ($row = $recent_transactions->fetch(PDO::FETCH_ASSOC)) {
        $recent_transactions_list[] = $row;
    }
} catch (PDOException $e) {
    $recent_transactions_list = [];
}
?>

<section id="pos" class="tab-content active">
    <div class="container">
        <div class="left-column">
            <!-- Current Transaction -->
            <div class="card">
                <div class="card-header">
                    <h2>Current Transaction #<span id="transaction-id"><?php echo $next_transaction_id; ?></span></h2>
                    <div class="transaction-options">
                        <button class="btn-option new-transaction" data-type="new"><i class="fas fa-plus"></i> New</button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>TITLE</th>
                                <th>AUTHOR</th>
                                <th>PRICE</th>
                                <th>QTY</th>
                                <th>TOTAL</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="transaction-items">
                            <!-- Transaction items will be added here dynamically -->
                        </tbody>
                    </table>
                    <div class="item-search">
                        <input type="text" id="item-search" placeholder="Scan barcode or search item">
                        <button id="add-item-btn"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="customer-field">
                        <input type="text" id="customer-field" placeholder="Customer name or phone">
                        <button id="add-customer-btn"><i class="fas fa-user-plus"></i></button>
                    </div>
                    <div class="transaction-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">₱ 0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="total">₱ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Section -->
            <div class="card">
                <div class="card-header">
                    <h2>Payment</h2>
                </div>
                <div class="card-body">
                    <div class="payment-options">
                        <button class="payment-btn active" data-method="cash">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </button>
                    </div>
                    <div class="action-buttons">
                        <button id="checkout-btn" class="btn-primary">Checkout (₱ 0.00)</button>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Transactions</h2>
                    <button id="view-all-transactions-btn" class="view-all">View All</button>
                </div>
                <div class="card-body">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>TIME</th>
                                <th>CUSTOMER</th>
                                <th>ITEMS</th>
                                <th>TOTAL</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_transactions_list)): ?>
                                <tr>
                                    <td colspan="6" class="no-data">No recent transactions.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_transactions_list as $trans): ?>
                                    <tr>
                                        <td>#<?php echo $trans['transaction_id']; ?></td>
                                        <td><?php echo date('g:i A', strtotime($trans['transaction_date'])); ?></td>
                                        <td><?php echo $trans['customer_name'] ? htmlspecialchars($trans['customer_name']) : 'Guest'; ?></td>
                                        <td><?php echo $trans['item_count']; ?></td>
                                        <td><?php echo formatMoney($trans['total']); ?></td>
                                        <td><?php echo getStatusBadge($trans['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="right-column">
            <!-- Inventory Status -->
            <div class="card">
                <div class="card-header">
                    <h2>Inventory Status</h2>
                    <a href="index.php?tab=inventory" class="view-all">View All</a>
                </div>
                <div class="card-body">
                    <div class="inventory-stats">
                        <div class="stat-box">
                            <span class="stat-label">In Stock</span>
                            <span class="stat-value"><?php echo number_format($inventory_stats['in_stock']); ?></span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Low Stock</span>
                            <span class="stat-value"><?php echo number_format($inventory_stats['low_stock']); ?></span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Out of Stock</span>
                            <span class="stat-value"><?php echo number_format($inventory_stats['out_of_stock']); ?></span>
                        </div>
                    </div>
                    <div class="search-inventory">
                        <input type="text" id="inventory-search" placeholder="Search inventory">
                        <button id="search-inventory-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="low-stock-items">
                        <!-- Low stock items removed -->
                    </div>
                    <button class="request-book-btn">
                        <i class="fas fa-book"></i>
                        Request Unavailable Book
                    </button>
                </div>
            </div>
            
            <!-- Pending Book Requests -->
            <div class="card">
                <div class="card-header">
                    <h2>Pending Book Requests</h2>
                    <span class="pending-count"><?php echo $pending_count; ?> Pending</span>
                </div>
                <div class="card-body">
                    <?php if (empty($book_requests_list)): ?>
                        <div class="no-data">No pending book requests.</div>
                    <?php else: ?>
                        <?php foreach($book_requests_list as $request): ?>
                        <div class="book-request">
                            <div class="request-info">
                                <h3><?php echo htmlspecialchars($request['title']); ?></h3>
                                <p><?php echo htmlspecialchars($request['author']); ?></p>
                                <p class="request-date">Requested: <?php echo date('M j, Y', strtotime($request['request_date'])); ?></p>
                            </div>
                            <div class="request-details">
                                <span class="priority <?php echo $request['priority']; ?>-priority">
                                    <?php echo ucfirst($request['priority']); ?> <?php echo $request['priority'] === 'high' ? 'Priority' : ''; ?>
                                </span>
                                <span class="quantity">Qty: <?php echo $request['quantity']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="index.php?tab=book_requests" class="view-all-btn">View All Requests</a>
                </div>
            </div>
            
        </div>
    </div>
</section>