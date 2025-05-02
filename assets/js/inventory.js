/**
 * Inventory JavaScript file for Bookstore POS
 * Handles inventory management functionality
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
    
    // Initialize reorder buttons
    initializeReorderButtons();
    
    // Initialize delete book buttons
    initializeDeleteBooks();
    
    // Initialize import/export buttons
    initializeImportExport();
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
 * Initialize reorder buttons
 */
function initializeReorderButtons() {
    const reorderButtons = document.querySelectorAll('.reorder-book-btn, .reorder-btn');
    
    reorderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-id');
            openReorderModal(bookId);
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
            
            if (confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                deleteBook(bookId);
            }
        });
    });
}

/**
 * Initialize import/export buttons
 */
function initializeImportExport() {
    const importBtn = document.getElementById('import-inventory-btn');
    const exportBtn = document.getElementById('export-inventory-btn');
    
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            openImportModal();
        });
    }
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportInventory();
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
    // In a real application, this would fetch the book details from the server
    // For this demo, we'll show a notification
    showNotification('Fetching book details...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        const template = document.getElementById('book-template');
        
        if (!template) return;
        
        openModal('Edit Book', template.content.cloneNode(true).querySelector('.book-form').outerHTML, function() {
            updateBook(bookId);
        });
        
        // In a real application, you would populate the form with the book details
        // For this demo, we'll just set placeholder values
        document.getElementById('book-title').value = 'Sample Book Title';
        document.getElementById('book-author').value = 'Sample Author';
        document.getElementById('book-isbn').value = '9781234567890';
        document.getElementById('book-price').value = '19.99';
        document.getElementById('book-stock').value = '10';
        document.getElementById('book-threshold').value = '5';
    }, 500);
}

/**
 * Open reorder modal
 * @param {string} bookId Book ID
 */
function openReorderModal(bookId) {
    // In a real application, this would fetch the book details from the server
    // For this demo, we'll show a notification
    showNotification('Fetching book details...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        const template = document.getElementById('reorder-template');
        
        if (!template) return;
        
        openModal('Reorder Book', template.content.cloneNode(true).querySelector('.reorder-form').outerHTML, function() {
            processReorder(bookId);
        });
        
        // In a real application, you would populate the form with the book details
        // For this demo, we'll just set placeholder values
        document.getElementById('reorder-book').value = 'Sample Book Title by Sample Author';
        document.getElementById('reorder-current-stock').value = '3';
    }, 500);
}

/**
 * Open import modal
 */
function openImportModal() {
    // Create import form
    const importForm = `
        <div class="import-form">
            <div class="form-group">
                <label for="import-file">Select CSV File*</label>
                <input type="file" id="import-file" accept=".csv" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="import-update-existing" checked>
                    Update existing books
                </label>
            </div>
            <p class="form-note">* Required fields</p>
            <p class="import-instructions">
                CSV file must have the following columns: title, author, isbn, price, stock_qty, low_stock_threshold
            </p>
        </div>
    `;
    
    openModal('Import Inventory', importForm, importInventory);
}

/**
 * Add book
 */
function addBook() {
    const title = document.getElementById('book-title').value;
    const author = document.getElementById('book-author').value;
    const isbn = document.getElementById('book-isbn').value;
    const price = document.getElementById('book-price').value;
    const stock = document.getElementById('book-stock').value;
    const threshold = document.getElementById('book-threshold').value;
    
    // Validate inputs
    if (!title || !author || !price || !stock) {
        showNotification('Please fill in all required fields.', 'warning');
        return;
    }
    
    // Prepare book data
    const bookData = {
        title: title,
        author: author,
        isbn: isbn,
        price: parseFloat(price),
        stock_qty: parseInt(stock),
        low_stock_threshold: parseInt(threshold)
    };
    
    // In a real application, this would make an API call to create the book
    // For this demo, we'll just show a notification
    showNotification('Adding book...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        closeModal();
        showNotification('Book added successfully.', 'success');
        
        // In a real application, you would reload the page or update the UI
        // For this demo, we'll just reload the page after a delay
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }, 1000);
}

/**
 * Update book
 * @param {string} bookId Book ID
 */
function updateBook(bookId) {
    const title = document.getElementById('book-title').value;
    const author = document.getElementById('book-author').value;
    const isbn = document.getElementById('book-isbn').value;
    const price = document.getElementById('book-price').value;
    const stock = document.getElementById('book-stock').value;
    const threshold = document.getElementById('book-threshold').value;
    
    // Validate inputs
    if (!title || !author || !price || !stock) {
        showNotification('Please fill in all required fields.', 'warning');
        return;
    }
    
    // Prepare book data
    const bookData = {
        book_id: bookId,
        title: title,
        author: author,
        isbn: isbn,
        price: parseFloat(price),
        stock_qty: parseInt(stock),
        low_stock_threshold: parseInt(threshold)
    };
    
    // In a real application, this would make an API call to update the book
    // For this demo, we'll just show a notification
    showNotification('Updating book...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        closeModal();
        showNotification('Book updated successfully.', 'success');
        
        // In a real application, you would reload the page or update the UI
        // For this demo, we'll just reload the page after a delay
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }, 1000);
}

/**
 * Delete book
 * @param {string} bookId Book ID
 */
function deleteBook(bookId) {
    // In a real application, this would make an API call to delete the book
    // For this demo, we'll just show a notification
    showNotification('Deleting book...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Book deleted successfully.', 'success');
        
        // In a real application, you would reload the page or update the UI
        // For this demo, we'll just reload the page after a delay
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }, 1000);
}

/**
 * Process reorder
 * @param {string} bookId Book ID
 */
function processReorder(bookId) {
    const quantity = document.getElementById('reorder-quantity').value;
    const notes = document.getElementById('reorder-notes').value;
    
    // Validate inputs
    if (!quantity || parseInt(quantity) <= 0) {
        showNotification('Please enter a valid quantity.', 'warning');
        return;
    }
    
    // Prepare reorder data
    const reorderData = {
        book_id: bookId,
        quantity: parseInt(quantity),
        notes: notes
    };
    
    // In a real application, this would make an API call to process the reorder
    // For this demo, we'll just show a notification
    showNotification('Processing reorder...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        closeModal();
        showNotification('Reorder processed successfully.', 'success');
    }, 1000);
}

/**
 * Import inventory
 */
function importInventory() {
    const importFile = document.getElementById('import-file');
    const updateExisting = document.getElementById('import-update-existing').checked;
    
    // Validate inputs
    if (!importFile.files || importFile.files.length === 0) {
        showNotification('Please select a CSV file.', 'warning');
        return;
    }
    
    // Get the selected file
    const file = importFile.files[0];
    
    // Check file type
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
        showNotification('Please select a valid CSV file.', 'warning');
        return;
    }
    
    // In a real application, this would make an API call to import the inventory
    // For this demo, we'll just show a notification
    showNotification('Importing inventory...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        closeModal();
        showNotification('Inventory imported successfully.', 'success');
        
        // In a real application, you would reload the page or update the UI
        // For this demo, we'll just reload the page after a delay
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }, 2000);
}

/**
 * Export inventory
 */
function exportInventory() {
    // In a real application, this would make an API call to export the inventory
    // For this demo, we'll just show a notification
    showNotification('Exporting inventory...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Inventory exported successfully.', 'success');
        
        // In a real application, you would trigger a file download
        // For this demo, we'll just show a message
        setTimeout(() => {
            alert('In a real application, this would download a CSV file of your inventory.');
        }, 1000);
    }, 1000);
}