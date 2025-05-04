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
    
    // Initialize recent transactions buttons
    initializeRecentTransactions();
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
    // No longer needed as only cash payment is available
}

/**
 * Initialize checkout button
 */
function initializeCheckout() {
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (!checkoutBtn) return;
    
    checkoutBtn.addEventListener('click', function() {
        processCheckout();
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
    
    // Receipt buttons
    const receiptButtons = document.querySelectorAll('.receipt-btn');
    receiptButtons.forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.getAttribute('data-id');
            showReceipt(transactionId);
        });
    });
}

/**
 * Search and add item to transaction
 * @param {string} searchTerm Search term
 */
function searchAndAddItem(searchTerm) {
    // Show loading indicator
    showNotification('Searching for items...', 'info');
    
    // In a real application, this would make an API call to search for items
    // For this demo, we'll simulate an API response
    fetch(`api/book/search.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.books && data.books.length > 0) {
                // Add the first book to the transaction
                const book = data.books[0];
                
                addTransactionItem({
                    book_id: book.book_id,
                    title: book.title,
                    author: book.author,
                    price: parseFloat(book.price),
                    quantity: 1,
                    total: parseFloat(book.price)
                });
                
                updateTransactionSummary();
                showNotification(`Added "${book.title}" to transaction`, 'success');
            } else {
                showNotification('No matching items found', 'warning');
            }
        })
        .catch(error => {
            console.error('Error searching for items:', error);
            showNotification('An error occurred while searching', 'error');
        });
}

/**
 * Search customer
 * @param {string} searchTerm Search term
 */
function searchCustomer(searchTerm) {
    // Show loading indicator
    showNotification('Searching for customer...', 'info');
    
    // In a real application, this would make an API call to search for customers
    // For this demo, we'll simulate an API response
    setTimeout(() => {
        // Simulate found customer
        const customerField = document.getElementById('customer-field');
        customerField.value = 'Michael Roberts';
        customerField.setAttribute('data-customer-id', '1');
        
        showNotification('Customer found and added to transaction', 'success');
    }, 500);
}

/**
 * Add transaction item
 * @param {Object} item Item to add
 */
function addTransactionItem(item) {
    const transactionItems = document.getElementById('transaction-items');
    
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
    const price = parseFloat(priceElement.textContent.replace('$', ''));
    const totalElement = row.querySelector('td:nth-child(5)');
    const newTotal = price * newQuantity;
    totalElement.textContent = formatCurrency(newTotal);
    
    updateTransactionSummary();
}

/**
 * Update transaction summary
 */
function updateTransactionSummary() {
    const transactionItems = document.getElementById('transaction-items');
    const rows = transactionItems.querySelectorAll('tr');
    
    let subtotal = 0;
    
    // Calculate subtotal
    rows.forEach(row => {
        const totalElement = row.querySelector('td:nth-child(5)');
        const total = parseFloat(totalElement.textContent.replace('$', ''));
        subtotal += total;
    });
    
    // Calculate tax (8%)
    const tax = subtotal * 0.08;
    
    // Calculate total
    const total = subtotal + tax;
    
    // Update elements
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('tax').textContent = formatCurrency(tax);
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
    
    // Payment method is always cash
    const paymentMethod = 'cash';
    
    // Build transaction data
    const items = [];
    rows.forEach(row => {
        const bookId = row.getAttribute('data-book-id');
        const title = row.querySelector('td:nth-child(1)').textContent;
        const price = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('$', ''));
        const quantity = parseInt(row.querySelector('.qty-value').textContent);
        const total = parseFloat(row.querySelector('td:nth-child(5)').textContent.replace('$', ''));
        
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
    const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('$', ''));
    const tax = parseFloat(document.getElementById('tax').textContent.replace('$', ''));
    const total = parseFloat(document.getElementById('total').textContent.replace('$', ''));
    
    const transactionData = {
        customer_id: customerId,
        user_id: 1, // In a real application, this would be the logged-in user's ID
        items: items,
        payment_method: paymentMethod,
        subtotal: subtotal,
        tax: tax,
        discount: 0, // No discount feature
        total: total,
        status: 'completed'
    };
    
    // Show loading notification
    showNotification('Processing transaction...', 'info');
    
    // In a real application, this would make an API call to process the checkout
    fetch('api/transaction/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(transactionData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.transaction_id) {
            showNotification('Transaction completed successfully', 'success');
            
            // Ask if they want to print receipt
            setTimeout(() => {
                if (confirm('Transaction completed! Would you like to print a receipt?')) {
                    // In a real application, this would open a print dialog
                    showNotification('Printing receipt...', 'info');
                }
                
                // Start new transaction
                startNewTransaction();
                
                // Reload page to refresh stats and transactions
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }, 500);
        } else {
            showNotification('Error processing transaction', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while processing the transaction', 'error');
    });
}

/**
 * Open recent transactions modal
 */
function openRecentTransactionsModal() {
    showNotification('Loading recent transactions...', 'info');
    
    // In a real application, this would make an API call to get recent transactions
    fetch('api/transaction/get_recent.php')
    .then(response => response.json())
    .then(data => {
        if (data.transactions && data.transactions.length > 0) {
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
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.transactions.forEach(transaction => {
                const date = new Date(transaction.transaction_date);
                const dateFormatted = `${date.toLocaleDateString()} ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                
                let actionButton = '';
                if (transaction.status === 'completed') {
                    actionButton = `<button class="btn-link show-receipt-btn" data-id="${transaction.transaction_id}">Receipt</button>`;
                }
                
                transactionsHTML += `
                    <tr>
                        <td>#${transaction.transaction_id}</td>
                        <td>${dateFormatted}</td>
                        <td>${transaction.customer_name || 'Guest'}</td>
                        <td>${transaction.item_count}</td>
                        <td>${parseFloat(transaction.total).toFixed(2)}</td>
                        <td><span class="status ${transaction.status}">${transaction.status}</span></td>
                        <td>${actionButton}</td>
                    </tr>
                `;
            });
            
            transactionsHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            
            openModal('Recent Transactions', transactionsHTML);
            
            // Add event listeners to buttons
            document.querySelectorAll('.show-receipt-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const transactionId = this.getAttribute('data-id');
                    showReceipt(transactionId);
                });
            });
        } else {
            openModal('Recent Transactions', '<p>No recent transactions found.</p>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        openModal('Recent Transactions', '<p>An error occurred while loading transactions.</p>');
    });
}

/**
 * Show receipt
 * @param {string} transactionId Transaction ID
 */
function showReceipt(transactionId) {
    showNotification('Loading receipt...', 'info');
    
    // In a real application, this would make an API call to get the transaction details
    // For this demo, we'll simulate a receipt
    setTimeout(() => {
        const receiptHTML = `
            <div class="receipt">
                <div class="receipt-header">
                    <h3>Bookstore POS</h3>
                    <p>123 Book Lane, Reading, CA 90210</p>
                    <p>Tel: (555) 123-4567</p>
                </div>
                <div class="receipt-details">
                    <p><strong>Transaction #:</strong> ${transactionId}</p>
                    <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Time:</strong> ${new Date().toLocaleTimeString()}</p>
                    <p><strong>Cashier:</strong> Emma Thompson</p>
                    <p><strong>Customer:</strong> Michael Roberts</p>
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
                            <tr>
                                <td>The Midnight Library<br><small>Matt Haig</small></td>
                                <td>1</td>
                                <td>$18.99</td>
                                <td>$18.99</td>
                            </tr>
                            <tr>
                                <td>Klara and the Sun<br><small>Kazuo Ishiguro</small></td>
                                <td>1</td>
                                <td>$24.99</td>
                                <td>$24.99</td>
                            </tr>
                            <tr>
                                <td>Project Hail Mary<br><small>Andy Weir</small></td>
                                <td>1</td>
                                <td>$22.50</td>
                                <td>$22.50</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="receipt-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$66.48</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (8%):</span>
                        <span>$5.32</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$71.80</span>
                    </div>
                </div>
                <div class="receipt-footer">
                    <p>Payment Method: Cash</p>
                    <p>Thank you for your purchase!</p>
                    <p>Please come again</p>
                </div>
            </div>
        `;
        
        openModal(`Receipt for Transaction #${transactionId}`, receiptHTML);
        
        // Add print button to modal footer
        const modalFooter = document.getElementById('modal-footer');
        const printButton = document.createElement('button');
        printButton.className = 'btn-primary';
        printButton.innerHTML = '<i class="fas fa-print"></i> Print Receipt';
        printButton.addEventListener('click', function() {
            printReceipt(receiptHTML);
        });
        
        // Replace the existing buttons
        modalFooter.innerHTML = '';
        modalFooter.appendChild(printButton);
        
        const closeButton = document.createElement('button');
        closeButton.className = 'btn-secondary';
        closeButton.textContent = 'Close';
        closeButton.addEventListener('click', closeModal);
        modalFooter.appendChild(closeButton);
    }, 500);
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
 * @param {number} amount - Amount
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '$' + amount.toFixed(2);
}
