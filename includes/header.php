<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['name'] : '';
$userRole = $isLoggedIn ? $_SESSION['role'] : '';

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Get active tab from URL parameter or default to 'pos'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'pos';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore POS</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ($currentPage === 'login.php' || $currentPage === 'install.php'): ?>
    <link rel="stylesheet" href="assets/css/<?php echo str_replace('.php', '', $currentPage); ?>.css">
    <?php endif; ?>
</head>
<body>
    <?php if ($isLoggedIn && $currentPage !== 'login.php' && $currentPage !== 'install.php'): ?>
    <header>
        <div class="logo">
            <span class="logo-text">CMU Press System</span>
        </div>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search books by title, author, or ISBN...">
            <button id="search-button"><i class="fas fa-search"></i></button>
        </div>
        <div class="user-info">
            <span class="user-name"><?php echo $userName; ?></span>
            <i class="fas fa-user-circle"></i>
            <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt logout-icon"></i></a>
        </div>
    </header>
    <nav>
        <ul>
            <li class="<?php echo $activeTab === 'pos' ? 'active' : ''; ?>">
                <a href="index.php?tab=pos"><i class="fas fa-cash-register"></i> Point of Sale</a>
            </li>
            <li class="<?php echo $activeTab === 'inventory' ? 'active' : ''; ?>">
                <a href="index.php?tab=inventory"><i class="fas fa-book"></i> Inventory</a>
            </li>
            <li class="<?php echo $activeTab === 'book_requests' ? 'active' : ''; ?>">
                <a href="index.php?tab=book_requests"><i class="fas fa-clipboard-list"></i> Book Requests</a>
            </li>
            <li class="<?php echo $activeTab === 'reports' ? 'active' : ''; ?>">
                <a href="index.php?tab=reports"><i class="fas fa-chart-bar"></i> Reports</a>
            </li>
            <li class="<?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                <a href="index.php?tab=settings"><i class="fas fa-cog"></i> Settings</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>