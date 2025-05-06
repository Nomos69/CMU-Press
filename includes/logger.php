<?php
/**
 * Simple logging utility for CMU-Press application
 */
class Logger {
    // Log file path (using absolute path to ensure writability)
    private static $log_file = '../../logs/inventory.log';
    
    /**
     * Log an inventory update
     * 
     * @param int $book_id Book ID
     * @param string $operation Type of operation (increase/decrease)
     * @param int $quantity Quantity changed
     * @param int $before Stock before update
     * @param int $after Stock after update
     * @param string $context Additional context (e.g., transaction ID)
     * @return bool Success or failure
     */
    public static function logInventoryUpdate($book_id, $operation, $quantity, $before, $after, $context = '') {
        // Ensure logs directory exists and is writable
        self::ensureLogDirectoryExists();
        
        // Direct file logging to debug potential issues
        $debug_msg = "[" . date('Y-m-d H:i:s') . "] INVENTORY: Book #{$book_id} {$operation} by {$quantity}. Stock: {$before} -> {$after} ({$context})";
        
        // First try using error_log for immediate output
        error_log($debug_msg);
        
        // Then try writing to our custom log file
        try {
            // Get the absolute path to the log file
            $log_file_path = self::getLogFilePath();
            
            // Attempt to write to the log file
            if (file_put_contents($log_file_path, $debug_msg . "\n", FILE_APPEND) === false) {
                error_log("Logger: Failed to write to inventory log file: {$log_file_path}");
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Logger Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ensure the logs directory exists and is writable
     */
    private static function ensureLogDirectoryExists() {
        $logs_dir = __DIR__ . '/../logs';
        
        // Check if logs directory exists
        if (!is_dir($logs_dir)) {
            // Try to create it
            if (!mkdir($logs_dir, 0755, true)) {
                error_log("Logger: Failed to create logs directory: {$logs_dir}");
            }
        }
        
        // Make sure the directory is writable
        if (!is_writable($logs_dir)) {
            error_log("Logger: Logs directory is not writable: {$logs_dir}");
            // Try to make it writable
            chmod($logs_dir, 0755);
        }
    }
    
    /**
     * Get the absolute path to the log file
     */
    private static function getLogFilePath() {
        return __DIR__ . '/../logs/inventory.log';
    }
    
    /**
     * Log a transaction event
     * 
     * @param int $transaction_id Transaction ID
     * @param string $event Event description
     * @param array $data Additional data to log
     * @return bool Success or failure
     */
    public static function logTransaction($transaction_id, $event, $data = []) {
        $log_file = '../../logs/transactions.log';
        
        // Create timestamp
        $timestamp = date('Y-m-d H:i:s');
        
        // Format log message
        $log_message = "[{$timestamp}] TRANSACTION #{$transaction_id}: {$event}";
        
        // Add data if provided
        if(!empty($data)) {
            $log_message .= " - " . json_encode($data);
        }
        
        // Add new line
        $log_message .= PHP_EOL;
        
        // Ensure logs directory exists
        $log_dir = dirname($log_file);
        if(!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Also log to PHP error log as a backup
        error_log($log_message);
        
        // Write to log file
        return file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}
?> 