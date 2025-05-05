-- ------------------------------------------------------
-- Fixed Bookstore POS System Database Dump
-- Changes:
-- 1. Removed has_loyalty_card from customers table
-- 2. Books table already has college field
-- 3. Fixed table dropping order to handle foreign key constraints
-- ------------------------------------------------------

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables in correct order (avoiding foreign key constraint issues)
DROP TABLE IF EXISTS `transaction_items`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `book_requests`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------
-- Table structure for table `books` (includes college field)
-- ------------------------------------------------------
CREATE TABLE `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `college` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `idx_book_title` (`title`),
  KEY `idx_book_author` (`author`),
  KEY `idx_book_college` (`college`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `customers` (without loyalty card)
-- ------------------------------------------------------
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_customer_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------
-- Table structure for table `users`
-- ------------------------------------------------------
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

-- Sample Books with college information
INSERT INTO `books` (`title`, `author`, `isbn`, `price`, `stock_qty`, `low_stock_threshold`, `college`) VALUES
('The Midnight Library', 'Matt Haig', '9780525559474', 18.99, 15, 5, 'College of Arts and Sciences'),
('Klara and the Sun', 'Kazuo Ishiguro', '9780571364879', 24.99, 12, 3, 'College of Arts and Sciences'),
('Project Hail Mary', 'Andy Weir', '9780593135204', 22.50, 20, 5, 'College of Engineering'),
('The Invisible Life of Addie LaRue', 'V.E. Schwab', '9780765387561', 19.99, 3, 5, 'College of Arts and Sciences'),
('The House in the Cerulean Sea', 'TJ Klune', '9781250217288', 17.99, 2, 3, 'College of Human Ecology'),
('The Song of Achilles', 'Madeline Miller', '9780062060617', 16.99, 1, 3, 'College of Arts and Sciences'),
('Dune', 'Frank Herbert', '9780441172719', 21.99, 18, 5, 'College of Information Sciences And Computing'),
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 14.99, 25, 5, 'College of Arts and Sciences'),
('To Kill a Mockingbird', 'Harper Lee', '9780060935467', 15.99, 22, 5, 'College of Education'),
('1984', 'George Orwell', '9780451524935', 12.99, 17, 5, 'College of Arts and Sciences'),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 9.99, 14, 3, 'College of Arts and Sciences'),
('The Hobbit', 'J.R.R. Tolkien', '9780547928227', 14.99, 19, 5, 'College of Arts and Sciences'),
('The Alchemist', 'Paulo Coelho', '9780062315007', 16.99, 13, 3, 'College of Business and Management'),
('The Hunger Games', 'Suzanne Collins', '9780439023481', 12.99, 8, 5, 'College of Arts and Sciences');

-- Sample Users (password hash for 'password123')
INSERT INTO `users` (`username`, `password`, `name`, `role`) VALUES
('admin', '$2a$10$r8toSCFdngcnfh0iHeLUeOW5w09JMQ2p7zikZUA/oohQhcje48sey', 'Admin User', 'admin'),
('emma', '$2y$10$rUGX4XcyMqR1Kfu2n1bKG.VbhUDJ9R6UMoGMeqQMk6g3B.CSuPy3i', 'Emma Thompson', 'staff'),
('john', '$2y$10$rUGX4XcyMqR1Kfu2n1bKG.VbhUDJ9R6UMoGMeqQMk6g3B.CSuPy3i', 'John Davis', 'staff');

-- Sample Customers (without loyalty card field)
INSERT INTO `customers` (`name`, `email`, `phone`) VALUES
('Michael Roberts', 'michael.r@example.com', '555-123-4567'),
('Sarah Johnson', 'sarah.j@example.com', '555-234-5678'),
('David Williams', 'david.w@example.com', '555-345-6789'),
('Jennifer Brown', 'jennifer.b@example.com', '555-456-7890'),
('Robert Smith', 'robert.s@example.com', '555-567-8901'),
('Emily Jones', 'emily.j@example.com', '555-678-9012'),
('William Taylor', 'william.t@example.com', '555-789-0123'),
('Elizabeth Davis', 'elizabeth.d@example.com', '555-890-1234'),
('James Miller', 'james.m@example.com', '555-901-2345'),
('Patricia Wilson', 'patricia.w@example.com', '555-012-3456');

-- Sample Book Requests
INSERT INTO `book_requests` (`title`, `author`, `requested_by`, `priority`, `quantity`, `status`) VALUES
('Babel', 'R.F. Kuang', 'Customer Request', 'high', 5, 'pending'),
('Tomorrow, and Tomorrow, and Tomorrow', 'Gabrielle Zevin', 'Customer Request', 'medium', 3, 'pending'),
('The Lincoln Highway', 'Amor Towles', 'Staff Recommendation', 'low', 2, 'pending'),
('Cloud Cuckoo Land', 'Anthony Doerr', 'Michael Roberts', 'medium', 1, 'pending'),
('The Four Winds', 'Kristin Hannah', 'Sarah Johnson', 'high', 3, 'pending');

-- Sample Transactions (remain unchanged)
-- Note: The customer_id values refer to the auto-increment IDs from the customers table
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

-- Sample Transaction Items (remain unchanged)
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
-- Useful Queries for College-Based Book Management
-- ------------------------------------------------------

-- Count books by college
SELECT college, COUNT(*) as book_count 
FROM books 
GROUP BY college 
ORDER BY book_count DESC;

-- Total inventory value by college
SELECT college, 
       COUNT(*) as total_books, 
       SUM(price * stock_qty) as inventory_value 
FROM books 
GROUP BY college 
ORDER BY inventory_value DESC;

-- Books with low stock by college
SELECT college, title, author, stock_qty, low_stock_threshold 
FROM books 
WHERE stock_qty <= low_stock_threshold 
ORDER BY college, stock_qty ASC;

-- Sales by college (books sold)
SELECT b.college, 
       COUNT(ti.item_id) as items_sold,
       SUM(ti.quantity) as total_books_sold,
       SUM(ti.total_price) as total_revenue
FROM transaction_items ti
JOIN books b ON ti.book_id = b.book_id
JOIN transactions t ON ti.transaction_id = t.transaction_id
WHERE t.status = 'completed'
GROUP BY b.college
ORDER BY total_revenue DESC;

-- ------------------------------------------------------
-- End of Fixed Database Dump
-- ------------------------------------------------------