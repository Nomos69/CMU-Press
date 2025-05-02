<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get current user details
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];

// Get active tab from URL parameter or default to 'pos'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'pos';

// Define valid tabs
$validTabs = ['pos', 'inventory', 'book_requests', 'reports', 'settings'];

// If invalid tab, default to POS
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'pos';
}

// Get tab title
$tabTitles = [
    'pos' => 'Point of Sale',
    'inventory' => 'Inventory Management',
    'book_requests' => 'Book Requests',
    'reports' => 'Reports',
    'settings' => 'Settings'
];

$pageTitle = $tabTitles[$activeTab];

// Include header
include_once 'includes/header.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Include appropriate tab content
switch ($activeTab) {
    case 'pos':
        // Load Book model for POS
        require_once 'models/Book.php';
        require_once 'models/Transaction.php';
        require_once 'models/TransactionItem.php';
        require_once 'models/BookRequest.php';
        include_once 'pages/pos.php';
        break;
    
    case 'inventory':
        // Load Book model for inventory
        require_once 'models/Book.php';
        include_once 'pages/inventory.php';
        break;
    
    case 'book_requests':
        // Load BookRequest model
        require_once 'models/BookRequest.php';
        require_once 'models/Book.php';
        include_once 'pages/book_requests.php';
        break;
    
    case 'reports':
        // Load models for reports
        require_once 'models/Transaction.php';
        require_once 'models/TransactionItem.php';
        require_once 'models/Book.php';
        require_once 'models/Customer.php';
        include_once 'pages/reports.php';
        break;
    
    case 'settings':
        // Load User model for settings
        require_once 'models/User.php';
        include_once 'pages/settings.php';
        break;
    
    default:
        // Default to POS
        require_once 'models/Book.php';
        require_once 'models/Transaction.php';
        require_once 'models/TransactionItem.php';
        require_once 'models/BookRequest.php';
        include_once 'pages/pos.php';
        break;
}

// Include footer
include_once 'includes/footer.php';
?>