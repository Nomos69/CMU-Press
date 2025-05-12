<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMU Press - Book Publishing</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/img/logo.png" type="image/png">
</head>
<body>
    <header class="landing-header">
        <div class="logo">
            <a href="landing.php">
                <img src="assets/img/logo.png" alt="CMU Press Logo" class="logo-image">
            </a>
        </div>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search books by title, author...">
            <button id="search-button"><i class="fas fa-search"></i></button>
        </div>
        <div class="admin-login">
            <a href="login.php" class="btn-admin">Admin Login</a>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Discover Your Next Favorite Book</h1>
            <p>Explore our carefully curated collection of books across various genres. From bestsellers to hidden gems, find the perfect read for every occasion.</p>
            <a href="#collection" class="btn-primary">Browse Collection</a>
        </div>
        <div class="hero-image">
            <img src="assets/img/book-stack.png" alt="Stack of books" onerror="this.src='assets/img/books/placeholder.jpg'">
        </div>
    </section>

    <section id="collection" class="book-collection">
        <h2>Our Book Collection</h2>
        
        <div class="book-grid">
            <!-- Book 1 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book1.jpg" alt="Data Structure and Algorithm in JAVA" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status in-stock">In Stock</span>
                </div>
                <div class="book-details">
                    <h3>Data Structure and Algorithm in JAVA</h3>
                    <p class="author">Robert Lafore</p>
                    <p class="description">A comprehensive guide to data structures and algorithms using the Java programming language. Perfect for students and professionals looking to enhance their programming skills.</p>
                    <p class="price">₱950</p>
                </div>
            </div>
            
            <!-- Book 2 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book2.jpg" alt="Discrete Mathematics and its Application" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status low-stock">Low Stock</span>
                </div>
                <div class="book-details">
                    <h3>Discrete Mathematics and its Application</h3>
                    <p class="author">Kenneth H Rosen</p>
                    <p class="description">This market-leading text is known for its comprehensive coverage, careful and correct mathematics, outstanding exercises, and self-contained chapters.</p>
                    <p class="price">₱1,200</p>
                </div>
            </div>

            <!-- Book 3 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book3.jpg" alt="Fundamental of Data Analytics" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status in-stock">In Stock</span>
                </div>
                <div class="book-details">
                    <h3>Fundamental of Data Analytics</h3>
                    <p class="author">Russel Dawson</p>
                    <p class="description">Learn the core concepts and techniques of data analytics. This book provides a solid foundation for understanding how to extract insights from data and make data-driven decisions.</p>
                    <p class="price">₱850</p>
                </div>
            </div>

            <!-- Book 4 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book4.jpg" alt="Information System Modeling" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status out-of-stock">Out of Stock</span>
                </div>
                <div class="book-details">
                    <h3>Information System Modeling</h3>
                    <p class="author">Kent Levi Bonifacio</p>
                    <p class="description">A practical guide to modeling information systems. This book covers essential techniques for designing, implementing, and managing information systems in various contexts.</p>
                    <p class="price">₱1,100</p>
                </div>
            </div>
        </div>

        <div class="book-grid">
            <!-- Additional books here - second row -->
            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book5.jpg" alt="Mein Kampf" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status in-stock">In Stock</span>
                </div>
                <div class="book-details">
                    <h3>Mein Kampf</h3>
                    <p class="author">Adolf Hitler</p>
                    <p class="description">A historical document providing insight into the ideology that led to one of the darkest periods in human history. This edition includes critical commentary and historical context.</p>
                    <p class="price">₱1,050</p>
                </div>
            </div>

            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book6.jpg" alt="48 Laws of Power" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status in-stock">In Stock</span>
                </div>
                <div class="book-details">
                    <h3>48 Laws of Power</h3>
                    <p class="author">Robert Greene</p>
                    <p class="description">Drawing from the philosophies of Machiavelli, Sun Tzu, and Carl Von Clausewitz, this book distills 3,000 years of the history of power into 48 essential laws.</p>
                    <p class="price">₱780</p>
                </div>
            </div>

            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book7.jpg" alt="Art of War" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status in-stock">In Stock</span>
                </div>
                <div class="book-details">
                    <h3>Art of War</h3>
                    <p class="author">Sun Tzu</p>
                    <p class="description">An ancient Chinese military treatise dating from the 5th century BC. The work contains a detailed explanation of military strategies and tactics still relevant today.</p>
                    <p class="price">₱920</p>
                </div>
            </div>

            <div class="book-card">
                <div class="book-image">
                    <img src="assets/img/books/book8.jpg" alt="Computer Architecture and Application" onerror="this.src='assets/img/books/placeholder.jpg'">
                    <span class="stock-status in-stock">In Stock</span>
                </div>
                <div class="book-details">
                    <h3>Computer Architecture and Application</h3>
                    <p class="author">Shuangbang Paul Wang</p>
                    <p class="description">A comprehensive exploration of computer architecture principles and their practical applications. Ideal for computer science students and professionals in the field.</p>
                    <p class="price">₱1,350</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>CMU Press</h3>
                <p>Your source for quality books and publications.</p>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: info@cmupress.edu</p>
                <p>Phone: (555) 123-4567</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#collection">Browse Books</a></li>
                    <li><a href="login.php">Admin Login</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> CMU Press. All rights reserved.
        </div>
    </footer>

    <script src="assets/js/landing.js"></script>
</body>
</html> 