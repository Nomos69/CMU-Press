<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in, redirect to landing page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
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
$validTabs = ['pos', 'inventory', 'book_requests', 'settings'];

// If invalid tab, default to POS
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'pos';
}

// Get tab title
$tabTitles = [
    'pos' => 'Point of Sale',
    'inventory' => 'Inventory Management',
    'book_requests' => 'Book Requests',
    'settings' => 'System Settings'
];

$pageTitle = $tabTitles[$activeTab];

// Include header
include_once 'includes/header.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Start main content wrapper
echo '<main>';

// Include appropriate tab content
switch ($activeTab) {
    case 'pos':
        // Load models for POS
        include_once 'models/Book.php';
        include_once 'models/Transaction.php';
        include_once 'models/Customer.php';
        include_once 'pages/pos.php';
        break;
    
    case 'inventory':
        // Load models for inventory
        include_once 'models/Book.php';
        include_once 'pages/inventory.php';
        break;
    
    case 'book_requests':
        // Load models for book requests
        include_once 'models/BookRequest.php';
        include_once 'pages/book_requests.php';
        break;
    
    case 'settings':
        // Load models for settings
        include_once 'models/User.php';
        include_once 'pages/settings.php';
        break;
    
    default:
        // Default to POS
        include_once 'models/Book.php';
        include_once 'models/Transaction.php';
        include_once 'models/Customer.php';
        include_once 'pages/pos.php';
        break;
}

// End main content wrapper
echo '</main>';

// Include footer
include_once 'includes/footer.php';
?>