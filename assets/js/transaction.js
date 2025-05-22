/**
 * Transaction JavaScript file for Bookstore POS
 * Handles transaction-related functionality
 */

// Determine the API root path based on the current URL structure
const API_ROOT_PATH = (() => {
    // Get the current path
    const path = window.location.pathname;
    // Check if we're in a subdirectory or not
    if (path.includes('/index.php')) {
        // We're at root level
        return '';
    } else if (path.endsWith('/')) {
        // We're at root level with trailing slash
        return '';
    } else {
        // We might be in a subdirectory
        const pathParts = path.split('/');
        // Remove the last part (file name)
        pathParts.pop();
        return pathParts.join('/') + '/';
    }
})();

console.log('API root path detected as:', API_ROOT_PATH);

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM content loaded, initializing transaction system');
    // Initialize transaction functionality
    setTimeout(() => {
        initializeTransaction();
    }, 100); // Small delay to ensure DOM is fully loaded
});

/**
 * Initialize transaction functionality
 */
function initializeTransaction() {
    console.log('Initializing transaction functionality');
    
    // Initialize item search
    initializeItemSearch();
    
    // Initialize customer search
    initializeCustomerSearch();
    
    // Initialize transaction buttons
    initializeTransactionButtons();
    
    // Initialize payment methods
    initializePaymentMethods();
    
    // Initialize checkout button
    initializeCheckout();
    
    // Initialize recent transactions
    initializeRecentTransactions();
    
    // Load stored transactions
    loadStoredTransactions();
    
    // Initialize inventory search in POS page
    initializeInventorySearch();
    
    // Added receipt button listeners
    addReceiptButtonListeners();
}

/**
 * Initialize item search
 */
function initializeItemSearch() {
    const itemSearchInput = document.getElementById('item-search');
    const addItemBtn = document.getElementById('add-item-btn');
    
    if (!itemSearchInput || !addItemBtn) return;
    
    addItemBtn.addEventListener('click', function() {
        if (itemSearchInput.value.trim() !== '') {
            searchAndAddItem(itemSearchInput.value);
            itemSearchInput.value = '';
        }
    });
    
    itemSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            searchAndAddItem(this.value);
            this.value = '';
        }
    });
}

/**
 * Initialize customer search
 */
function initializeCustomerSearch() {
    const customerField = document.getElementById('customer-field');
    const addCustomerBtn = document.getElementById('add-customer-btn');
    
    if (!customerField || !addCustomerBtn) return;
    
    addCustomerBtn.addEventListener('click', function() {
        if (customerField.value.trim() !== '') {
            searchCustomer(customerField.value);
        }
    });
    
    customerField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            searchCustomer(this.value);
        }
    });
}

/**
 * Initialize transaction buttons
 */
function initializeTransactionButtons() {
    console.log('Initializing transaction buttons');
    const transactionOptions = document.querySelectorAll('.btn-option');
    
    if (transactionOptions.length === 0) {
        console.warn('No transaction option buttons found');
    }
    
    transactionOptions.forEach(option => {
        const optionType = option.getAttribute('data-type');
        console.log('Found transaction button:', optionType);
        
        // Remove any existing event listeners
        option.removeEventListener('click', transactionButtonHandler);
        
        // Add new event listener - using direct function for clarity
        option.addEventListener('click', function(event) {
            console.log('Transaction button clicked:', optionType);
            
            if (optionType === 'new') {
                startNewTransaction();
            }
        });
    });
}

/**
 * Initialize payment methods
 */
function initializePaymentMethods() {
    // Get the cash payment button
    const cashButton = document.querySelector('.payment-btn[data-method="cash"]');
    if (cashButton) {
        // Make sure the Cash button is active
        cashButton.classList.add('active');
        
        // Add click handler for cash payment button
        cashButton.addEventListener('click', function() {
            // Show cash payment modal if needed
            showCashPaymentModal();
        });
    }
}

/**
 * Show cash payment modal for entering cash amount
 */
function showCashPaymentModal() {
    console.log('Opening cash payment modal');
    
    // Check if there are items in the transaction
    const transactionItems = document.getElementById('transaction-items');
    const rows = transactionItems.querySelectorAll('tr');
    
    if (rows.length === 0) {
        showNotification('Cannot proceed with an empty transaction', 'warning');
        return;
    }
    
    // Get the total amount from the transaction
    const totalElement = document.getElementById('total');
    if (!totalElement) {
        console.error('Total element not found');
        showNotification('Error: Could not find transaction total', 'error');
        return;
    }
    
    const totalAmount = parseFloat(totalElement.textContent.replace('₱', ''));
    if (isNaN(totalAmount) || totalAmount <= 0) {
        showNotification('Cannot process a zero-amount transaction', 'warning');
        return;
    }
    
    console.log('Total amount:', totalAmount);
    
    // Create modal content
    const modalContent = `
        <div class="cash-payment-form">
            <div class="form-group">
                <label for="cash-amount">Cash Amount</label>
                <input type="number" id="cash-amount" min="${totalAmount}" step="0.01" value="${totalAmount}" placeholder="Enter cash amount">
            </div>
            <div class="payment-summary">
                <div class="summary-row">
                    <span>Total:</span>
                    <span>${formatCurrency(totalAmount)}</span>
                </div>
                <div class="summary-row change">
                    <span>Change:</span>
                    <span id="change-amount">${formatCurrency(0)}</span>
                </div>
            </div>
        </div>
    `;
    
    // Check if openModal function exists
    if (typeof openModal !== 'function') {
        console.error('openModal function not defined');
        showNotification('Error: Modal system not loaded properly', 'error');
        return;
    }
    
    // Show modal with wrapper to handle any errors
    try {
        openModal('Cash Payment', modalContent, processCashPayment);
        
        // Add event listener for calculating change
        const cashAmountInput = document.getElementById('cash-amount');
        if (cashAmountInput) {
            cashAmountInput.addEventListener('input', function() {
                const cashAmount = parseFloat(this.value) || totalAmount;
                const change = cashAmount - totalAmount;
                const changeElement = document.getElementById('change-amount');
                if (changeElement) {
                    changeElement.textContent = formatCurrency(change >= 0 ? change : 0);
                }
            });
            
            // Focus on input
            cashAmountInput.focus();
            cashAmountInput.select();
        } else {
            console.error('Cash amount input not found after opening modal');
        }
    } catch (error) {
        console.error('Error opening cash payment modal:', error);
        showNotification('Error with checkout system. Please try again.', 'error');
    }
}

/**
 * Process cash payment
 */
function processCashPayment() {
    console.log('Processing cash payment');
    
    try {
        const cashAmountInput = document.getElementById('cash-amount');
        const totalElement = document.getElementById('total');
        
        if (!cashAmountInput || !totalElement) {
            console.error('Cash amount or total element not found');
            showNotification('Error processing payment: Required elements not found', 'error');
            return;
        }
        
        const cashAmount = parseFloat(cashAmountInput.value);
        const totalAmount = parseFloat(totalElement.textContent.replace('₱', ''));
        
        console.log('Cash amount:', cashAmount, 'Total amount:', totalAmount);
        
        // Validate cash amount
        if (isNaN(cashAmount) || cashAmount < totalAmount) {
            showNotification('Cash amount must be greater than or equal to the total', 'warning');
            return;
        }
        
        // Calculate change
        const change = cashAmount - totalAmount;
        
        // Store cash payment details for checkout process
        window.cashAmount = cashAmount;
        window.change = change;
        
        // Close modal
        if (typeof closeModal === 'function') {
            closeModal();
        } else {
            console.error('closeModal function not defined');
            // Try to close modal manually
            const modalOverlay = document.getElementById('modal-overlay');
            if (modalOverlay) {
                modalOverlay.classList.add('hidden');
            }
        }
        
        // Show success notification
        showNotification(`Cash payment accepted. Change: ${formatCurrency(change)}`, 'success');
        
        // Process the transaction
        processTransaction();
    } catch (error) {
        console.error('Error in processCashPayment:', error);
        showNotification('Error processing payment. Please try again.', 'error');
    }
}

/**
 * Process the transaction after payment is confirmed
 */
function processTransaction() {
    console.log('Processing transaction');
    
    try {
        // Get all transaction items
        const transactionItems = document.getElementById('transaction-items');
        if (!transactionItems) {
            console.error('Transaction items container not found');
            showNotification('Error: Could not find transaction items', 'error');
            return;
        }
        
        const rows = transactionItems.querySelectorAll('tr');
        if (rows.length === 0) {
            showNotification('Cannot process an empty transaction', 'warning');
            return;
        }
        
        // Build transaction data
        const items = [];
        rows.forEach(row => {
            const bookId = row.getAttribute('data-book-id');
            const title = row.querySelector('td:nth-child(1)').textContent;
            const price = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('₱', ''));
            const quantity = parseInt(row.querySelector('.qty-value').textContent);
            const total = parseFloat(row.querySelector('td:nth-child(5)').textContent.replace('₱', ''));
            
            items.push({
                book_id: bookId,
                quantity: quantity,
                price: price,
                total: total,
                title: title // Add title for display purposes
            });
        });
        
        // Get customer ID if any
        const customerField = document.getElementById('customer-field');
        const customerId = customerField && customerField.hasAttribute('data-customer-id') ? 
                        customerField.getAttribute('data-customer-id') : null;
        const customerName = customerField && customerField.value ? customerField.value.trim() : 'Guest';
        
        // Get transaction summary values
        const subtotalElement = document.getElementById('subtotal');
        const totalElement = document.getElementById('total');
        
        if (!subtotalElement || !totalElement) {
            console.error('Transaction summary elements not found');
            showNotification('Error: Could not find transaction summary', 'error');
            return;
        }
        
        const subtotal = parseFloat(subtotalElement.textContent.replace('₱', ''));
        const total = parseFloat(totalElement.textContent.replace('₱', ''));
        
        // Get cash details
        const cashAmount = window.cashAmount || total;
        const change = window.change || 0;
        
        // Prepare transaction data and API URL before stock check
        const transactionData = {
            customer_id: customerId,
            customer_name: customerName,
            user_id: 1, 
            items: items,
            payment_method: 'cash',
            subtotal: subtotal,
            tax: 0, // Add tax field with default value of 0
            discount: 0,
            total: total,
            cash_amount: cashAmount,
            change: change,
            status: 'completed'
        };
        const apiUrl = `${API_ROOT_PATH}api/transaction/create.php`;
        const simulatedTransactionId = Math.floor(Math.random() * 10000) + 1000;
        
        // Before sending the transaction, check stock for each book
        const checkStockPromises = items.map(item => {
            return fetch(`${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(item.book_id)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.books && data.books.length > 0) {
                        const book = data.books[0];
                        return { book_id: item.book_id, title: book.title, stock_qty: parseInt(book.stock_qty), requested_qty: item.quantity };
                    } else {
                        return { book_id: item.book_id, title: item.title, stock_qty: 0, requested_qty: item.quantity };
                    }
                })
                .catch(() => ({ book_id: item.book_id, title: item.title, stock_qty: 0, requested_qty: item.quantity }));
        });

        Promise.all(checkStockPromises).then(stockResults => {
            const insufficient = stockResults.find(b => b.stock_qty < b.requested_qty);
            if (insufficient) {
                showNotification(`Cannot proceed: "${insufficient.title}" only has ${insufficient.stock_qty} in stock.`, 'error');
                return;
            }

            // Proceed to send the transaction via API
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(transactionData),
            })
            .then(async response => {
                console.log('API response status:', response.status);
                let data = null;
                try {
                    data = await response.json();
                } catch (err) {
                    console.warn('Could not parse JSON response:', err);
                }
                if (response.status === 201 && data && data.transaction_id) {
                    // Success
                    completeTransaction(data.transaction_id, items, transactionData);
                } else if (response.status === 400 && data && data.message) {
                    // Show error from backend (e.g., insufficient stock)
                    showNotification(data.message, 'error');
                    // Do NOT complete the transaction or use a simulated ID
                } else {
                    // Other errors
                    showNotification('Transaction failed. Please try again.', 'error');
                    // Do NOT complete the transaction or use a simulated ID
                }
            })
            .catch(error => {
                console.error('API error:', error);
                showNotification('Warning: Could not connect to server. Processing transaction offline.', 'warning');
                // Fall back to simulated ID only if the server is unreachable
                completeTransaction(simulatedTransactionId, items, transactionData);
            });
        });
    } catch (error) {
        console.error('Transaction processing error:', error);
        showNotification('Error processing transaction: ' + error.message, 'error');
    }
}

/**
 * Initialize checkout button
 */
function initializeCheckout() {
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (!checkoutBtn) {
        console.warn('Checkout button not found');
        return;
    }
    
    // Remove any existing event listeners
    checkoutBtn.removeEventListener('click', processCheckout);
    
    // Add new event listener
    checkoutBtn.addEventListener('click', processCheckout);
    
    // Update checkout button text
    updateCheckoutButtonText();
}

/**
 * Update checkout button text with total
 */
function updateCheckoutButtonText() {
    const checkoutBtn = document.getElementById('checkout-btn');
    const total = document.getElementById('total');
    
    if (checkoutBtn && total) {
        checkoutBtn.textContent = `Checkout (${total.textContent})`;
    }
}

/**
 * Initialize recent transactions
 */
function initializeRecentTransactions() {
    // Recent transactions view all button
    const viewAllBtn = document.getElementById('view-all-transactions-btn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            openRecentTransactionsModal();
        });
    }
}

/**
 * Add event listeners to receipt buttons
 */
function addReceiptButtonListeners() {
    // Removed receipt functionality
}

/**
 * Search and add item to transaction
 * @param {string} searchTerm Search term
 */
function searchAndAddItem(searchTerm) {
    if (searchTerm.trim() === '') {
        showNotification('Please enter a search term', 'warning');
        return;
    }

    // Show loading indicator
    console.log("Searching for item:", searchTerm);
    showNotification('Searching for items...', 'info');
    
    // Add debugging to console
    console.log("Fetching from:", `${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(searchTerm)}`);
    
    // Direct fetch to ensure we're bypassing any caching or issues with the API wrapper
    fetch(`${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            console.log("API response status:", response.status);
            console.log("API response headers:", [...response.headers.entries()]);
            
            // Check if response is ok (status 200-299)
            if (response.ok) {
                return response.json().catch(error => {
                    console.error("JSON parsing error:", error);
                    throw new Error('Invalid JSON response from server');
                });
            }
            
            // Handle 404 (not found) specially
            if (response.status === 404) {
                console.log("404 response: No books found");
                return { books: [] };
            }
            
            // For any other error, throw an exception
            console.error("Non-OK, Non-404 response:", response.status);
            throw new Error(`API error: ${response.status}`);
        })
        .then(data => {
            console.log("Search response data:", data);
            
            if (data && data.books && data.books.length > 0) {
                // Use the first book from search results
                const book = data.books[0];
                console.log("Found book:", book);
                addBookToTransaction(book);
                return;
            }
            
            // No books found
            console.log("No books found or empty response");
            showNotification(`No items found matching "${searchTerm}"`, 'warning');
        })
        .catch(error => {
            console.error("Search error:", error);
            
            // Try using relative path as fallback
            console.log("Trying alternative URL with relative path...");
            fetch(`${API_ROOT_PATH}../api/book/search.php?q=${encodeURIComponent(searchTerm)}`)
                .then(response => {
                    console.log("Alternative API response status:", response.status);
                    if (response.ok) {
                        return response.json();
                    }
                    throw new Error('Alternative path also failed');
                })
                .then(data => {
                    console.log("Alternative search response data:", data);
                    if (data && data.books && data.books.length > 0) {
                        const book = data.books[0];
                        console.log("Found book via alternative path:", book);
                        addBookToTransaction(book);
                        return;
                    }
                    showNotification(`No items found matching "${searchTerm}"`, 'warning');
                })
                .catch(altError => {
                    console.error("Alternative search also failed:", altError);
                    showNotification('Error searching for items. Please check server connection or try refreshing the page.', 'error');
                });
        });
}

/**
 * Add found book to transaction
 * @param {Object} book Book to add
 */
function addBookToTransaction(book) {
    console.log("Adding book to transaction:", book);
    
    // Validate book object
    if (!book || typeof book !== 'object') {
        console.error("Invalid book object:", book);
        showNotification("Invalid book data received from server", 'error');
        return;
    }
    
    // Check for required fields
    if (!book.book_id || !book.title || !book.price) {
        console.error("Book missing required fields:", book);
        showNotification("Book data incomplete, missing required fields", 'error');
        return;
    }
    
    // Check if book has stock or stock data is missing
    if (book.stock_qty !== undefined && book.stock_qty <= 0) {
        showNotification(`"${book.title}" is out of stock`, 'warning');
        return;
    }
    
    try {
        addTransactionItem({
            book_id: book.book_id,
            title: book.title,
            author: book.author || 'Unknown', // Provide default if missing
            price: parseFloat(book.price) || 0,
            quantity: 1,
            total: parseFloat(book.price) || 0
        });
        
        updateTransactionSummary();
        showNotification(`Added "${book.title}" to transaction`, 'success');
    } catch (error) {
        console.error("Error adding book to transaction:", error);
        showNotification(`Error adding "${book.title}" to transaction: ${error.message}`, 'error');
    }
}

/**
 * Search customer
 * @param {string} searchTerm Search term
 */
function searchCustomer(searchTerm) {
    // Show loading indicator
    showNotification('Searching for customer...', 'info');
    
    // Mock customer database
    const mockCustomers = [
        { customer_id: 1, name: 'Michael Roberts', email: 'michael.roberts@example.com', phone: '555-123-4567' },
        { customer_id: 2, name: 'Sarah Johnson', email: 'sarah.j@example.com', phone: '555-234-5678' },
        { customer_id: 3, name: 'David Williams', email: 'd.williams@example.com', phone: '555-345-6789' },
        { customer_id: 4, name: 'Jennifer Brown', email: 'jennifer.b@example.com', phone: '555-456-7890' },
        { customer_id: 5, name: 'Robert Smith', email: 'robert.smith@example.com', phone: '555-567-8901' }
    ];
    
    // Try using the server API first
    fetch(`${API_ROOT_PATH}api/customer/search.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('API not available');
            }
            return response.json();
        })
        .then(data => {
            if (data.customers && data.customers.length > 0) {
                // Add the first customer to the transaction
                addCustomerToTransaction(data.customers[0]);
            } else {
                showNotification('No matching customers found via API', 'warning');
                // Fall back to mock data
                searchMockCustomers(searchTerm);
            }
        })
        .catch(error => {
            console.log('API error:', error);
            // Fall back to mock data
            searchMockCustomers(searchTerm);
        });
    
    /**
     * Search mock customers database
     * @param {string} term Search term
     */
    function searchMockCustomers(term) {
        // Convert to lowercase for case-insensitive search
        const searchLower = term.toLowerCase();
        
        // Search in names, emails, and phone numbers
        const matchingCustomers = mockCustomers.filter(customer => 
            customer.name.toLowerCase().includes(searchLower) || 
            customer.email.toLowerCase().includes(searchLower) ||
            customer.phone.includes(term) ||
            customer.customer_id.toString() === term // Exact match for ID
        );
        
        if (matchingCustomers.length > 0) {
            // Add the first matching customer
            addCustomerToTransaction(matchingCustomers[0]);
        } else {
            // If the search term might be a new customer name, suggest adding them
            if (searchTerm.trim().split(' ').length >= 2) { // At least first and last name
                if (confirm(`No customer found named "${searchTerm}". Would you like to add them as a new customer?`)) {
                    // Create a new mock customer
                    const newCustomer = {
                        customer_id: Math.floor(Math.random() * 1000) + 100,
                        name: searchTerm.trim(),
                        email: '',
                        phone: ''
                    };
                    addCustomerToTransaction(newCustomer);
                    showNotification(`Added new customer: ${newCustomer.name}`, 'success');
                } else {
                    showNotification('No customer added', 'info');
                }
            } else {
                showNotification('No matching customers found', 'warning');
            }
        }
    }
    
    /**
     * Add found customer to transaction
     * @param {Object} customer Customer to add
     */
    function addCustomerToTransaction(customer) {
        const customerField = document.getElementById('customer-field');
        customerField.value = customer.name;
        customerField.setAttribute('data-customer-id', customer.customer_id);
        
        showNotification(`Customer ${customer.name} added to transaction`, 'success');
    }
}

/**
 * Add transaction item
 * @param {Object} item Item to add
 */
function addTransactionItem(item) {
    console.log("Adding transaction item:", item);
    try {
        const transactionItems = document.getElementById('transaction-items');
        if (!transactionItems) {
            console.error("Transaction items container not found");
            return;
        }
        // Check if item already exists
        const existingRows = transactionItems.querySelectorAll('tr');
        for (let i = 0; i < existingRows.length; i++) {
            const row = existingRows[i];
            const bookId = row.getAttribute('data-book-id');
            if (bookId === item.book_id.toString()) {
                // Check stock before increasing quantity
                fetch(`${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(item.book_id)}`)
                    .then(response => response.json())
                    .then(data => {
                        let stock_qty = 0;
                        if (data && data.books && data.books.length > 0) {
                            stock_qty = parseInt(data.books[0].stock_qty);
                        }
                        const quantityElement = row.querySelector('.qty-value');
                        const currentQuantity = parseInt(quantityElement.textContent);
                        if (currentQuantity + 1 > stock_qty) {
                            showNotification(`Cannot add more. Only ${stock_qty} in stock.`, 'error');
                            return;
                        }
                        const newQuantity = currentQuantity + 1;
                        quantityElement.textContent = newQuantity;
                        // Update total
                        const totalElement = row.querySelector('td:nth-child(5)');
                        const newTotal = item.price * newQuantity;
                        totalElement.textContent = formatCurrency(newTotal);
                        updateTransactionSummary();
                    });
                return;
            }
        }
        // Check stock before adding new row
        fetch(`${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(item.book_id)}`)
            .then(response => response.json())
            .then(data => {
                let stock_qty = 0;
                if (data && data.books && data.books.length > 0) {
                    stock_qty = parseInt(data.books[0].stock_qty);
                }
                if (stock_qty < 1) {
                    showNotification(`Cannot add. Only ${stock_qty} in stock.`, 'error');
                    return;
                }
                // Create new row
                const row = document.createElement('tr');
                row.setAttribute('data-book-id', item.book_id);
                row.innerHTML = `
                    <td>${item.title}</td>
                    <td>${item.author}</td>
                    <td>${formatCurrency(item.price)}</td>
                    <td>
                        <button class="qty-btn qty-minus">-</button>
                        <span class="qty-value">1</span>
                        <button class="qty-btn qty-plus">+</button>
                    </td>
                    <td>${formatCurrency(item.price)}</td>
                    <td>
                        <button class="remove-item-btn"><i class="fas fa-times"></i></button>
                    </td>
                `;
                // Add event listeners to buttons
                const minusBtn = row.querySelector('.qty-minus');
                const plusBtn = row.querySelector('.qty-plus');
                const removeBtn = row.querySelector('.remove-item-btn');
                minusBtn.addEventListener('click', function() {
                    updateItemQuantity(row, -1);
                });
                plusBtn.addEventListener('click', function() {
                    // Check stock before increasing
                    fetch(`${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(item.book_id)}`)
                        .then(response => response.json())
                        .then(data => {
                            let stock_qty = 0;
                            if (data && data.books && data.books.length > 0) {
                                stock_qty = parseInt(data.books[0].stock_qty);
                            }
                            const quantityElement = row.querySelector('.qty-value');
                            const currentQuantity = parseInt(quantityElement.textContent);
                            if (currentQuantity + 1 > stock_qty) {
                                showNotification(`Cannot add more. Only ${stock_qty} in stock.`, 'error');
                                return;
                            }
                            updateItemQuantity(row, 1);
                        });
                });
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    updateTransactionSummary();
                });
                transactionItems.appendChild(row);
                console.log("Added row to transaction items");
                updateTransactionSummary();
            });
    } catch (error) {
        console.error("Error in addTransactionItem:", error);
    }
}

/**
 * Update item quantity
 * @param {HTMLElement} row Table row
 * @param {number} change Quantity change
 */
function updateItemQuantity(row, change) {
    const quantityElement = row.querySelector('.qty-value');
    const currentQuantity = parseInt(quantityElement.textContent);
    let newQuantity = currentQuantity + change;
    // Ensure quantity is at least 1
    if (newQuantity < 1) newQuantity = 1;
    // Check stock before updating
    const bookId = row.getAttribute('data-book-id');
    fetch(`${API_ROOT_PATH}api/book/search.php?q=${encodeURIComponent(bookId)}`)
        .then(response => response.json())
        .then(data => {
            let stock_qty = 0;
            if (data && data.books && data.books.length > 0) {
                stock_qty = parseInt(data.books[0].stock_qty);
            }
            if (newQuantity > stock_qty) {
                showNotification(`Cannot set quantity. Only ${stock_qty} in stock.`, 'error');
                return;
            }
            quantityElement.textContent = newQuantity;
            // Update total
            const priceElement = row.querySelector('td:nth-child(3)');
            const price = parseFloat(priceElement.textContent.replace('₱', ''));
            const totalElement = row.querySelector('td:nth-child(5)');
            const newTotal = price * newQuantity;
            totalElement.textContent = formatCurrency(newTotal);
            updateTransactionSummary();
        });
}

/**
 * Update transaction summary totals
 */
function updateTransactionSummary() {
    let subtotal = 0;
    let total = 0;
    
    // Get all transaction rows
    const rows = document.querySelectorAll('#transaction-items tr');
    
    // Calculate totals
    rows.forEach(row => {
        const itemTotal = parseFloat(row.querySelector('td:nth-child(5)').textContent.replace('₱', ''));
        subtotal += itemTotal;
    });
    
    // Set total equal to subtotal for now (can add tax or discounts here later)
    total = subtotal;
    
    // Update displayed values
    document.getElementById('subtotal').textContent = '₱ ' + subtotal.toFixed(2);
    document.getElementById('total').textContent = '₱ ' + total.toFixed(2);
    
    // Update checkout button text as well
    updateCheckoutButtonText();
}

/**
 * Start new transaction
 */
function startNewTransaction() {
    console.log('Starting new transaction');
    
    try {
        // Clear transaction items
        const transactionItems = document.getElementById('transaction-items');
        if (transactionItems) {
            transactionItems.innerHTML = '';
        } else {
            console.error('Transaction items container not found');
        }
        
        // Clear customer field
        const customerField = document.getElementById('customer-field');
        if (customerField) {
            customerField.value = '';
            customerField.removeAttribute('data-customer-id');
        }
        
        // Reset cash amount and change
        window.cashAmount = null;
        window.change = null;
        
        // Update transaction summary
        updateTransactionSummary();
        
        // Increment transaction ID
        const transactionIdElement = document.getElementById('transaction-id');
        if (transactionIdElement) {
            const currentId = parseInt(transactionIdElement.textContent);
            transactionIdElement.textContent = currentId + 1;
        }
        
        // Show success notification
        showNotification('Started new transaction', 'success');
    } catch (error) {
        console.error('Error starting new transaction:', error);
        showNotification('Error starting new transaction', 'error');
    }
}

/**
 * Process checkout
 */
function processCheckout() {
    console.log('Processing checkout');
    const transactionItems = document.getElementById('transaction-items');
    const rows = transactionItems.querySelectorAll('tr');
    
    if (rows.length === 0) {
        showNotification('Cannot checkout an empty transaction', 'warning');
        return;
    }
    
    // Open cash payment modal
    showCashPaymentModal();
}

/**
 * Complete the transaction
 * @param {number|string} transactionId The transaction ID
 * @param {Array} items Items in the transaction
 * @param {Object} transactionData Full transaction data
 */
function completeTransaction(transactionId, items, transactionData) {
    // Get the current time for the new transaction
    const currentDate = new Date();
    const timeString = currentDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    // Get customer name
    const customerField = document.getElementById('customer-field');
    const customerName = customerField.value.trim() || 'Guest';
    
    // Get items count and total
    const itemsCount = items.length;
    const total = transactionData.total;
    
    // Create a new recent transaction entry
    addRecentTransaction({
        transaction_id: transactionId,
        transaction_date: currentDate,
        customer_name: customerName,
        item_count: itemsCount,
        total: total,
        status: 'completed'
    });
    
    // Update inventory before showing the success message
    if (items && items.length > 0) {
        // First update the inventory
        updateInventoryAfterSale(items)
            .then(() => {
                showNotification('Transaction completed successfully', 'success');
                cleanupAfterTransaction();
            })
            .catch(error => {
                console.error('Error updating inventory:', error);
                showNotification('Transaction completed, but inventory update failed. Please check stock levels.', 'warning');
                cleanupAfterTransaction();
            });
    } else {
        showNotification('Transaction completed successfully', 'success');
        cleanupAfterTransaction();
    }
    
    // Helper function to clean up after transaction
    function cleanupAfterTransaction() {
        setTimeout(() => {
            // Clear cash transaction data
            window.cashAmount = null;
            window.change = null;
            
            // Start new transaction
            startNewTransaction();
        }, 500);
    }
}

/**
 * Update inventory after sale
 * @param {Array} soldItems Items that were sold
 * @returns {Promise} Promise that resolves when all inventory updates are complete
 */
function updateInventoryAfterSale(soldItems) {
    // Check if there are items to update
    if (!soldItems || soldItems.length === 0) {
        console.log('No items to update in inventory');
        return Promise.resolve([]);
    }
    
    console.log('Updating inventory after sale:', soldItems);
    showNotification('Updating inventory...', 'info');
    
    // Validate items have the required properties
    const validItems = soldItems.filter(item => {
        if (!item.book_id) {
            console.error('Item missing book_id:', item);
            return false;
        }
        if (!item.quantity || isNaN(item.quantity) || item.quantity <= 0) {
            console.error('Item has invalid quantity:', item);
            return false;
        }
        return true;
    });
    
    if (validItems.length === 0) {
        console.error('No valid items to update after filtering', soldItems);
        return Promise.resolve([]);
    }
    
    // Get a transaction ID - either real or simulated
    const transactionIdElement = document.getElementById('transaction-id');
    const transactionId = transactionIdElement ? 
        parseInt(transactionIdElement.textContent) : 
        Math.floor(Math.random() * 10000) + 1000;
    
    // Create request data
    const requestData = {
        transaction_id: transactionId,
        items: validItems.map(item => ({
            book_id: item.book_id,
            quantity: item.quantity,
            title: item.title || 'Unknown' // Include title for better logging
        }))
    };
    
    console.log('Sending inventory update request:', requestData);
    
    const apiUrl = `${API_ROOT_PATH}api/inventory/update_after_sale.php`;
    console.log('API URL:', apiUrl);
    
    // Call the new dedicated inventory update API
    return fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Inventory API response status:', response.status);
        console.log('Inventory API response headers:', 
            Array.from(response.headers.entries()).map(([k, v]) => `${k}: ${v}`).join(', '));
        
        if (!response.ok) {
            throw new Error(`Failed to update inventory. Server responded with ${response.status}`);
        }
        return response.json().catch(error => {
            console.error('Error parsing JSON response:', error);
            return response.text().then(text => {
                console.error('Raw response text:', text);
                throw new Error('Invalid JSON response from server');
            });
        });
    })
    .then(data => {
        console.log('Inventory update response data:', data);
        
        if (data.success) {
            showNotification('Inventory updated successfully', 'success');
            
            // Verify the update by logging each item's results
            if (data.results && Array.isArray(data.results)) {
                data.results.forEach(result => {
                    console.log(`Book ${result.book_id} (${result.title}): Stock changed from ${result.previous_stock} to ${result.new_stock}`);
                });
            }
            
            return data.results;
        } else {
            console.warn('Inventory update warnings:', data);
            showNotification('Inventory updated with warnings. Check stock levels.', 'warning');
            return data.results;
        }
    })
    .catch(error => {
        console.error('Error updating inventory:', error);
        showNotification('Error updating inventory. Please check stock levels.', 'error');
        throw error;
    });
}

/**
 * Open recent transactions modal
 */
function openRecentTransactionsModal() {
    showNotification('Loading recent transactions...', 'info');
    
    // Get stored transactions or use empty array
    const storedTransactions = localStorage.getItem('recent_transactions');
    let recentTransactions = storedTransactions ? JSON.parse(storedTransactions) : [];
    
    // Try using the server API first
    fetch(`${API_ROOT_PATH}api/transaction/get_recent.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error('API not available');
            }
            return response.json();
        })
        .then(data => {
            if (data.transactions && data.transactions.length > 0) {
                recentTransactions = data.transactions;
                displayTransactionsModal(recentTransactions);
            } else {
                // Fall back to stored transactions
                displayTransactionsModal(recentTransactions);
            }
        })
        .catch(error => {
            console.log('API error:', error);
            // Fall back to stored transactions
            displayTransactionsModal(recentTransactions);
        });
    
    /**
     * Display transactions in modal
     * @param {Array} transactions Transactions to display
     */
    function displayTransactionsModal(transactions) {
        if (transactions.length > 0) {
            let transactionsHTML = `
                <div class="recent-transactions">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DATE/TIME</th>
                                <th>CUSTOMER</th>
                                <th>ITEMS</th>
                                <th>TOTAL</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            transactions.forEach(transaction => {
                const date = new Date(transaction.transaction_date);
                const dateFormatted = `${date.toLocaleDateString()} ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                
                transactionsHTML += `
                    <tr>
                        <td>#${transaction.transaction_id}</td>
                        <td>${dateFormatted}</td>
                        <td>${transaction.customer_name || 'Guest'}</td>
                        <td>${transaction.item_count}</td>
                        <td>${formatMoney(transaction.total)}</td>
                        <td>${getStatusBadge(transaction.status || 'completed')}</td>
                    </tr>
                `;
            });
            
            transactionsHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            
            openModal('Recent Transactions', transactionsHTML);
        } else {
            openModal('Recent Transactions', '<p>No recent transactions found.</p>');
        }
    }
}

/**
 * Format currency with peso sign
 * @param {number} amount Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    // Convert to number if it's a string
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    // Check if value is a valid number
    if (isNaN(numAmount)) {
        console.warn('Invalid amount passed to formatCurrency:', amount);
        return '₱ 0.00';
    }
    
    return '₱ ' + numAmount.toFixed(2);
}

/**
 * Format money (alias for formatCurrency for backward compatibility)
 * @param {number} amount Amount to format
 * @returns {string} Formatted money string
 */
function formatMoney(amount) {
    return formatCurrency(amount);
}

/**
 * Get status badge
 * @param {string} status Transaction status
 * @returns {string} Status badge HTML
 */
function getStatusBadge(status) {
    const badgeClass = status === 'completed' ? 'badge-success' : 'badge-warning';
    return `<span class="badge ${badgeClass}">${status.toUpperCase()}</span>`;
}

/**
 * Initialize inventory search in POS interface
 */
function initializeInventorySearch() {
    console.log("Initializing inventory search");
    const inventorySearchInput = document.getElementById('inventory-search');
    const searchInventoryBtn = document.getElementById('search-inventory-btn');
    
    if (!inventorySearchInput || !searchInventoryBtn) {
        console.warn("Inventory search elements not found");
        return;
    }
    
    console.log("Found inventory search elements, attaching event listeners");
    
    // Add event listener to search button
    searchInventoryBtn.addEventListener('click', function() {
        console.log("Inventory search button clicked");
        if (inventorySearchInput.value.trim() !== '') {
            performInventorySearch(inventorySearchInput.value.trim());
        } else {
            showNotification('Please enter a search term', 'warning');
        }
    });
    
    // Add event listener for Enter key in search input
    inventorySearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            console.log("Enter key pressed in inventory search");
            if (this.value.trim() !== '') {
                performInventorySearch(this.value.trim());
            } else {
                showNotification('Please enter a search term', 'warning');
            }
        }
    });
}

/**
 * Perform inventory search - redirects to inventory page with search query
 * @param {string} query Search query
 */
function performInventorySearch(query) {
    if (query.trim() === '') {
        showNotification('Please enter a search term', 'warning');
        return;
    }
    
    console.log("Performing inventory search for:", query);
    showNotification(`Searching inventory for "${query}"...`, 'info');
    
    // Redirect to inventory page with search query
    setTimeout(() => {
        window.location.href = `${API_ROOT_PATH}index.php?tab=inventory&search=${encodeURIComponent(query)}`;
    }, 500);
}

/**
 * Load stored transactions from localStorage
 */
function loadStoredTransactions() {
    console.log("Loading stored transactions");
    try {
        const storedTransactions = localStorage.getItem('recent_transactions');
        
        if (!storedTransactions) {
            console.log("No stored transactions found");
            return;
        }
        
        // Parse transactions
        const transactions = JSON.parse(storedTransactions);
        console.log(`Loaded ${transactions.length} stored transactions`);
        
        // Display transactions in recent transactions section
        const recentTransactionsContainer = document.querySelector('.transactions-table tbody');
        
        if (!recentTransactionsContainer) {
            console.warn("Recent transactions container not found");
            return;
        }
        
        // Clear existing rows
        recentTransactionsContainer.innerHTML = '';
        
        // Add up to 5 most recent transactions
        const recentTransactions = transactions.slice(0, 5);
        
        recentTransactions.forEach(transaction => {
            const date = new Date(transaction.transaction_date);
            const timeString = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>#${transaction.transaction_id}</td>
                <td>${timeString}</td>
                <td>${transaction.customer_name || 'Guest'}</td>
                <td>${transaction.item_count}</td>
                <td>${formatMoney(transaction.total)}</td>
                <td>${getStatusBadge(transaction.status || 'completed')}</td>
            `;
            
            recentTransactionsContainer.appendChild(row);
        });
    } catch (error) {
        console.error("Error loading stored transactions:", error);
    }
}

/**
 * Add a transaction to recent transactions
 * @param {Object} transaction Transaction data
 */
function addRecentTransaction(transaction) {
    console.log("Adding recent transaction:", transaction);
    try {
        // Get stored transactions or initialize empty array
        const storedTransactions = localStorage.getItem('recent_transactions');
        const transactions = storedTransactions ? JSON.parse(storedTransactions) : [];
        
        // Add new transaction at the beginning
        transactions.unshift(transaction);
        
        // Keep only the 50 most recent transactions
        const recentTransactions = transactions.slice(0, 50);
        
        // Save back to localStorage
        localStorage.setItem('recent_transactions', JSON.stringify(recentTransactions));
        
        console.log("Transaction saved to localStorage");
    } catch (error) {
        console.error("Error saving transaction to localStorage:", error);
    }
}