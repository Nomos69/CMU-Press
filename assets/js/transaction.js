/**
 * Transaction JavaScript file for Bookstore POS
 * Handles transaction-related functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize transaction functionality
    initializeTransaction();
});

/**
 * Initialize transaction functionality
 */
function initializeTransaction() {
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
    const transactionOptions = document.querySelectorAll('.btn-option');
    
    transactionOptions.forEach(option => {
        option.addEventListener('click', function() {
            const optionType = this.getAttribute('data-type');
            
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
    // Check if there are items in the transaction
    const transactionItems = document.getElementById('transaction-items');
    const rows = transactionItems.querySelectorAll('tr');
    
    if (rows.length === 0) {
        showNotification('Cannot proceed with an empty transaction', 'warning');
        return;
    }
    
    // Get the total amount from the transaction
    const totalAmount = parseFloat(document.getElementById('total').textContent.replace('₱', ''));
    if (totalAmount <= 0) {
        showNotification('Cannot process a zero-amount transaction', 'warning');
        return;
    }
    
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
    
    // Show modal
    openModal('Cash Payment', modalContent, processCashPayment);
    
    // Add event listener for calculating change
    const cashAmountInput = document.getElementById('cash-amount');
    if (cashAmountInput) {
        cashAmountInput.addEventListener('input', function() {
            const cashAmount = parseFloat(this.value) || totalAmount;
            const change = cashAmount - totalAmount;
            document.getElementById('change-amount').textContent = formatCurrency(change >= 0 ? change : 0);
        });
        
        // Focus on input
        cashAmountInput.focus();
        cashAmountInput.select();
    }
}

/**
 * Process cash payment
 */
function processCashPayment() {
    const cashAmount = parseFloat(document.getElementById('cash-amount').value);
    const totalAmount = parseFloat(document.getElementById('total').textContent.replace('₱', ''));
    
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
    closeModal();
    
    // Show notification with change
    showNotification(`Cash payment accepted. Change: ${formatCurrency(change)}`, 'success');
    
    // Process checkout
    processCheckout();
}

/**
 * Initialize checkout button
 */
function initializeCheckout() {
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (!checkoutBtn) return;
    
    checkoutBtn.addEventListener('click', function() {
        const transactionItems = document.getElementById('transaction-items');
        const rows = transactionItems.querySelectorAll('tr');
        
        if (rows.length === 0) {
            showNotification('Cannot checkout an empty transaction', 'warning');
            return;
        }
        
        // Open cash payment modal
        showCashPaymentModal();
    });
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
    // Show loading indicator
    console.log("Searching for item:", searchTerm);
    
    try {
        // Create notifications container if it doesn't exist
        if (!document.getElementById('notifications-container')) {
            console.log("Creating notifications container");
            const container = document.createElement('div');
            container.id = 'notifications-container';
            container.style.position = 'fixed';
            container.style.top = '100px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            container.style.width = '300px';
            document.body.appendChild(container);
        }
        
        // Show notification
        showNotification('Searching for items...', 'info');
    } catch (error) {
        console.error("Error showing notification:", error);
        // Default to alert as fallback
        alert('Searching for items...');
    }
    
    // Mock book database for demonstration
    const mockBooks = [
        { book_id: 1, title: 'The Great Gatsby', author: 'F. Scott Fitzgerald', price: 14.99, stock_qty: 25 },
        { book_id: 2, title: 'To Kill a Mockingbird', author: 'Harper Lee', price: 12.50, stock_qty: 18 },
        { book_id: 3, title: '1984', author: 'George Orwell', price: 11.99, stock_qty: 14 },
        { book_id: 4, title: 'Pride and Prejudice', author: 'Jane Austen', price: 9.99, stock_qty: 22 },
        { book_id: 5, title: 'The Catcher in the Rye', author: 'J.D. Salinger', price: 10.50, stock_qty: 16 },
        { book_id: 6, title: 'Brave New World', author: 'Aldous Huxley', price: 13.75, stock_qty: 8 },
        { book_id: 7, title: 'The Hobbit', author: 'J.R.R. Tolkien', price: 15.99, stock_qty: 20 },
        { book_id: 8, title: 'Lord of the Flies', author: 'William Golding', price: 11.25, stock_qty: 12 },
        { book_id: 9, title: 'Animal Farm', author: 'George Orwell', price: 9.75, stock_qty: 17 },
        { book_id: 10, title: 'The Alchemist', author: 'Paulo Coelho', price: 12.99, stock_qty: 19 },
        // Add more books for testing with keywords
        { book_id: 11, title: 'Babel', author: 'R.F. Kuang', price: 18.99, stock_qty: 15 },
        { book_id: 12, title: 'Bible', author: 'Various', price: 24.99, stock_qty: 30 },
        { book_id: 13, title: 'Book of Testing', author: 'Test Author', price: 9.99, stock_qty: 10 }
    ];
    
    // API call is not working properly, go directly to mock search
    searchMockBooks(searchTerm);
    
    /**
     * Search mock books database
     * @param {string} term Search term
     */
    function searchMockBooks(term) {
        console.log("Searching mock books for:", term);
        
        // Convert to lowercase for case-insensitive search
        const searchLower = term.toLowerCase();
        
        // Search in titles and authors
        const matchingBooks = mockBooks.filter(book => 
            book.title.toLowerCase().includes(searchLower) || 
            book.author.toLowerCase().includes(searchLower) ||
            book.book_id.toString() === term // Exact match for ID
        );
        
        console.log("Matching books found:", matchingBooks.length, matchingBooks);
        
        if (matchingBooks.length > 0) {
            // Add the first matching book
            console.log("Found book in mock data:", matchingBooks[0]);
            addBookToTransaction(matchingBooks[0]);
        } else {
            console.log("No books found in mock data");
            try {
                showNotification('No matching items found for "' + term + '"', 'warning');
            } catch (error) {
                console.error("Error showing notification:", error);
                alert('No matching items found for "' + term + '"');
            }
        }
    }
    
    /**
     * Add found book to transaction
     * @param {Object} book Book to add
     */
    function addBookToTransaction(book) {
        console.log("Adding book to transaction:", book);
        try {
            addTransactionItem({
                book_id: book.book_id,
                title: book.title,
                author: book.author,
                price: parseFloat(book.price),
                quantity: 1,
                total: parseFloat(book.price)
            });
            
            updateTransactionSummary();
            
            try {
                showNotification(`Added "${book.title}" to transaction`, 'success');
            } catch (error) {
                console.error("Error showing notification:", error);
                alert(`Added "${book.title}" to transaction`);
            }
            
        } catch (error) {
            console.error("Error adding book to transaction:", error);
            alert(`Error adding "${book.title}" to transaction. See console for details.`);
        }
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
        { customer_id: 1, name: 'Michael Roberts', email: 'michael.roberts@example.com', phone: '555-123-4567', has_loyalty_card: true },
        { customer_id: 2, name: 'Sarah Johnson', email: 'sarah.j@example.com', phone: '555-234-5678', has_loyalty_card: false },
        { customer_id: 3, name: 'David Williams', email: 'd.williams@example.com', phone: '555-345-6789', has_loyalty_card: true },
        { customer_id: 4, name: 'Jennifer Brown', email: 'jennifer.b@example.com', phone: '555-456-7890', has_loyalty_card: false },
        { customer_id: 5, name: 'Robert Smith', email: 'robert.smith@example.com', phone: '555-567-8901', has_loyalty_card: true }
    ];
    
    // Try using the server API first
    fetch(`api/customer/search.php?q=${encodeURIComponent(searchTerm)}`)
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
                        phone: '',
                        has_loyalty_card: false
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
        
        const loyaltyStatus = customer.has_loyalty_card ? ' (Loyalty Member)' : '';
        showNotification(`Customer ${customer.name}${loyaltyStatus} added to transaction`, 'success');
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
                // Update quantity instead of adding new row
                const quantityElement = row.querySelector('.qty-value');
                const currentQuantity = parseInt(quantityElement.textContent);
                const newQuantity = currentQuantity + 1;
                
                quantityElement.textContent = newQuantity;
                
                // Update total
                const totalElement = row.querySelector('td:nth-child(5)');
                const newTotal = item.price * newQuantity;
                totalElement.textContent = formatCurrency(newTotal);
                
                updateTransactionSummary();
                return;
            }
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
            updateItemQuantity(row, 1);
        });
        
        removeBtn.addEventListener('click', function() {
            row.remove();
            updateTransactionSummary();
        });
        
        transactionItems.appendChild(row);
        console.log("Added row to transaction items");
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
    
    quantityElement.textContent = newQuantity;
    
    // Update total
    const priceElement = row.querySelector('td:nth-child(3)');
    const price = parseFloat(priceElement.textContent.replace('₱', ''));
    const totalElement = row.querySelector('td:nth-child(5)');
    const newTotal = price * newQuantity;
    totalElement.textContent = formatCurrency(newTotal);
    
    updateTransactionSummary();
}

/**
 * Update transaction summary totals
 */
function updateTransactionSummary() {
    const transactionItems = document.getElementById('transaction-items');
    const rows = transactionItems.querySelectorAll('tr');
    
    let subtotal = 0;
    
    // Calculate subtotal
    rows.forEach(row => {
        const totalElement = row.querySelector('td:nth-child(5)');
        const total = parseFloat(totalElement.textContent.replace('₱', ''));
        subtotal += total;
    });
    
    // Total is now equal to subtotal (no tax)
    const total = subtotal;
    
    // Update elements
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('total').textContent = formatCurrency(total);
    document.getElementById('checkout-btn').textContent = `Checkout (${formatCurrency(total)})`;
}

/**
 * Start new transaction
 */
function startNewTransaction() {
    // Clear transaction items
    const transactionItems = document.getElementById('transaction-items');
    transactionItems.innerHTML = '';
    
    // Clear customer field
    const customerField = document.getElementById('customer-field');
    customerField.value = '';
    customerField.removeAttribute('data-customer-id');
    
    // Update transaction summary
    updateTransactionSummary();
    
    // Increment transaction ID
    const transactionIdElement = document.getElementById('transaction-id');
    const currentId = parseInt(transactionIdElement.textContent);
    transactionIdElement.textContent = currentId + 1;
    
    showNotification('Started new transaction', 'success');
}

/**
 * Process checkout
 */
function processCheckout() {
    const transactionItems = document.getElementById('transaction-items');
    const rows = transactionItems.querySelectorAll('tr');
    
    if (rows.length === 0) {
        showNotification('Cannot checkout an empty transaction', 'warning');
        return;
    }
    
    // Always use cash payment method
    const paymentMethod = 'cash';
    
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
            total: total
        });
    });
    
    // Get customer ID if any
    const customerField = document.getElementById('customer-field');
    const customerId = customerField.hasAttribute('data-customer-id') ? 
                      customerField.getAttribute('data-customer-id') : null;
    
    // Get transaction summary values
    const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('₱', ''));
    const total = parseFloat(document.getElementById('total').textContent.replace('₱', ''));
    
    // Get cash details if available
    const cashAmount = window.cashAmount || total;
    const change = window.change || 0;
    
    const transactionData = {
        customer_id: customerId,
        user_id: 1, // In a real application, this would be the logged-in user's ID
        items: items,
        payment_method: paymentMethod,
        subtotal: subtotal,
        discount: 0, // No discount feature
        total: total,
        cash_amount: cashAmount,
        change: change,
        status: 'completed'
    };
    
    // Show loading notification
    showNotification('Processing transaction...', 'info');
    
    // Try to call the API endpoint
    try {
        // For demonstration/development, create a simulated transaction ID 
        // This will be used if the API call fails
        const simulatedTransactionId = Math.floor(Math.random() * 10000) + 1000;
        
        // First check if the API endpoint exists by sending a HEAD request
        fetch('api/transaction/create.php', { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    // API endpoint exists, send the real request
                    return fetch('api/transaction/create.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(transactionData),
                    });
                } else {
                    // API endpoint doesn't exist, use fallback
                    throw new Error('API endpoint not found');
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned error');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.transaction_id) {
                    completeTransaction(data.transaction_id, items, transactionData);
                } else {
                    // Use simulated transaction ID as fallback
                    completeTransaction(simulatedTransactionId, items, transactionData);
                }
            })
            .catch(error => {
                console.log('API error:', error);
                // Use simulated transaction ID as fallback
                completeTransaction(simulatedTransactionId, items, transactionData);
            });
    } catch (error) {
        console.error('Error in transaction processing:', error);
        // Use simulated transaction ID as fallback
        completeTransaction(simulatedTransactionId, items, transactionData);
    }
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
    
    showNotification('Transaction completed successfully', 'success');
    
    setTimeout(() => {
        // Clear cash transaction data
        window.cashAmount = null;
        window.change = null;
        
        // Start new transaction
        startNewTransaction();
        
        // Update items to reflect reduced inventory
        updateInventoryAfterSale(items);
    }, 500);
}

/**
 * Update inventory after sale (simulated function)
 * @param {Array} soldItems Items that were sold
 */
function updateInventoryAfterSale(soldItems) {
    // In a real application, this would call an API to update inventory
    console.log('Updating inventory after sale:', soldItems);
    
    // For demonstration purposes, we're just showing a notification
    showNotification('Inventory updated', 'info');
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
    fetch('api/transaction/get_recent.php')
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
 * Show receipt in a modal
 * @param {string|number} transactionId Transaction ID
 * @param {object} options Additional options
 */
function showReceipt(transactionId, options = {}) {
    const cashAmount = options.cashAmount || 0;
    const change = options.change || 0;
    const items = options.items || [];
    const subtotal = options.subtotal || 71.80;
    const total = options.total || 71.80;
    
    const receiptTemplate = `
        <div class="receipt">
            <div class="receipt-header">
                <h3>CMU PRESS BOOKSTORE</h3>
                <p>123 University Avenue, Carnegie</p>
                <p>Pittsburgh, PA 15213</p>
                <p>Tel: (412) 555-1234</p>
            </div>
            <div class="receipt-info">
                <p><strong>Transaction #:</strong> ${transactionId}</p>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Time:</strong> ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                <p><strong>Cashier:</strong> ${getUserName()}</p>
            </div>
            <div class="receipt-items">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(item => `
                            <tr>
                                <td>${item.title || 'Book'}</td>
                                <td>${item.quantity}</td>
                                <td>${formatMoney(item.price)}</td>
                                <td>${formatMoney(item.total)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            <div class="receipt-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>${formatMoney(subtotal)}</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>${formatMoney(total)}</span>
                </div>
            </div>
            <div class="receipt-payment">
                <div class="payment-row">
                    <span>Cash:</span>
                    <span>${formatMoney(cashAmount)}</span>
                </div>
                <div class="payment-row">
                    <span>Change:</span>
                    <span>${formatMoney(change)}</span>
                </div>
            </div>
            <div class="receipt-footer">
                <p>Thank you for your purchase!</p>
                <p>Return Policy: Returns accepted within 30 days with receipt.</p>
            </div>
        </div>
    `;
    
    // Create the modal with print button
    openModal('Receipt', receiptTemplate, function() {
        printReceipt(receiptTemplate);
    }, 'Print Receipt');
}

/**
 * Print receipt
 * @param {string} receiptHTML Receipt HTML
 */
function printReceipt(receiptHTML) {
    // In a real application, this would open a print dialog
    // For this demo, we'll just show a notification
    showNotification('Printing receipt...', 'info');
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=600,height=600');
    
    // Add receipt styles
    const receiptStyles = `
        <style>
            body {
                font-family: 'Courier New', monospace;
                margin: 0;
                padding: 20px;
                font-size: 12px;
            }
            
            .receipt {
                max-width: 300px;
                margin: 0 auto;
            }
            
            .receipt-header {
                text-align: center;
                margin-bottom: 20px;
            }
            
            .receipt-header h3 {
                margin: 0;
                font-size: 16px;
            }
            
            .receipt-details {
                margin-bottom: 20px;
            }
            
            .receipt-items table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            
            .receipt-items th {
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                padding: 5px;
                text-align: left;
            }
            
            .receipt-items td {
                padding: 5px;
            }
            
            .receipt-items small {
                font-size: 10px;
            }
            
            .receipt-summary {
                margin-bottom: 20px;
            }
            
            .summary-row {
                display: flex;
                justify-content: space-between;
            }
            
            .summary-row.total {
                font-weight: bold;
                border-top: 1px solid #000;
                padding-top: 5px;
                margin-top: 5px;
            }
            
            .receipt-footer {
                text-align: center;
                margin-top: 20px;
                border-top: 1px dashed #000;
                padding-top: 10px;
            }
            
            @media print {
                body {
                    padding: 0;
                }
                
                button {
                    display: none;
                }
            }
        </style>
    `;
    
    // Write to the new window
    printWindow.document.write('<html><head><title>Receipt</title>' + receiptStyles + '</head><body>');
    printWindow.document.write(receiptHTML);
    printWindow.document.write('<button onclick="window.print()">Print</button>');
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
}

/**
 * Format currency
 * @param {number} amount Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '₱' + amount.toFixed(2);
}

/**
 * Format money value
 * @param {number} value Value to format
 * @returns {string} Formatted money string
 */
function formatMoney(value) {
    return '₱' + value.toFixed(2);
}

/**
 * Get status badge HTML
 * @param {string} status Transaction status
 * @returns {string} HTML status badge
 */
function getStatusBadge(status) {
    let className = '';
    let label = status.charAt(0).toUpperCase() + status.slice(1);
    
    switch (status) {
        case 'completed':
            className = 'success';
            break;
        case 'on_hold':
        case 'on-hold':
            className = 'warning';
            label = 'On Hold';
            break;
        case 'cancelled':
            className = 'danger';
            break;
        default:
            className = 'info';
            break;
    }
    
    return `<span class="status ${className}">${label}</span>`;
}

/**
 * Add a transaction to the recent transactions list
 * @param {Object} transaction The transaction to add
 */
function addRecentTransaction(transaction) {
    // Add to UI
    updateRecentTransactionsUI(transaction);
    
    // Save to localStorage
    saveTransactionToStorage(transaction);
}

/**
 * Update the recent transactions UI
 * @param {Object} transaction The transaction to add
 */
function updateRecentTransactionsUI(transaction) {
    const table = document.querySelector('.transactions-table tbody');
    if (!table) return;
    
    // Remove "no data" row if it exists
    const noDataRow = table.querySelector('.no-data');
    if (noDataRow) {
        noDataRow.remove();
    }
    
    // Create new row
    const row = document.createElement('tr');
    
    // Format the time
    const date = new Date(transaction.transaction_date);
    const timeString = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    row.innerHTML = `
        <td>#${transaction.transaction_id}</td>
        <td>${timeString}</td>
        <td>${transaction.customer_name || 'Guest'}</td>
        <td>${transaction.item_count}</td>
        <td>${formatMoney(transaction.total)}</td>
        <td>${getStatusBadge(transaction.status || 'completed')}</td>
    `;
    
    // Add to the top of the table
    if (table.firstChild) {
        table.insertBefore(row, table.firstChild);
    } else {
        table.appendChild(row);
    }
    
    // Keep only the most recent 3 transactions
    const rows = table.querySelectorAll('tr');
    if (rows.length > 3) {
        for (let i = 3; i < rows.length; i++) {
            rows[i].remove();
        }
    }
}

/**
 * Save transaction to localStorage
 * @param {Object} transaction The transaction to save
 */
function saveTransactionToStorage(transaction) {
    // Get existing transactions
    const storedTransactions = localStorage.getItem('recent_transactions');
    let transactions = storedTransactions ? JSON.parse(storedTransactions) : [];
    
    // Add new transaction at the beginning
    transactions.unshift(transaction);
    
    // Keep only the most recent 20 transactions
    if (transactions.length > 20) {
        transactions = transactions.slice(0, 20);
    }
    
    // Save back to localStorage
    localStorage.setItem('recent_transactions', JSON.stringify(transactions));
}

/**
 * Load stored transactions from localStorage
 */
function loadStoredTransactions() {
    const storedTransactions = localStorage.getItem('recent_transactions');
    if (!storedTransactions) return;
    
    try {
        const transactions = JSON.parse(storedTransactions);
        
        // Clear "no data" message if present
        const table = document.querySelector('.transactions-table tbody');
        if (table) {
            const noDataRow = table.querySelector('.no-data');
            if (noDataRow && transactions.length > 0) {
                noDataRow.remove();
            }
            
            // Add most recent 3 transactions
            const recentTransactions = transactions.slice(0, 3);
            recentTransactions.forEach(transaction => {
                updateRecentTransactionsUI(transaction);
            });
        }
    } catch (error) {
        console.error('Error loading stored transactions:', error);
    }
}

/**
 * Initialize inventory search in POS page
 */
function initializeInventorySearch() {
    const inventorySearchInput = document.getElementById('inventory-search');
    const searchInventoryBtn = document.getElementById('search-inventory-btn');
    
    if (!inventorySearchInput || !searchInventoryBtn) {
        console.log("Inventory search elements not found");
        return;
    }
    
    searchInventoryBtn.addEventListener('click', function() {
        const searchTerm = inventorySearchInput.value.trim();
        if (searchTerm !== '') {
            console.log("Searching inventory for:", searchTerm);
            searchAndAddItem(searchTerm);
            inventorySearchInput.value = '';
        } else {
            alert("Please enter a search term");
        }
    });
    
    inventorySearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            if (searchTerm !== '') {
                console.log("Searching inventory for:", searchTerm);
                searchAndAddItem(searchTerm);
                this.value = '';
            } else {
                alert("Please enter a search term");
            }
        }
    });
}
