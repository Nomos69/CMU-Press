/**
 * Enhanced Inventory JavaScript for Bookstore POS
 * Handles inventory management functionality with improved UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize inventory functionality
    initializeInventory();
});

/**
 * Initialize inventory functionality
 */
function initializeInventory() {
    // Initialize add book button
    initializeAddBook();
    
    // Initialize edit book buttons
    initializeEditBooks();
    
    // Initialize manual inventory update
    initializeManualInventory();
    
    // Initialize inventory search
    initializeInventorySearch();
    
    // Initialize bulk update button
    initializeBulkUpdate();
    
    // Initialize quick actions
    initializeQuickActions();
}

/**
 * Initialize add book button
 */
function initializeAddBook() {
    const addBookBtn = document.getElementById('add-book-btn');
    
    if (!addBookBtn) return;
    
    addBookBtn.addEventListener('click', function() {
        openAddBookModal();
    });
}

/**
 * Initialize edit book buttons
 */
function initializeEditBooks() {
    const editButtons = document.querySelectorAll('.edit-book-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-id');
            openEditBookModal(bookId);
        });
    });
}

/**
 * Initialize delete book buttons
 */
function initializeDeleteBooks() {
    const deleteButtons = document.querySelectorAll('.delete-book-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-id');
            
            // Enhanced confirmation dialog
            if (confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                deleteBook(bookId);
            }
        });
    });
}

/**
 * Initialize manual inventory update functionality
 */
function initializeManualInventory() {
    // Add stock quantity display without edit controls
    const stockCells = document.querySelectorAll('.inventory-table tbody td:nth-child(6)');
    stockCells.forEach(cell => {
        const currentStock = parseInt(cell.textContent);
        
        // Replace text with simple stock display (no control buttons)
        cell.innerHTML = `
            <div class="stock-display">
                <span class="stock-value">${currentStock}</span>
            </div>
        `;
    });
}

/**
 * Initialize inventory search
 */
function initializeInventorySearch() {
    const searchForm = document.querySelector('.search-inventory form');
    const searchInput = document.querySelector('.search-inventory input[name="search"]');
    const searchButton = document.querySelector('.search-inventory button');
    
    if (!searchInput || !searchButton) {
        console.warn("Inventory search elements not found");
        return;
    }
    
    // Add event listener for the search button
    searchButton.addEventListener('click', function(e) {
        if (!searchForm) {
            e.preventDefault();
            performInventorySearch(searchInput.value.trim());
        }
    });
    
    // Add event listener for Enter key in search input
    searchInput.addEventListener('keypress', function(e) {
        if (!searchForm && e.key === 'Enter') {
            e.preventDefault();
            performInventorySearch(this.value.trim());
        }
    });
    
    // Add clear search button if there's a search value
    if (searchInput.value.trim() !== '') {
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'clear-search-btn';
        clearButton.innerHTML = '<i class="fas fa-times"></i>';
        clearButton.title = 'Clear search';
        
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            if (searchForm) {
                searchForm.submit();
            } else {
                window.location.href = 'index.php?tab=inventory';
            }
        });
        
        searchInput.parentNode.insertBefore(clearButton, searchInput.nextSibling);
    }
}

/**
 * Perform inventory search
 * @param {string} query Search query
 */
function performInventorySearch(query) {
    if (query.trim() === '') {
        showNotification('Please enter a search term', 'warning');
        return;
    }
    
    // Show loading notification
    showNotification('Searching inventory...', 'info');
    
    // Redirect to inventory page with search query
    window.location.href = `index.php?tab=inventory&search=${encodeURIComponent(query)}`;
}

/**
 * Initialize bulk update functionality
 */
function initializeBulkUpdate() {
    const bulkUpdateBtn = document.getElementById('bulk-update-btn');
    
    if (!bulkUpdateBtn) return;
    
    bulkUpdateBtn.addEventListener('click', function() {
        openBulkUpdateModal();
    });
}

/**
 * Initialize quick actions
 */
function initializeQuickActions() {
    const manageCategoriesBtn = document.getElementById('manage-categories-btn');
    const importBtn = document.getElementById('import-inventory-btn');
    const exportBtn = document.getElementById('export-inventory-btn');
    
    if (manageCategoriesBtn) {
        manageCategoriesBtn.addEventListener('click', function() {
            // In a real app, we would open a categories management modal
            showNotification('Categories management feature coming soon!', 'info');
        });
    }
    
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            // Create a hidden file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.csv,.xlsx';
            fileInput.style.display = 'none';
            
            // Add to DOM
            document.body.appendChild(fileInput);
            
            // Trigger click
            fileInput.click();
            
            // Handle file selection
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    showNotification(`Importing inventory from ${file.name}...`, 'info');
                    
                    // In a real app, we would process the file
                    setTimeout(() => {
                        showNotification('Import completed successfully!', 'success');
                    }, 1500);
                }
                
                // Clean up
                document.body.removeChild(fileInput);
            });
        });
    }
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            showNotification('Preparing inventory export...', 'info');
            
            // In a real app, we would generate and download a file
            setTimeout(() => {
                // Create a sample CSV download
                const csvContent = "data:text/csv;charset=utf-8,Book ID,Title,Author,ISBN,Price,Stock,Low Stock Threshold\n1,Sample Book,Sample Author,1234567890,19.99,10,5";
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "inventory_export_" + new Date().toISOString().slice(0, 10) + ".csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showNotification('Export completed successfully!', 'success');
            }, 1000);
        });
    }
}

/**
 * Open add book modal
 */
function openAddBookModal() {
    const template = document.getElementById('book-template');
    
    if (!template) return;
    
    openModal('Add New Book', template.content.cloneNode(true).querySelector('.book-form').outerHTML, addBook);
}

/**
 * Open edit book modal
 * @param {string} bookId Book ID
 */
function openEditBookModal(bookId) {
    showNotification('Fetching book details...', 'info');
    
    // Get book details from the API
    fetch(`api/book/get.php?id=${bookId}`)
        .then(response => response.json())
        .then(book => {
            const template = document.getElementById('book-template');
            
            if (!template) return;
            
            openModal('Edit Book', template.content.cloneNode(true).querySelector('.book-form').outerHTML, function() {
                updateBook(bookId);
            });
            
            // Populate form with book details
            document.getElementById('book-title').value = book.title;
            document.getElementById('book-author').value = book.author;
            document.getElementById('book-isbn').value = book.isbn || '';
            document.getElementById('book-price').value = book.price;
            document.getElementById('book-stock').value = book.stock_qty;
            document.getElementById('book-threshold').value = book.low_stock_threshold;
            
            // Set college if it exists, otherwise set to empty value
            const collegeSelect = document.getElementById('book-college');
            collegeSelect.value = book.college || '';
        })
        .catch(error => {
            console.error('Error fetching book details:', error);
            showNotification('Error fetching book details. Please try again.', 'error');
        });
}

/**
 * Open bulk update modal
 */
function openBulkUpdateModal() {
    // Create modal HTML
    const modalContent = `
        <div class="bulk-update-form">
            <div class="form-group">
                <label for="bulk-action">Action</label>
                <select id="bulk-action">
                    <option value="increase">Increase Stock</option>
                    <option value="decrease">Decrease Stock</option>
                    <option value="set">Set Stock To</option>
                </select>
            </div>
            <div class="form-group">
                <label for="bulk-value">Value</label>
                <input type="number" id="bulk-value" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="bulk-criteria">Apply To</label>
                <select id="bulk-criteria">
                    <option value="all">All Books</option>
                    <option value="low_stock">Low Stock Books</option>
                    <option value="out_of_stock">Out of Stock Books</option>
                </select>
            </div>
        </div>
    `;
    
    // Open modal
    openModal('Bulk Update Stock', modalContent, processBulkUpdate);
}

/**
 * Add book
 */
function addBook() {
    // Validate form
    const title = document.getElementById('book-title').value.trim();
    const author = document.getElementById('book-author').value.trim();
    const isbn = document.getElementById('book-isbn').value.trim();
    const price = parseFloat(document.getElementById('book-price').value);
    const stock = parseInt(document.getElementById('book-stock').value);
    const threshold = parseInt(document.getElementById('book-threshold').value);
    const college = document.getElementById('book-college').value.trim() || null; // Handle empty college value

    // Validate required fields
    if (!title || !author || isNaN(price) || isNaN(stock)) {
        showNotification('Please fill in all required fields correctly', 'error');
        return;
    }

    // Create book data object
    const bookData = {
        title: title,
        author: author,
        isbn: isbn || null, // Send null if empty
        price: price,
        stock_qty: stock,
        low_stock_threshold: threshold || 5,
        college: college
    };

    // Send data to API
    fetch('api/book/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            showNotification('Book added successfully!', 'success');
            closeModal();
            
            // Refresh inventory table after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error adding book', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while adding the book', 'error');
    });
}

/**
 * Update book
 * @param {string} bookId Book ID
 */
function updateBook(bookId) {
    // Validate form
    const title = document.getElementById('book-title').value.trim();
    const author = document.getElementById('book-author').value.trim();
    const isbn = document.getElementById('book-isbn').value.trim();
    const price = parseFloat(document.getElementById('book-price').value);
    const stock = parseInt(document.getElementById('book-stock').value);
    const threshold = parseInt(document.getElementById('book-threshold').value);
    const college = document.getElementById('book-college').value.trim() || null; // Handle empty college value

    // Validate required fields
    if (!title || !author || isNaN(price) || isNaN(stock)) {
        showNotification('Please fill in all required fields correctly', 'error');
        return;
    }

    // Create book data object
    const bookData = {
        book_id: bookId,
        title: title,
        author: author,
        isbn: isbn || null, // Send null if empty
        price: price,
        stock_qty: stock,
        low_stock_threshold: threshold || 5,
        college: college
    };

    // Send data to API
    fetch('api/book/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            showNotification('Book updated successfully!', 'success');
            closeModal();
            
            // Refresh inventory table after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error updating book', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating the book', 'error');
    });
}

/**
 * Delete book
 * @param {string} bookId Book ID
 */
function deleteBook(bookId) {
    // Show loading notification
    showNotification('Deleting book...', 'info');
    
    // First try with DELETE method
    fetch(`api/book/delete.php?id=${bookId}`, {
        method: 'DELETE'
    })
    .then(response => {
        if (!response.ok && response.status !== 0) {
            // If DELETE fails with an actual error (not CORS), try POST as fallback
            return fetch(`api/book/delete.php?id=${bookId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${bookId}`
            });
        }
        return response;
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            showNotification('Book deleted successfully.', 'success');
            
            // Remove the book from the table
            const bookRow = document.querySelector(`tr[data-id="${bookId}"]`);
            if (bookRow) {
                bookRow.remove();
            }
            
            // Update inventory statistics - in a real app you might want to fetch fresh stats
            updateInventoryUI();
        } else {
            showNotification(data.message || 'Failed to delete book.', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting book:', error);
        showNotification('An error occurred while deleting the book. Please try again.', 'error');
    });
}

/**
 * Process bulk update of book stock
 */
function processBulkUpdate() {
    // Get form values
    const action = document.getElementById('bulk-action').value;
    const value = parseInt(document.getElementById('bulk-value').value);
    const criteria = document.getElementById('bulk-criteria').value;
    
    // Validate input
    if (isNaN(value) || value <= 0) {
        showNotification('Please enter a valid quantity.', 'error');
        return;
    }
    
    // Get books based on criteria
    let bookRows;
    if (criteria === 'all') {
        bookRows = document.querySelectorAll('.inventory-table tbody tr');
    } else if (criteria === 'low_stock') {
        bookRows = document.querySelectorAll('.inventory-table tbody tr .status-low-stock').closest('tr');
    } else if (criteria === 'out_of_stock') {
        bookRows = document.querySelectorAll('.inventory-table tbody tr .status-out-of-stock').closest('tr');
    }
    
    // Process each book
    const updatePromises = [];
    bookRows.forEach(row => {
        const bookId = row.getAttribute('data-id');
        const stockCell = row.querySelector('td:nth-child(5)');
        const currentStock = parseInt(stockCell.querySelector('.stock-value').textContent);
        
        let newStock;
        if (action === 'increase') {
            newStock = currentStock + value;
        } else if (action === 'decrease') {
            newStock = Math.max(0, currentStock - value);
        } else if (action === 'set') {
            newStock = value;
        }
        
        // Add update promise
        updatePromises.push(
            fetch('api/book/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    book_id: bookId,
                    stock_qty: newStock
                })
            })
        );
    });
    
    // Show loading notification
    showNotification(`Updating ${bookRows.length} books...`, 'info');
    
    // Wait for all updates to complete
    Promise.all(updatePromises)
        .then(responses => {
            showNotification('Bulk update completed successfully!', 'success');
            closeModal();
            
            // Refresh page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Error during bulk update:', error);
            showNotification('An error occurred during bulk update.', 'error');
        });
}

/**
 * Update book stock by increment/decrement
 * This function is no longer actively used as stock editing buttons have been removed
 * Kept for API compatibility
 * @param {string} bookId Book ID
 * @param {number} change Stock change amount (positive or negative)
 */
function updateBookStock(bookId, change) {
    // Get current stock
    const bookRow = document.querySelector(`tr[data-id="${bookId}"]`);
    const stockValueElem = bookRow.querySelector('.stock-value');
    const currentStock = parseInt(stockValueElem.textContent);
    
    // Calculate new stock (ensure it doesn't go below 0)
    const newStock = Math.max(0, currentStock + change);
    
    // If no change, do nothing
    if (newStock === currentStock) return;
    
    // Update UI immediately for better UX
    stockValueElem.textContent = newStock;
    
    // Update book status cell
    updateBookStatusCell(bookRow, newStock);
    
    // Send update to server
    fetch('api/book/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: bookId,
            stock_qty: newStock
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            // Show a brief success notification
            showNotification(`Stock updated to ${newStock}`, 'success', 1500);
        } else {
            // Revert UI changes on error
            stockValueElem.textContent = currentStock;
            updateBookStatusCell(bookRow, currentStock);
            showNotification(data.message || 'Failed to update stock.', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        // Revert UI changes on error
        stockValueElem.textContent = currentStock;
        updateBookStatusCell(bookRow, currentStock);
        showNotification('An error occurred while updating the stock.', 'error');
    });
}

/**
 * Set book stock to specific value
 * This function is no longer actively used as stock editing buttons have been removed
 * Kept for API compatibility
 * @param {string} bookId Book ID
 * @param {number} newStock New stock value
 */
function setBookStock(bookId, newStock) {
    // Get current stock
    const bookRow = document.querySelector(`tr[data-id="${bookId}"]`);
    const stockValueElem = bookRow.querySelector('.stock-value');
    const currentStock = parseInt(stockValueElem.textContent);
    
    // If no change, just update UI
    if (newStock === currentStock) {
        stockValueElem.textContent = currentStock;
        return;
    }
    
    // Update UI immediately for better UX
    stockValueElem.textContent = newStock;
    
    // Update book status cell
    updateBookStatusCell(bookRow, newStock);
    
    // Send update to server
    fetch('api/book/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: bookId,
            stock_qty: newStock
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('successfully')) {
            // Show a brief success notification
            showNotification(`Stock set to ${newStock}`, 'success', 1500);
        } else {
            // Revert UI changes on error
            stockValueElem.textContent = currentStock;
            updateBookStatusCell(bookRow, currentStock);
            showNotification(data.message || 'Failed to update stock.', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        // Revert UI changes on error
        stockValueElem.textContent = currentStock;
        updateBookStatusCell(bookRow, currentStock);
        showNotification('An error occurred while updating the stock.', 'error');
    });
}

/**
 * Update book status cell based on stock quantity
 * @param {HTMLElement} bookRow Book table row
 * @param {number} stockQty Stock quantity
 */
function updateBookStatusCell(bookRow, stockQty) {
    const statusCell = bookRow.querySelector('td:nth-child(6)');
    const threshold = parseInt(bookRow.getAttribute('data-threshold') || 5);
    
    if (stockQty <= 0) {
        statusCell.innerHTML = '<span class="status status-out-of-stock">Out of Stock</span>';
    } else if (stockQty <= threshold) {
        statusCell.innerHTML = '<span class="status status-low-stock">Low Stock</span>';
    } else {
        statusCell.innerHTML = '<span class="status status-in-stock">In Stock</span>';
    }
}

/**
 * Update inventory UI after changes
 */
function updateInventoryUI() {
    // In a real app, you would refresh the stats from the server
    // For this demo, we'll just update the UI based on the current table

    // Count books by status
    const inStockCount = document.querySelectorAll('.status-in-stock').length;
    const lowStockCount = document.querySelectorAll('.status-low-stock').length;
    const outOfStockCount = document.querySelectorAll('.status-out-of-stock').length;
    
    // Update stats display
    const inStockElem = document.querySelector('.inventory-stats .stat-box:nth-child(1) .stat-value');
    const lowStockElem = document.querySelector('.inventory-stats .stat-box:nth-child(2) .stat-value');
    const outOfStockElem = document.querySelector('.inventory-stats .stat-box:nth-child(3) .stat-value');
    
    if (inStockElem) inStockElem.textContent = inStockCount;
    if (lowStockElem) lowStockElem.textContent = lowStockCount;
    if (outOfStockElem) outOfStockElem.textContent = outOfStockCount;
}

/**
 * Show notification
 * @param {string} message Message to display
 * @param {string} type Notification type (success, error, info, warning)
 * @param {number} duration Duration in ms
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification container if it doesn't exist
    let container = document.getElementById('notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notifications-container';
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        ${message}
        <span class="notification-close">&times;</span>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Add close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
        notification.classList.add('closing');
        setTimeout(() => {
            container.removeChild(notification);
        }, 300);
    });
    
    // Auto remove after duration
    setTimeout(() => {
        if (container.contains(notification)) {
            notification.classList.add('closing');
            setTimeout(() => {
                if (container.contains(notification)) {
                    container.removeChild(notification);
                }
            }, 300);
        }
    }, duration);
}

// Note: Using openModal and closeModal functions from main.js
// The duplicate functions were removed to fix modal button functionality