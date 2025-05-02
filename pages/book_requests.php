<?php
// Require authentication
requireLogin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize book request model
$bookRequest = new BookRequest($db);

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get status filter
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';

// Get priority filter
$priorityFilter = isset($_GET['priority']) ? sanitizeInput($_GET['priority']) : 'all';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;

// Get book requests based on filters
$query = "SELECT * FROM book_requests WHERE 1=1";

// Add search filter if provided
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR author LIKE :search OR requested_by LIKE :search)";
}

// Add status filter if provided
if ($statusFilter !== 'all') {
    $query .= " AND status = :status";
}

// Add priority filter if provided
if ($priorityFilter !== 'all') {
    $query .= " AND priority = :priority";
}

// Order by priority and date
$query .= " ORDER BY 
            CASE priority 
                WHEN 'high' THEN 1 
                WHEN 'medium' THEN 2 
                WHEN 'low' THEN 3 
            END, 
            request_date DESC";

// Calculate offset for pagination
$offset = ($page - 1) * $itemsPerPage;
$query .= " LIMIT :offset, :limit";

// Prepare and execute the query
$stmt = $db->prepare($query);

// Bind parameters
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam);
}

if ($statusFilter !== 'all') {
    $stmt->bindParam(':status', $statusFilter);
}

if ($priorityFilter !== 'all') {
    $stmt->bindParam(':priority', $priorityFilter);
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();

// Fetch all requests
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total requests for pagination
$countQuery = "SELECT COUNT(*) as total FROM book_requests WHERE 1=1";

// Add search filter if provided
if (!empty($search)) {
    $countQuery .= " AND (title LIKE :search OR author LIKE :search OR requested_by LIKE :search)";
}

// Add status filter if provided
if ($statusFilter !== 'all') {
    $countQuery .= " AND status = :status";
}

// Add priority filter if provided
if ($priorityFilter !== 'all') {
    $countQuery .= " AND priority = :priority";
}

$countStmt = $db->prepare($countQuery);

// Bind parameters for count query
if (!empty($search)) {
    $countStmt->bindParam(':search', $searchParam);
}

if ($statusFilter !== 'all') {
    $countStmt->bindParam(':status', $statusFilter);
}

if ($priorityFilter !== 'all') {
    $countStmt->bindParam(':priority', $priorityFilter);
}

$countStmt->execute();
$totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get status counts
$statusQuery = "SELECT status, COUNT(*) as count FROM book_requests GROUP BY status";
$statusStmt = $db->prepare($statusQuery);
$statusStmt->execute();
$statusCounts = [
    'pending' => 0,
    'ordered' => 0,
    'fulfilled' => 0,
    'cancelled' => 0
];

while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = $row['count'];
}

$totalRequests = array_sum($statusCounts);

// Get priority counts
$priorityQuery = "SELECT priority, COUNT(*) as count FROM book_requests WHERE status = 'pending' GROUP BY priority";
$priorityStmt = $db->prepare($priorityQuery);
$priorityStmt->execute();
$priorityCounts = [
    'high' => 0,
    'medium' => 0,
    'low' => 0
];

while ($row = $priorityStmt->fetch(PDO::FETCH_ASSOC)) {
    $priorityCounts[$row['priority']] = $row['count'];
}
?>

<section id="book_requests" class="tab-content active">
    <div class="container">
        <div class="left-column">
            <div class="card">
                <div class="card-header">
                    <h2>Book Requests</h2>
                    <div class="header-actions">
                        <button id="add-request-btn" class="btn-primary">
                            <i class="fas fa-plus"></i> New Request
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="filters">
                        <form action="" method="get" class="filter-form">
                            <input type="hidden" name="tab" value="book_requests">
                            
                            <div class="filter-group">
                                <input type="text" name="search" placeholder="Search requests..." value="<?php echo $search; ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                            
                            <div class="filter-group">
                                <select name="status" id="status-filter">
                                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="ordered" <?php echo $statusFilter === 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                                    <option value="fulfilled" <?php echo $statusFilter === 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <select name="priority" id="priority-filter">
                                    <option value="all" <?php echo $priorityFilter === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                                    <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Low</option>
                                </select>
                            </div>
                            
                            <?php if (!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all'): ?>
                                <div class="filter-group">
                                    <a href="index.php?tab=book_requests" class="clear-filters">Clear Filters</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="requests-table-wrapper">
                        <table class="requests-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Requested By</th>
                                    <th>Date</th>
                                    <th>Priority</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="8" class="no-data">No book requests found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr data-id="<?php echo $request['request_id']; ?>">
                                            <td><?php echo htmlspecialchars($request['title']); ?></td>
                                            <td><?php echo htmlspecialchars($request['author']); ?></td>
                                            <td><?php echo htmlspecialchars($request['requested_by']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($request['request_date'])); ?></td>
                                            <td><?php echo getPriorityBadge($request['priority']); ?></td>
                                            <td><?php echo $request['quantity']; ?></td>
                                            <td><?php echo getStatusBadge($request['status']); ?></td>
                                            <td class="actions">
                                                <button class="edit-request-btn" data-id="<?php echo $request['request_id']; ?>" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button class="fulfill-request-btn" data-id="<?php echo $request['request_id']; ?>" title="Fulfill">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button class="cancel-request-btn" data-id="<?php echo $request['request_id']; ?>" title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-container">
                            <?php 
                            $queryParams = http_build_query(array_filter([
                                'tab' => 'book_requests',
                                'search' => $search,
                                'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                'priority' => $priorityFilter !== 'all' ? $priorityFilter : null
                            ]));
                            
                            $url = 'index.php?' . $queryParams . '&page=%d';
                            echo getPagination($totalItems, $page, $itemsPerPage, $url); 
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="right-column">
            <div class="card">
                <div class="card-header">
                    <h2>Request Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="stat-section">
                        <h3>Status Breakdown</h3>
                        <div class="stat-bars">
                            <div class="stat-bar">
                                <div class="stat-label">Pending</div>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?php echo $totalRequests > 0 ? ($statusCounts['pending'] / $totalRequests * 100) . '%' : '0%'; ?>; background-color: #ff9800;"></div>
                                </div>
                                <div class="stat-value"><?php echo $statusCounts['pending']; ?></div>
                            </div>
                            <div class="stat-bar">
                                <div class="stat-label">Ordered</div>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?php echo $totalRequests > 0 ? ($statusCounts['ordered'] / $totalRequests * 100) . '%' : '0%'; ?>; background-color: #2196f3;"></div>
                                </div>
                                <div class="stat-value"><?php echo $statusCounts['ordered']; ?></div>
                            </div>
                            <div class="stat-bar">
                                <div class="stat-label">Fulfilled</div>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?php echo $totalRequests > 0 ? ($statusCounts['fulfilled'] / $totalRequests * 100) . '%' : '0%'; ?>; background-color: #4caf50;"></div>
                                </div>
                                <div class="stat-value"><?php echo $statusCounts['fulfilled']; ?></div>
                            </div>
                            <div class="stat-bar">
                                <div class="stat-label">Cancelled</div>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?php echo $totalRequests > 0 ? ($statusCounts['cancelled'] / $totalRequests * 100) . '%' : '0%'; ?>; background-color: #f44336;"></div>
                                </div>
                                <div class="stat-value"><?php echo $statusCounts['cancelled']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-section">
                        <h3>Priority Breakdown</h3>
                        <div class="priority-stats">
                            <div class="priority-stat high">
                                <div class="priority-icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="priority-details">
                                    <div class="priority-label">High Priority</div>
                                    <div class="priority-value"><?php echo $priorityCounts['high']; ?></div>
                                </div>
                            </div>
                            <div class="priority-stat medium">
                                <div class="priority-icon"><i class="fas fa-arrow-circle-up"></i></div>
                                <div class="priority-details">
                                    <div class="priority-label">Medium Priority</div>
                                    <div class="priority-value"><?php echo $priorityCounts['medium']; ?></div>
                                </div>
                            </div>
                            <div class="priority-stat low">
                                <div class="priority-icon"><i class="fas fa-arrow-circle-down"></i></div>
                                <div class="priority-details">
                                    <div class="priority-label">Low Priority</div>
                                    <div class="priority-value"><?php echo $priorityCounts['low']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Actions</h2>
                </div>
                <div class="card-body">
                    <div class="action-buttons request-actions">
                        <button id="export-requests-btn" class="btn-secondary">
                            <i class="fas fa-file-export"></i> Export Requests
                        </button>
                        <button id="print-requests-btn" class="btn-secondary">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Book Request Add/Edit Modal Template -->
<template id="request-template">
    <div class="request-form">
        <div class="form-group">
            <label for="request-title">Book Title*</label>
            <input type="text" id="request-title" placeholder="Enter book title" required>
        </div>
        <div class="form-group">
            <label for="request-author">Author</label>
            <input type="text" id="request-author" placeholder="Enter author name">
        </div>
        <div class="form-group">
            <label for="request-by">Requested By*</label>
            <input type="text" id="request-by" placeholder="Enter name of requestor" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="request-priority">Priority</label>
                <select id="request-priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="form-group">
                <label for="request-quantity">Quantity</label>
                <input type="number" id="request-quantity" value="1" min="1">
            </div>
        </div>
        <div class="form-group">
            <label for="request-status">Status</label>
            <select id="request-status">
                <option value="pending">Pending</option>
                <option value="ordered">Ordered</option>
                <option value="fulfilled">Fulfilled</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <p class="form-note">* Required fields</p>
    </div>
</template>

<script src="assets/js/book-requests.js"></script>