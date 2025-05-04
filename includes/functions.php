<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Format money amount
 * 
 * @param float $amount Amount to format
 * @return string Formatted amount with ₱ symbol
 */
function formatMoney($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format time
 * 
 * @param string $time Time string
 * @param string $format Time format
 * @return string Formatted time
 */
function formatTime($time, $format = 'g:i A') {
    return date($format, strtotime($time));
}

/**
 * Format date and time
 * 
 * @param string $datetime Date and time string
 * @param string $format Date and time format
 * @return string Formatted date and time
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Sanitize input
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Generate random string
 * 
 * @param int $length Length of string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Get transaction status badge HTML
 * 
 * @param string $status Transaction status
 * @return string HTML badge
 */
function getStatusBadge($status) {
    $class = '';
    $label = ucfirst($status);
    
    switch ($status) {
        case 'completed':
            $class = 'success';
            break;
        case 'on_hold':
        case 'on-hold':
            $class = 'warning';
            $label = 'On Hold';
            break;
        case 'cancelled':
            $class = 'danger';
            break;
        default:
            $class = 'info';
            break;
    }
    
    return '<span class="status ' . $class . '">' . $label . '</span>';
}

/**
 * Get priority badge HTML
 * 
 * @param string $priority Priority level
 * @return string HTML badge
 */
function getPriorityBadge($priority) {
    $class = '';
    $label = ucfirst($priority);
    
    switch ($priority) {
        case 'high':
            $class = 'high-priority';
            $label = 'High Priority';
            break;
        case 'medium':
            $class = 'medium-priority';
            break;
        case 'low':
            $class = 'low-priority';
            break;
        default:
            $class = 'medium-priority';
            break;
    }
    
    return '<span class="priority ' . $class . '">' . $label . '</span>';
}

/**
 * Calculate tax amount
 * 
 * @param float $amount Amount to calculate tax on
 * @param float $taxRate Tax rate (default 0.08 = 8%)
 * @return float Tax amount
 */
function calculateTax($amount, $taxRate = 0.08) {
    return $amount * $taxRate;
}

/**
 * Get inventory status label
 * 
 * @param int $quantity Current quantity
 * @param int $threshold Low stock threshold
 * @return string Status label (in_stock, low_stock, out_of_stock)
 */
function getInventoryStatus($quantity, $threshold) {
    if ($quantity <= 0) {
        return 'out_of_stock';
    } else if ($quantity <= $threshold) {
        return 'low_stock';
    } else {
        return 'in_stock';
    }
}

/**
 * Get inventory status badge HTML
 * 
 * @param int $quantity Current quantity
 * @param int $threshold Low stock threshold
 * @return string HTML badge
 */
function getInventoryStatusBadge($quantity, $threshold) {
    $status = getInventoryStatus($quantity, $threshold);
    $class = '';
    $label = '';
    
    switch ($status) {
        case 'in_stock':
            $class = 'success';
            $label = 'In Stock';
            break;
        case 'low_stock':
            $class = 'warning';
            $label = 'Low Stock';
            break;
        case 'out_of_stock':
            $class = 'danger';
            $label = 'Out of Stock';
            break;
    }
    
    return '<span class="inventory-status ' . $class . '">' . $label . '</span>';
}

/**
 * Truncate text
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Check if user is logged in, redirect if not
 * 
 * @param string $redirect URL to redirect to
 * @return void
 */
function requireLogin($redirect = 'login.php') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Check if user is admin, redirect if not
 * 
 * @param string $redirect URL to redirect to
 * @return void
 */
function requireAdmin($redirect = 'index.php') {
    requireLogin();
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Show alert message
 * 
 * @param string $message Message to display
 * @param string $type Alert type (success, danger, warning, info)
 * @return string HTML alert
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}

/**
 * Log error to file
 * 
 * @param string $message Error message
 * @param string $level Error level
 * @return bool True if logged, false otherwise
 */
function logError($message, $level = 'ERROR') {
    $logFile = '../logs/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    
    return file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Get pagination HTML
 * 
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Items per page
 * @param string $url URL pattern with %d for page number
 * @return string HTML pagination
 */
function getPagination($totalItems, $currentPage, $itemsPerPage, $url) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous page link
    if ($currentPage > 1) {
        $html .= '<a href="' . sprintf($url, $currentPage - 1) . '" class="page-prev">&laquo; Previous</a>';
    } else {
        $html .= '<span class="page-prev disabled">&laquo; Previous</span>';
    }
    
    // Page links
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    // Always show first page
    if ($startPage > 1) {
        $html .= '<a href="' . sprintf($url, 1) . '" class="page-num">1</a>';
        if ($startPage > 2) {
            $html .= '<span class="page-ellipsis">...</span>';
        }
    }
    
    // Page numbers
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i === $currentPage) {
            $html .= '<span class="page-num active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . sprintf($url, $i) . '" class="page-num">' . $i . '</a>';
        }
    }
    
    // Always show last page
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<span class="page-ellipsis">...</span>';
        }
        $html .= '<a href="' . sprintf($url, $totalPages) . '" class="page-num">' . $totalPages . '</a>';
    }
    
    // Next page link
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . sprintf($url, $currentPage + 1) . '" class="page-next">Next &raquo;</a>';
    } else {
        $html .= '<span class="page-next disabled">Next &raquo;</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get CSV file contents as array
 * 
 * @param string $filename File path
 * @param bool $hasHeader Whether the CSV has a header row
 * @return array CSV data
 */
function getCSVData($filename, $hasHeader = true) {
    if (!file_exists($filename)) {
        return [];
    }
    
    $rows = [];
    $header = [];
    
    if (($handle = fopen($filename, "r")) !== false) {
        if ($hasHeader) {
            $header = fgetcsv($handle, 1000, ",");
        }
        
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($hasHeader) {
                $rows[] = array_combine($header, $data);
            } else {
                $rows[] = $data;
            }
        }
        
        fclose($handle);
    }
    
    return $rows;
}

/**
 * Export data to CSV file
 * 
 * @param array $data Data to export
 * @param string $filename File path
 * @param array $headers Column headers
 * @return bool True if exported, false otherwise
 */
function exportToCSV($data, $filename, $headers = []) {
    if (($handle = fopen($filename, "w")) !== false) {
        // Write headers
        if (!empty($headers)) {
            fputcsv($handle, $headers);
        } else if (!empty($data) && is_array($data[0])) {
            fputcsv($handle, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        return true;
    }
    
    return false;
}
?>