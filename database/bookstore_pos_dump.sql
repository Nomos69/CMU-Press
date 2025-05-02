-- ------------------------------------------------------
-- Bookstore POS System Database Dump
-- ------------------------------------------------------

-- Create database
-- CREATE DATABASE IF NOT EXISTS `bookstore_pos`;
-- USE `bookstore_pos`;

-- ------------------------------------------------------
-- Table structure for table `books`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `idx_book_title` (`title`),
  KEY `idx_book_author` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `customers`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `has_loyalty_card` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_customer_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `users`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `transactions`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('completed','on_hold','cancelled') NOT NULL DEFAULT 'completed',
  `payment_method` enum('credit_card','cash','paypal','other') NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_transaction_status` (`status`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `transaction_items`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `transaction_items`;
CREATE TABLE `transaction_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `book_requests`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `book_requests`;
CREATE TABLE `book_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `requested_by` varchar(255) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','ordered','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Insert Sample Data
-- ------------------------------------------------------

-- Sample Books
INSERT INTO `books` (`title`, `author`, `isbn`, `price`, `stock_qty`, `low_stock_threshold`) VALUES
('The Midnight Library', 'Matt Haig', '9780525559474', 18.99, 15, 5),
('Klara and the Sun', 'Kazuo Ishiguro', '9780571364879', 24.99, 12, 3),
('Project Hail Mary', 'Andy Weir', '9780593135204', 22.50, 20, 5),
('The Invisible Life of Addie LaRue', 'V.E. Schwab', '9780765387561', 19.99, 3, 5),
('The House in the Cerulean Sea', 'TJ Klune', '9781250217288', 17.99, 2, 3),
('The Song of Achilles', 'Madeline Miller', '9780062060617', 16.99, 1, 3),
('Dune', 'Frank Herbert', '9780441172719', 21.99, 18, 5),
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 14.99, 25, 5),
('To Kill a Mockingbird', 'Harper Lee', '9780060935467', 15.99, 22, 5),
('1984', 'George Orwell', '9780451524935', 12.99, 17, 5),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 9.99, 14, 3),
('The Hobbit', 'J.R.R. Tolkien', '9780547928227', 14.99, 19, 5),
('The Alchemist', 'Paulo Coelho', '9780062315007', 16.99, 13, 3),
('The Hunger Games', 'Suzanne Collins', '9780439023481', 12.99, 8, 5),
('Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', '9780590353427', 24.99, 7, 5);

-- Sample Users (password hash for 'password123')
INSERT INTO `users` (`username`, `password`, `name`, `role`) VALUES
('admin', '$2y$10$rUGX4XcyMqR1Kfu2n1bKG.VbhUDJ9R6UMoGMeqQMk6g3B.CSuPy3i', 'Admin User', 'admin'),
('emma', '$2y$10$rUGX4XcyMqR1Kfu2n1bKG.VbhUDJ9R6UMoGMeqQMk6g3B.CSuPy3i', 'Emma Thompson', 'staff'),
('john', '$2y$10$rUGX4XcyMqR1Kfu2n1bKG.VbhUDJ9R6UMoGMeqQMk6g3B.CSuPy3i', 'John Davis', 'staff');

-- Sample Customers
INSERT INTO `customers` (`name`, `email`, `phone`, `has_loyalty_card`) VALUES
('Michael Roberts', 'michael.r@example.com', '555-123-4567', 1),
('Sarah Johnson', 'sarah.j@example.com', '555-234-5678', 1),
('David Williams', 'david.w@example.com', '555-345-6789', 0),
('Jennifer Brown', 'jennifer.b@example.com', '555-456-7890', 1),
('Robert Smith', 'robert.s@example.com', '555-567-8901', 0),
('Emily Jones', 'emily.j@example.com', '555-678-9012', 1),
('William Taylor', 'william.t@example.com', '555-789-0123', 0),
('Elizabeth Davis', 'elizabeth.d@example.com', '555-890-1234', 1),
('James Miller', 'james.m@example.com', '555-901-2345', 0),
('Patricia Wilson', 'patricia.w@example.com', '555-012-3456', 1);

-- Sample Book Requests
INSERT INTO `book_requests` (`title`, `author`, `requested_by`, `priority`, `quantity`, `status`) VALUES
('Babel', 'R.F. Kuang', 'Customer Request', 'high', 5, 'pending'),
('Tomorrow, and Tomorrow, and Tomorrow', 'Gabrielle Zevin', 'Customer Request', 'medium', 3, 'pending'),
('The Lincoln Highway', 'Amor Towles', 'Staff Recommendation', 'low', 2, 'ordered'),
('Cloud Cuckoo Land', 'Anthony Doerr', 'Michael Roberts', 'medium', 1, 'pending'),
('The Four Winds', 'Kristin Hannah', 'Sarah Johnson', 'high', 3, 'pending');

-- Sample Transactions
INSERT INTO `transactions` (`transaction_id`, `customer_id`, `user_id`, `status`, `payment_method`, `subtotal`, `tax`, `discount`, `total`, `transaction_date`) VALUES
(5779, 3, 2, 'on_hold', 'paypal', 39.35, 3.15, 0.00, 42.50, '2025-05-01 10:15:00'),
(5780, 2, 2, 'completed', 'cash', 23.14, 1.85, 0.00, 24.99, '2025-05-01 10:32:00'),
(5781, 1, 2, 'completed', 'credit_card', 62.45, 5.30, 0.00, 67.75, '2025-05-01 10:45:00'),
(5782, NULL, 2, 'completed', 'credit_card', 27.77, 2.22, 0.00, 29.99, '2025-05-01 11:15:00'),
(5783, 4, 2, 'completed', 'cash', 46.28, 3.71, 5.00, 44.99, '2025-05-01 11:47:00'),
(5784, 5, 2, 'cancelled', 'credit_card', 18.51, 1.48, 0.00, 19.99, '2025-05-01 12:05:00'),
(5785, 1, 2, 'completed', 'paypal', 55.54, 4.45, 0.00, 59.99, '2025-05-01 13:22:00'),
(5786, NULL, 3, 'completed', 'cash', 34.71, 2.78, 2.50, 34.99, '2025-05-01 14:01:00'),
(5787, 6, 3, 'completed', 'credit_card', 27.77, 2.22, 0.00, 29.99, '2025-05-01 14:45:00'),
(5788, 2, 3, 'on_hold', 'other', 35.17, 2.82, 0.00, 37.99, '2025-05-01 15:12:00');

-- Sample Transaction Items
INSERT INTO `transaction_items` (`transaction_id`, `book_id`, `quantity`, `price_per_unit`, `total_price`) VALUES
(5779, 4, 1, 19.99, 19.99),
(5779, 5, 1, 17.99, 17.99),
(5780, 2, 1, 24.99, 24.99),
(5781, 1, 1, 18.99, 18.99),
(5781, 2, 1, 24.99, 24.99),
(5781, 3, 1, 22.50, 22.50),
(5782, 6, 1, 16.99, 16.99),
(5782, 11, 1, 9.99, 9.99),
(5783, 7, 1, 21.99, 21.99),
(5783, 9, 1, 15.99, 15.99),
(5783, 10, 1, 12.99, 12.99),
(5784, 4, 1, 19.99, 19.99),
(5785, 14, 1, 12.99, 12.99),
(5785, 15, 1, 24.99, 24.99),
(5785, 8, 1, 14.99, 14.99),
(5786, 12, 1, 14.99, 14.99),
(5786, 13, 1, 16.99, 16.99),
(5787, 3, 1, 22.50, 22.50),
(5787, 5, 1, 17.99, 17.99),
(5788, 8, 1, 14.99, 14.99),
(5788, 9, 1, 15.99, 15.99),
(5788, 14, 1, 12.99, 12.99);

-- ------------------------------------------------------
-- End of Database Dump
-- ------------------------------------------------------