<?php
// Require authentication
requireLogin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models for reports
$transaction = new Transaction($db);
$transactionItem = new TransactionItem($db);

// Get report type from URL parameter or default to 'sales'
$reportType = isset($_GET['report_type']) ? sanitizeInput($_GET['report_type']) : 'sales';

// Get date range from URL parameters or default to last 30 days
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $startDate = sanitizeInput($_GET['start_date']);
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $endDate = sanitizeInput($_GET['end_date']);
}

// Validate date range
if (strtotime($endDate) < strtotime($startDate)) {
    $temp = $endDate;
    $endDate = $startDate;
    $startDate = $temp;
}

// Function to generate sample daily sales data
function generateSampleDailySales($startDate, $endDate, $db) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    $days = $interval->days + 1;
    
    $dailySales = [];
    $currentDate = clone $start;
    
    for ($i = 0; $i < $days; $i++) {
        $dateStr = $currentDate->format('Y-m-d');
        
        // In a real application, you would get this data from the database
        // For this demo, we'll generate random data
        $amount = mt_rand(100, 400);
        $transactions = mt_rand(5, 20);
        
        $dailySales[] = [
            'date' => $dateStr,
            'formatted_date' => $currentDate->format('M j'),
            'amount' => $amount,
            'transactions' => $transactions
        ];
        
        $currentDate->modify('+1 day');
    }
    
    return $dailySales;
}

// Function to generate sample inventory data
function generateSampleInventoryData($db) {
    $categories = ['Fiction', 'Non-Fiction', 'Children\'s Books', 'Textbooks', 'Biography'];
    $inventoryData = [];
    
    foreach ($categories as $category) {
        $inventoryData[] = [
            'category' => $category,
            'in_stock' => mt_rand(50, 250),
            'low_stock' => mt_rand(5, 25),
            'out_of_stock' => mt_rand(0, 10)
        ];
    }
    
    return $inventoryData;
}

// Function to generate sample customer data
function generateSampleCustomerData($startDate, $endDate, $db) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    $days = $interval->days + 1;
    
    $customerData = [];
    $currentDate = clone $start;
    
    for ($i = 0; $i < $days; $i++) {
        $dateStr = $currentDate->format('Y-m-d');
        
        $count = mt_rand(1, 5);
        
        $customerData[] = [
            'date' => $dateStr,
            'formatted_date' => $currentDate->format('M j'),
            'count' => $count
        ];
        
        $currentDate->modify('+1 day');
    }
    
    return $customerData;
}

// Function to generate bestsellers data
function generateSampleBestsellersData($db) {
    $bestsellers = [
        ['title' => 'The Midnight Library', 'author' => 'Matt Haig', 'copies' => mt_rand(30, 40), 'revenue' => 0],
        ['title' => 'Klara and the Sun', 'author' => 'Kazuo Ishiguro', 'copies' => mt_rand(25, 35), 'revenue' => 0],
        ['title' => 'Project Hail Mary', 'author' => 'Andy Weir', 'copies' => mt_rand(20, 30), 'revenue' => 0],
        ['title' => 'The Invisible Life of Addie LaRue', 'author' => 'V.E. Schwab', 'copies' => mt_rand(15, 25), 'revenue' => 0],
        ['title' => 'The House in the Cerulean Sea', 'author' => 'TJ Klune', 'copies' => mt_rand(10, 20), 'revenue' => 0],
        ['title' => 'The Song of Achilles', 'author' => 'Madeline Miller', 'copies' => mt_rand(10, 20), 'revenue' => 0],
        ['title' => 'Dune', 'author' => 'Frank Herbert', 'copies' => mt_rand(10, 20), 'revenue' => 0],
        ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'copies' => mt_rand(5, 15), 'revenue' => 0],
        ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'copies' => mt_rand(5, 15), 'revenue' => 0],
        ['title' => '1984', 'author' => 'George Orwell', 'copies' => mt_rand(5, 15), 'revenue' => 0]
    ];
    
    // Calculate revenue
    foreach ($bestsellers as &$book) {
        $price = mt_rand(1499, 2499) / 100; // Generate price between $14.99 and $24.99
        $book['revenue'] = round($book['copies'] * $price, 2);
    }
    
    return $bestsellers;
}

// Generate report data based on type
$reportData = [];
$reportTitle = '';
$summary = [];

switch ($reportType) {
    case 'sales':
        $reportTitle = 'Sales Report';
        $dailySales = generateSampleDailySales($startDate, $endDate, $db);
        $reportData = $dailySales;
        
        // Calculate summary data
        $totalSales = 0;
        $totalTransactions = 0;
        
        foreach ($dailySales as $day) {
            $totalSales += $day['amount'];
            $totalTransactions += $day['transactions'];
        }
        
        $averageSale = $totalTransactions > 0 ? ($totalSales / $totalTransactions) : 0;
        
        $summary = [
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'average_sale' => $averageSale
        ];
        break;
    
    case 'inventory':
        $reportTitle = 'Inventory Report';
        $inventoryData = generateSampleInventoryData($db);
        $reportData = $inventoryData;
        
        // Calculate summary data
        $totalItems = 0;
        $totalValue = 0;
        $lowStockItems = 0;
        $outOfStockItems = 0;
        
        foreach ($inventoryData as $category) {
            $totalItems += $category['in_stock'] + $category['low_stock'] + $category['out_of_stock'];
            $lowStockItems += $category['low_stock'];
            $outOfStockItems += $category['out_of_stock'];
            
            // Calculate approximate value (average book price * quantity)
            $averagePrice = 19.99;
            $totalValue += ($category['in_stock'] + $category['low_stock']) * $averagePrice;
        }
        
        $summary = [
            'total_items' => $totalItems,
            'total_value' => $totalValue,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems
        ];
        break;
    
    case 'customers':
        $reportTitle = 'Customer Report';
        $customerData = generateSampleCustomerData($startDate, $endDate, $db);
        $reportData = $customerData;
        
        // Calculate summary data
        $totalNewCustomers = 0;
        
        foreach ($customerData as $day) {
            $totalNewCustomers += $day['count'];
        }
        
        $summary = [
            'total_new_customers' => $totalNewCustomers,
            'top_customers' => [
                ['name' => 'John Smith', 'purchases' => 12, 'spent' => 387.65],
                ['name' => 'Mary Johnson', 'purchases' => 9, 'spent' => 276.50],
                ['name' => 'Robert Brown', 'purchases' => 7, 'spent' => 198.20],
                ['name' => 'Patricia Davis', 'purchases' => 6, 'spent' => 167.85],
                ['name' => 'Michael Wilson', 'purchases' => 5, 'spent' => 145.30]
            ]
        ];
        break;
    
    case 'bestsellers':
        $reportTitle = 'Bestsellers Report';
        $bestsellersData = generateSampleBestsellersData($db);
        $reportData = $bestsellersData;
        
        // Calculate summary data
        $totalCopies = 0;
        $totalRevenue = 0;
        
        foreach ($bestsellersData as $book) {
            $totalCopies += $book['copies'];
            $totalRevenue += $book['revenue'];
        }
        
        $averagePrice = $totalCopies > 0 ? ($totalRevenue / $totalCopies) : 0;
        
        $summary = [
            'total_copies_sold' => $totalCopies,
            'total_revenue' => $totalRevenue,
            'average_price' => $averagePrice
        ];
        break;
    
    default:
        $reportTitle = 'Sales Report';
        $dailySales = generateSampleDailySales($startDate, $endDate, $db);
        $reportData = $dailySales;
        break;
}

// Format dates for display
$formattedStartDate = date('M j, Y', strtotime($startDate));
$formattedEndDate = date('M j, Y', strtotime($endDate));
?>

<section id="reports" class="tab-content active">
    <div class="container">
        <div class="left-column">
            <!-- Report Controls -->
            <div class="card">
                <div class="card-header">
                    <h2>Generate Report</h2>
                </div>
                <div class="card-body">
                    <form action="" method="get" class="report-controls">
                        <input type="hidden" name="tab" value="reports">
                        
                        <div class="form-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type">
                                <option value="sales" <?php echo $reportType === 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                                <option value="inventory" <?php echo $reportType === 'inventory' ? 'selected' : ''; ?>>Inventory Report</option>
                                <option value="customers" <?php echo $reportType === 'customers' ? 'selected' : ''; ?>>Customer Report</option>
                                <option value="bestsellers" <?php echo $reportType === 'bestsellers' ? 'selected' : ''; ?>>Bestsellers Report</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                            </div>
                        </div>
                        
                        <button type="submit" id="apply-date-btn" class="btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>
            
            <!-- Report Output -->
            <div class="card">
                <div class="card-header">
                    <h2><?php echo $reportTitle; ?></h2>
                    <span class="report-date-range">From <?php echo $formattedStartDate; ?> to <?php echo $formattedEndDate; ?></span>
                </div>
                <div class="card-body">
                    <?php if ($reportType === 'sales'): ?>
                        <!-- Sales Report -->
                        <div class="summary-cards">
                            <div class="summary-card">
                                <div class="card-title">Total Sales</div>
                                <div class="card-value">$<?php echo number_format($summary['total_sales'], 2); ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Average Sale</div>
                                <div class="card-value">$<?php echo number_format($summary['average_sale'], 2); ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Total Transactions</div>
                                <div class="card-value"><?php echo $summary['total_transactions']; ?></div>
                            </div>
                        </div>
                        
                        <div class="chart-container">
                            <canvas id="sales-chart"></canvas>
                        </div>
                    <?php elseif ($reportType === 'inventory'): ?>
                        <!-- Inventory Report -->
                        <div class="summary-cards">
                            <div class="summary-card">
                                <div class="card-title">Total Items</div>
                                <div class="card-value"><?php echo $summary['total_items']; ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Inventory Value</div>
                                <div class="card-value">$<?php echo number_format($summary['total_value'], 2); ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Low Stock Items</div>
                                <div class="card-value"><?php echo $summary['low_stock_items']; ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Out of Stock</div>
                                <div class="card-value"><?php echo $summary['out_of_stock_items']; ?></div>
                            </div>
                        </div>
                        
                        <div class="chart-container">
                            <canvas id="inventory-chart"></canvas>
                        </div>
                        
                        <h3>Inventory by Category</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>In Stock</th>
                                    <th>Low Stock</th>
                                    <th>Out of Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $item): ?>
                                <tr>
                                    <td><?php echo $item['category']; ?></td>
                                    <td><?php echo $item['in_stock']; ?></td>
                                    <td><?php echo $item['low_stock']; ?></td>
                                    <td><?php echo $item['out_of_stock']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($reportType === 'customers'): ?>
                        <!-- Customer Report -->
                        <div class="summary-cards">
                            <div class="summary-card">
                                <div class="card-title">New Customers</div>
                                <div class="card-value"><?php echo $summary['total_new_customers']; ?></div>
                            </div>
                        </div>
                        
                        <div class="chart-container">
                            <canvas id="customers-chart"></canvas>
                        </div>
                        
                        <h3>Top Customers</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Purchases</th>
                                    <th>Amount Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summary['top_customers'] as $customer): ?>
                                <tr>
                                    <td><?php echo $customer['name']; ?></td>
                                    <td><?php echo $customer['purchases']; ?></td>
                                    <td>$<?php echo number_format($customer['spent'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($reportType === 'bestsellers'): ?>
                        <!-- Bestsellers Report -->
                        <div class="summary-cards">
                            <div class="summary-card">
                                <div class="card-title">Total Books Sold</div>
                                <div class="card-value"><?php echo $summary['total_copies_sold']; ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Total Revenue</div>
                                <div class="card-value">$<?php echo number_format($summary['total_revenue'], 2); ?></div>
                            </div>
                            <div class="summary-card">
                                <div class="card-title">Average Price</div>
                                <div class="card-value">$<?php echo number_format($summary['average_price'], 2); ?></div>
                            </div>
                        </div>
                        
                        <div class="chart-container">
                            <canvas id="bestsellers-chart"></canvas>
                        </div>
                        
                        <h3>Top 10 Bestselling Books</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Copies Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $index => $book): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $book['title']; ?></td>
                                    <td><?php echo $book['author']; ?></td>
                                    <td><?php echo $book['copies']; ?></td>
                                    <td>$<?php echo number_format($book['revenue'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="right-column">
            <?php if ($reportType === 'sales'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Sales Insights</h2>
                </div>
                <div class="card-body">
                    <div class="insights">
                        <p><strong>Peak Sales Day:</strong> <?php echo $reportData[array_search(max(array_column($reportData, 'amount')), array_column($reportData, 'amount'))]['formatted_date']; ?></p>
                        <p><strong>Slowest Sales Day:</strong> <?php echo $reportData[array_search(min(array_column($reportData, 'amount')), array_column($reportData, 'amount'))]['formatted_date']; ?></p>
                        <p><strong>Sales Trend:</strong> 
                            <?php
                            $firstHalf = array_sum(array_column(array_slice($reportData, 0, ceil(count($reportData) / 2)), 'amount'));
                            $secondHalf = array_sum(array_column(array_slice($reportData, ceil(count($reportData) / 2)), 'amount'));
                            echo $secondHalf > $firstHalf ? 'Upward' : ($secondHalf < $firstHalf ? 'Downward' : 'Stable');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php elseif ($reportType === 'inventory'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Inventory Insights</h2>
                </div>
                <div class="card-body">
                    <div class="insights">
                        <p><strong>Largest Category:</strong> <?php echo $reportData[array_search(max(array_column($reportData, 'in_stock')), array_column($reportData, 'in_stock'))]['category']; ?></p>
                        <p><strong>Most Out of Stock:</strong> <?php echo $reportData[array_search(max(array_column($reportData, 'out_of_stock')), array_column($reportData, 'out_of_stock'))]['category']; ?></p>
                        <p><strong>Low Stock Percentage:</strong> <?php echo number_format(($summary['low_stock_items'] / $summary['total_items']) * 100, 1); ?>%</p>
                        <p><strong>Action Required:</strong> Reorder items from the <?php echo $reportData[array_search(max(array_column($reportData, 'low_stock')), array_column($reportData, 'low_stock'))]['category']; ?> category.</p>
                    </div>
                </div>
            </div>
            <?php elseif ($reportType === 'customers'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Customer Insights</h2>
                </div>
                <div class="card-body">
                    <div class="insights">
                        <p><strong>Customer Growth:</strong> 
                            <?php
                            $firstHalf = array_sum(array_column(array_slice($reportData, 0, ceil(count($reportData) / 2)), 'count'));
                            $secondHalf = array_sum(array_column(array_slice($reportData, ceil(count($reportData) / 2)), 'count'));
                            echo $secondHalf > $firstHalf ? 'Increasing' : ($secondHalf < $firstHalf ? 'Decreasing' : 'Stable');
                            ?>
                        </p>
                        <p><strong>Top Customer Value:</strong> $<?php echo number_format($summary['top_customers'][0]['spent'], 2); ?></p>
                    </div>
                </div>
            </div>
            <?php elseif ($reportType === 'bestsellers'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Bestseller Insights</h2>
                </div>
                <div class="card-body">
                    <div class="insights">
                        <p><strong>Top Author:</strong> <?php echo $reportData[0]['author']; ?></p>
                        <p><strong>Best Revenue Generator:</strong> <?php echo $reportData[array_search(max(array_column($reportData, 'revenue')), array_column($reportData, 'revenue'))]['title']; ?></p>
                        <p><strong>Bestseller Contribution:</strong> <?php echo number_format(($reportData[0]['copies'] / $summary['total_copies_sold']) * 100, 1); ?>% of total sales</p>
                        <p><strong>Action Required:</strong> Stock more copies of top bestsellers and promote related titles.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<!-- Initialize charts based on report type -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($reportType === 'sales'): ?>
    // Sales chart
    const salesCtx = document.getElementById('sales-chart').getContext('2d');
    const salesData = <?php echo json_encode(array_column($reportData, 'amount')); ?>;
    const salesLabels = <?php echo json_encode(array_column($reportData, 'formatted_date')); ?>;
    
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Daily Sales ($)',
                data: salesData,
                backgroundColor: 'rgba(90, 90, 243, 0.2)',
                borderColor: 'rgba(90, 90, 243, 1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
    <?php elseif ($reportType === 'inventory'): ?>
    // Inventory chart
    const inventoryCtx = document.getElementById('inventory-chart').getContext('2d');
    const categories = <?php echo json_encode(array_column($reportData, 'category')); ?>;
    const inStockData = <?php echo json_encode(array_column($reportData, 'in_stock')); ?>;
    const lowStockData = <?php echo json_encode(array_column($reportData, 'low_stock')); ?>;
    const outOfStockData = <?php echo json_encode(array_column($reportData, 'out_of_stock')); ?>;
    
    new Chart(inventoryCtx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [
                {
                    label: 'In Stock',
                    data: inStockData,
                    backgroundColor: 'rgba(76, 175, 80, 0.7)'
                },
                {
                    label: 'Low Stock',
                    data: lowStockData,
                    backgroundColor: 'rgba(255, 152, 0, 0.7)'
                },
                {
                    label: 'Out of Stock',
                    data: outOfStockData,
                    backgroundColor: 'rgba(244, 67, 54, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    });
    <?php elseif ($reportType === 'customers'): ?>
    // Customers chart
    const customersCtx = document.getElementById('customers-chart').getContext('2d');
    const customerDates = <?php echo json_encode(array_column($reportData, 'formatted_date')); ?>;
    const newCustomersData = <?php echo json_encode(array_column($reportData, 'count')); ?>;
    
    new Chart(customersCtx, {
        type: 'bar',
        data: {
            labels: customerDates,
            datasets: [
                {
                    label: 'New Customers',
                    data: newCustomersData,
                    backgroundColor: 'rgba(33, 150, 243, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    <?php elseif ($reportType === 'bestsellers'): ?>
    // Bestsellers chart
    const bestsellersCtx = document.getElementById('bestsellers-chart').getContext('2d');
    const top5Books = <?php echo json_encode(array_slice($reportData, 0, 5)); ?>;
    const bookTitles = top5Books.map(book => book.title);
    const bookCopies = top5Books.map(book => book.copies);
    const colors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ];
    
    new Chart(bestsellersCtx, {
        type: 'doughnut',
        data: {
            labels: bookTitles,
            datasets: [{
                data: bookCopies,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    <?php endif; ?>

});
</script>