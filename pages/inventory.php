<?php
// Require authentication
requireLogin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize book model
$book = new Book($db);

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;

// Get books count
$totalItems = $book->count();

// Get books based on search or all books
if (!empty($search)) {
    $stmt = $book->search($search);
} else {
    // Calculate offset for pagination
    $offset = ($page - 1) * $itemsPerPage;
    
    // In a real application, you would get books with pagination
    // For simplicity, we'll get all books
    $stmt = $book->getAll();
}

// Fetch all books
$books = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $books[] = $row;
}

// Get inventory statistics
$inStock = 0;
$lowStock = 0;
$outOfStock = 0;

foreach ($books as $book) {
    if ($book['stock_qty'] == 0) {
        $outOfStock++;
    } else if ($book['stock_qty'] <= $book['low_stock_threshold']) {
        $lowStock++;
    } else {
        $inStock++;
    }
}
?>

<section id="inventory" class="tab-content active">
    <div class="container">
        <div class="left-column">
            <div class="card">
                <div class="card-header">
                    <h2>Book Inventory</h2>
                    <div class="header-actions">
                        <button id="add-book-btn" class="btn-primary">
                            <i class="fas fa-plus"></i> Add Book
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="search-bar">
                        <form action="" method="get">
                            <input type="hidden" name="tab" value="inventory">
                            <div class="search-inventory">
                                <input type="text" name="search" placeholder="Search books..." value="<?php echo $search; ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (!empty($search)): ?>
                        <div class="search-results-info">
                            Showing search results for: <strong><?php echo $search; ?></strong>
                            <a href="index.php?tab=inventory" class="clear-search">Clear search</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="inventory-table-wrapper">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($books)): ?>
                                    <tr>
                                        <td colspan="7" class="no-data">No books found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo $book['isbn'] ? htmlspecialchars($book['isbn']) : '-'; ?></td>
                                            <td><?php echo formatMoney($book['price']); ?></td>
                                            <td><?php echo $book['stock_qty']; ?></td>
                                            <td><?php echo getInventoryStatusBadge($book['stock_qty'], $book['low_stock_threshold']); ?></td>
                                            <td class="actions">
                                                <button class="edit-book-btn" data-id="<?php echo $book['book_id']; ?>" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="reorder-book-btn" data-id="<?php echo $book['book_id']; ?>" title="Reorder">
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                                <button class="delete-book-btn" data-id="<?php echo $book['book_id']; ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (!empty($search)): ?>
                        <!-- No pagination for search results -->
                    <?php else: ?>
                        <div class="pagination-container">
                            <?php echo getPagination($totalItems, $page, $itemsPerPage, 'index.php?tab=inventory&page=%d'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="right-column">
            <div class="card">
                <div class="card-header">
                    <h2>Inventory Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="inventory-stats">
                        <div class="stat-box">
                            <span class="stat-label">In Stock</span>
                            <span class="stat-value"><?php echo $inStock; ?></span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Low Stock</span>
                            <span class="stat-value"><?php echo $lowStock; ?></span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Out of Stock</span>
                            <span class="stat-value"><?php echo $outOfStock; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Low Stock Items</h2>
                </div>
                <div class="card-body">
                    <?php
                    $lowStockItems = array_filter($books, function($book) {
                        return $book['stock_qty'] <= $book['low_stock_threshold'] && $book['stock_qty'] > 0;
                    });
                    
                    if (empty($lowStockItems)):
                    ?>
                        <div class="no-data">No low stock items.</div>
                    <?php else: ?>
                        <div class="low-stock-items">
                            <?php foreach (array_slice($lowStockItems, 0, 5) as $item): ?>
                                <div class="low-stock-item">
                                    <div class="item-info">
                                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($item['author']); ?></p>
                                    </div>
                                    <div class="item-stock">
                                        <span class="remaining"><?php echo $item['stock_qty']; ?></span>
                                        <span class="remaining-label">remaining</span>
                                        <button class="reorder-btn" data-id="<?php echo $item['book_id']; ?>">Reorder</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Book Add/Edit Modal Template -->
<template id="book-template">
    <div class="book-form">
        <div class="form-group">
            <label for="book-title">Title*</label>
            <input type="text" id="book-title" placeholder="Enter book title" required>
        </div>
        <div class="form-group">
            <label for="book-author">Author*</label>
            <input type="text" id="book-author" placeholder="Enter author name" required>
        </div>
        <div class="form-group">
            <label for="book-isbn">ISBN</label>
            <input type="text" id="book-isbn" placeholder="Enter ISBN">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="book-price">Price*</label>
                <input type="number" id="book-price" placeholder="0.00" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="book-stock">Stock Quantity*</label>
                <input type="number" id="book-stock" placeholder="0" min="0" required>
            </div>
        </div>
        <div class="form-group">
            <label for="book-threshold">Low Stock Threshold</label>
            <input type="number" id="book-threshold" placeholder="5" min="1" value="5">
        </div>
        <p class="form-note">* Required fields</p>
    </div>
</template>

<!-- Reorder Book Modal Template -->
<template id="reorder-template">
    <div class="reorder-form">
        <div class="form-group">
            <label for="reorder-book">Book</label>
            <input type="text" id="reorder-book" readonly>
        </div>
        <div class="form-group">
            <label for="reorder-current-stock">Current Stock</label>
            <input type="number" id="reorder-current-stock" readonly>
        </div>
        <div class="form-group">
            <label for="reorder-quantity">Quantity to Order*</label>
            <input type="number" id="reorder-quantity" min="1" value="10" required>
        </div>
        <div class="form-group">
            <label for="reorder-notes">Notes</label>
            <textarea id="reorder-notes" placeholder="Add any special instructions..."></textarea>
        </div>
        <p class="form-note">* Required fields</p>
    </div>
</template>