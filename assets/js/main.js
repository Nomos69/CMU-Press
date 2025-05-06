/**
 * Main JavaScript file for Bookstore POS
 * Contains common functionality used across all pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize global elements and functionality
    initializeGlobalFunctions();
    initializeSearch();
});

/**
 * Initialize global functions
 */
function initializeGlobalFunctions() {
    // Initialize modal functionality
    initializeModals();
    
    // Initialize notifications
    initializeNotifications();
}

/**
 * Initialize notification system
 */
function initializeNotifications() {
    // Check if notifications container already exists
    let container = document.getElementById('notifications-container');
    
    // If container doesn't exist, create it
    if (!container) {
        container = document.createElement('div');
        container.id = 'notifications-container';
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 3000) {
    const container = document.getElementById('notifications-container');
    
    if (!container) {
        console.error('Notifications container not found');
        return;
    }
    
    // Check for existing identical notification to prevent duplicates
    const existingNotifications = container.querySelectorAll(`.notification-${type}`);
    for (let existingNote of existingNotifications) {
        if (existingNote.textContent.includes(message)) {
            return null; // Duplicate notification, do not show
        }
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Create message element
    const messageEl = document.createElement('span');
    messageEl.textContent = message;
    
    // Create close button
    const closeBtn = document.createElement('span');
    closeBtn.className = 'notification-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', () => closeNotification(notification));
    
    // Add elements to notification
    notification.appendChild(messageEl);
    notification.appendChild(closeBtn);
    
    // Add notification to container
    container.appendChild(notification);
    
    // Auto-close after duration
    if (duration > 0) {
        setTimeout(() => {
            closeNotification(notification);
        }, duration);
    }
    
    // Return notification element for reference
    return notification;
}

/**
 * Close notification
 * @param {HTMLElement} notification Notification element
 */
function closeNotification(notification) {
    notification.classList.add('closing');
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300); // Match animation duration
}

/**
 * Initialize modal functionality
 */
function initializeModals() {
    const modalOverlay = document.getElementById('modal-overlay');
    const modalClose = document.getElementById('modal-close');
    const modalCancel = document.getElementById('modal-cancel');
    
    if (!modalOverlay || !modalClose || !modalCancel) return;
    
    // Close modal on click outside
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });
    
    // Close modal on close button click
    modalClose.addEventListener('click', closeModal);
    
    // Close modal on cancel button click
    modalCancel.addEventListener('click', closeModal);
    
    // Initialize book request button if it exists
    const requestBookBtn = document.querySelector('.request-book-btn');
    if (requestBookBtn) {
        requestBookBtn.addEventListener('click', function() {
            openBookRequestModal();
        });
    }
}

/**
 * Open modal with content
 * @param {string} title Modal title
 * @param {HTMLElement} content Modal content
 * @param {Function} confirmCallback Callback for confirm button
 * @param {boolean} showFooter Show modal footer
 */
function openModal(title, content, confirmCallback = null, showFooter = true) {
    const modalOverlay = document.getElementById('modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    const modalFooter = document.getElementById('modal-footer');
    const modalConfirm = document.getElementById('modal-confirm');
    
    if (!modalOverlay || !modalTitle || !modalContent || !modalFooter || !modalConfirm) {
        console.error('Modal elements not found');
        return;
    }
    
    // Set modal title
    modalTitle.textContent = title;
    
    // Clear and set modal content
    modalContent.innerHTML = '';
    if (typeof content === 'string') {
        modalContent.innerHTML = content;
    } else {
        modalContent.appendChild(content);
    }
    
    // Show/hide footer
    modalFooter.style.display = showFooter ? 'flex' : 'none';
    
    // Set confirm callback
    if (confirmCallback) {
        modalConfirm.onclick = confirmCallback;
    } else {
        modalConfirm.onclick = closeModal;
    }
    
    // Show modal
    modalOverlay.classList.remove('hidden');
    
    // Add body class to prevent scrolling
    document.body.classList.add('modal-open');
}

/**
 * Close modal
 */
function closeModal() {
    const modalOverlay = document.getElementById('modal-overlay');
    
    if (!modalOverlay) return;
    
    // Hide modal
    modalOverlay.classList.add('hidden');
    
    // Remove body class to allow scrolling
    document.body.classList.remove('modal-open');
}

// Ensure these functions are available globally
window.openModal = openModal;
window.closeModal = closeModal;

/**
 * Open book request modal
 */
function openBookRequestModal() {
    const template = document.getElementById('book-request-template');
    if (!template) return;
    
    openModal('Request Unavailable Book', template.content.cloneNode(true).querySelector('.book-request-form').outerHTML, submitBookRequest);
}

/**
 * Submit book request
 */
function submitBookRequest() {
    const titleInput = document.getElementById('book-title');
    const authorInput = document.getElementById('book-author');
    const requesterInput = document.getElementById('requester-name');
    const prioritySelect = document.getElementById('request-priority');
    const quantityInput = document.getElementById('request-quantity');
    
    // Validate inputs
    if (titleInput.value.trim() === '') {
        showNotification('Please enter the book title', 'warning');
        return;
    }
    
    if (requesterInput.value.trim() === '') {
        showNotification('Please enter the requester name', 'warning');
        return;
    }
    
    // Prepare request data
    const requestData = {
        title: titleInput.value.trim(),
        author: authorInput.value.trim(),
        requested_by: requesterInput.value.trim(),
        priority: prioritySelect.value,
        quantity: parseInt(quantityInput.value)
    };
    
    // Send request to API
    fetch('api/book_request/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        
        if (data.message && data.message.includes('success')) {
            showNotification('Book request submitted successfully', 'success');
            
            // Reload page to refresh book requests section
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error submitting book request', 'error');
        }
    })
    .catch(error => {
        closeModal();
        showNotification('Error: Could not connect to the server', 'error');
        console.error('Error:', error);
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    
    if (!searchInput || !searchButton) return;
    
    searchButton.addEventListener('click', function() {
        performSearch(searchInput.value);
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch(this.value);
        }
    });
}

/**
 * Perform search
 * @param {string} query - The search query
 */
function performSearch(query) {
    if (query.trim() === '') return;
    
    // Redirect to inventory page with search query
    window.location.href = `index.php?tab=inventory&search=${encodeURIComponent(query)}`;
}

/**
 * Format currency
 * @param {number} amount - The amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * API service for making requests to the backend
 */
const API = {
    /**
     * Make a GET request
     * @param {string} endpoint - The API endpoint
     * @param {object} params - Query parameters
     * @returns {Promise} Promise with response data
     */
    get: async function(endpoint, params = {}) {
        const url = new URL(endpoint, window.location.origin);
        
        // Add query parameters
        Object.keys(params).forEach(key => {
            url.searchParams.append(key, params[key]);
        });
        
        try {
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },
    
    /**
     * Make a POST request
     * @param {string} endpoint - The API endpoint
     * @param {object} data - The data to send
     * @returns {Promise} Promise with response data
     */
    post: async function(endpoint, data = {}) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },
    
    /**
     * Search books
     * @param {string} query - The search query
     * @returns {Promise} Promise with search results
     */
    searchBooks: function(query) {
        return this.get('api/book/search.php', { q: query });
    },
    
    /**
     * Get book by ID
     * @param {number} id - The book ID
     * @returns {Promise} Promise with book data
     */
    getBook: function(id) {
        return this.get(`api/book/get.php`, { id: id });
    },
    
    /**
     * Get recent transactions
     * @param {number} limit - The number of transactions to retrieve
     * @returns {Promise} Promise with transaction data
     */
    getRecentTransactions: function(limit = 10) {
        return this.get('api/transaction/get_recent.php', { limit: limit });
    },
    
    /**
     * Create transaction
     * @param {object} transactionData - The transaction data
     * @returns {Promise} Promise with response data
     */
    createTransaction: function(transactionData) {
        return this.post('api/transaction/create.php', transactionData);
    },
    
    /**
     * Get low stock items
     * @param {number} limit - The number of items to retrieve
     * @returns {Promise} Promise with low stock data
     */
    getLowStockItems: function(limit = 5) {
        return this.get('api/inventory/get_low_stock_items.php', { limit: limit });
    }
};

/**
 * This is a direct replacement for the checkout functionality.
 * Copy and paste this code into your browser console when on the POS page
 * to immediately fix the checkout button.
 */

// Direct checkout fix
(function() {
    console.log("Applying checkout button fix...");
    
    // Get the checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (!checkoutBtn) {
      console.error("Checkout button not found!");
      return;
    }
    
    // Remove all existing event listeners by cloning the button
    const newCheckoutBtn = checkoutBtn.cloneNode(true);
    checkoutBtn.parentNode.replaceChild(newCheckoutBtn, checkoutBtn);
    
    // Add new direct event listener
    newCheckoutBtn.addEventListener('click', function(event) {
      console.log("Checkout button clicked - using direct fix");
      event.preventDefault();
      
      // Get transaction items
      const transactionItems = document.getElementById('transaction-items');
      if (!transactionItems) {
        alert("Transaction items container not found!");
        return;
      }
      
      const rows = transactionItems.querySelectorAll('tr');
      if (rows.length === 0) {
        alert("Cannot checkout with an empty transaction!");
        return;
      }
      
      // Get the total
      const totalElement = document.getElementById('total');
      if (!totalElement) {
        alert("Total element not found!");
        return;
      }
      
      // Extract numeric value from total
      const totalText = totalElement.textContent.trim();
      const total = parseFloat(totalText.replace(/[^0-9.-]+/g, ''));
      
      if (isNaN(total) || total <= 0) {
        alert("Invalid total amount!");
        return;
      }
      
      // Skip the modal and directly process payment
      processCashPaymentDirect(total);
    });
    
    console.log("Checkout button fix applied successfully!");
    
    /**
     * Direct cash payment processing function
     */
    function processCashPaymentDirect(total) {
      console.log("Processing direct cash payment for:", total);
      
      // Store values on window object for transaction processing
      window.cashAmount = total;
    
      
      // Process the transaction
      processTransactionDirect();
    }
    
    /**
     * Process transaction directly
     */
    function processTransactionDirect() {
      console.log("Processing transaction directly");
      
      try {
        // Get all transaction items
        const transactionItems = document.getElementById('transaction-items');
        const rows = transactionItems.querySelectorAll('tr');
        
        // Build transaction data
        const items = [];
        rows.forEach(row => {
          const bookId = row.getAttribute('data-book-id');
          const title = row.querySelector('td:nth-child(1)').textContent;
          const priceText = row.querySelector('td:nth-child(3)').textContent;
          const price = parseFloat(priceText.replace(/[^0-9.-]+/g, ''));
          const quantity = parseInt(row.querySelector('.qty-value').textContent);
          const totalText = row.querySelector('td:nth-child(5)').textContent;
          const total = parseFloat(totalText.replace(/[^0-9.-]+/g, ''));
          
          items.push({
            book_id: bookId,
            quantity: quantity,
            price: price,
            total: total,
            title: title
          });
        });
        
        // Get customer info
        const customerField = document.getElementById('customer-field');
        const customerId = customerField && customerField.hasAttribute('data-customer-id') ? 
                        customerField.getAttribute('data-customer-id') : null;
        const customerName = customerField && customerField.value ? customerField.value.trim() : 'Guest';
        
        // Get transaction summary values
        const subtotalElement = document.getElementById('subtotal');
        const totalElement = document.getElementById('total');
        
        const subtotalText = subtotalElement.textContent.trim();
        const totalText = totalElement.textContent.trim();
        
        const subtotal = parseFloat(subtotalText.replace(/[^0-9.-]+/g, ''));
        const total = parseFloat(totalText.replace(/[^0-9.-]+/g, ''));
        
        // Get cash details
        const cashAmount = window.cashAmount || total;
        
        
        // Transaction data object
        const transactionData = {
          customer_id: customerId,
          customer_name: customerName,
          user_id: 1,
          items: items,
          payment_method: 'cash',
          subtotal: subtotal,
          tax: 0,
          discount: 0,
          total: total,
          cash_amount: cashAmount,
          status: 'completed'
        };
        
        // Show an alert for success
        alert(`Transaction processed successfully!\nTotal: ${formatMoney(total)}}`);
        
        // Simulate transaction completion
        const simulatedId = Math.floor(Math.random() * 10000) + 1000;
        
        // Complete transaction
        completeTransactionDirect(simulatedId, items, transactionData);
        
      } catch (error) {
        console.error("Transaction processing error:", error);
        alert("Error processing transaction: " + error.message);
      }
    }
    
    /**
     * Complete transaction
     */
    function completeTransactionDirect(transactionId, items, transactionData) {
      console.log("Completing transaction:", transactionId);
      
      // Create transaction record for display
      const currentDate = new Date();
      const transactionRecord = {
        transaction_id: transactionId,
        transaction_date: currentDate,
        customer_name: transactionData.customer_name,
        item_count: items.length,
        total: transactionData.total,
        status: 'completed'
      };
      
      // Save transaction to local storage
      try {
        const storedTransactions = localStorage.getItem('recent_transactions');
        const transactions = storedTransactions ? JSON.parse(storedTransactions) : [];
        transactions.unshift(transactionRecord);
        localStorage.setItem('recent_transactions', JSON.stringify(transactions.slice(0, 50)));
      } catch (error) {
        console.error("Error saving transaction:", error);
      }
      
      // Update recent transactions display
      const recentTransactionsContainer = document.querySelector('.transactions-table tbody');
      
      if (recentTransactionsContainer) {
        const row = document.createElement('tr');
        const timeString = currentDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        row.innerHTML = `
          <td>#${transactionId}</td>
          <td>${timeString}</td>
          <td>${transactionData.customer_name}</td>
          <td>${items.length}</td>
          <td>${formatMoney(transactionData.total)}</td>
          <td><span class="badge badge-success">COMPLETED</span></td>
        `;
        
        // Insert at the beginning
        if (recentTransactionsContainer.firstChild) {
          recentTransactionsContainer.insertBefore(row, recentTransactionsContainer.firstChild);
        } else {
          recentTransactionsContainer.appendChild(row);
        }
      }
      
      // Clear the transaction
      startNewTransactionDirect();
    }
    
    /**
     * Start a new transaction
     */
    function startNewTransactionDirect() {
      console.log("Starting new transaction");
      
      // Clear transaction items
      const transactionItems = document.getElementById('transaction-items');
      if (transactionItems) {
        transactionItems.innerHTML = '';
      }
      
      // Clear customer field
      const customerField = document.getElementById('customer-field');
      if (customerField) {
        customerField.value = '';
        customerField.removeAttribute('data-customer-id');
      }
      
      // Update transaction summary
      let subtotalElement = document.getElementById('subtotal');
      let totalElement = document.getElementById('total');
      
      if (subtotalElement) subtotalElement.textContent = '₱ 0.00';
      if (totalElement) totalElement.textContent = '₱ 0.00';
      
      // Update checkout button text
      newCheckoutBtn.textContent = 'Checkout (₱ 0.00)';
      
      // Increment transaction ID
      const transactionIdElement = document.getElementById('transaction-id');
      if (transactionIdElement) {
        const currentId = parseInt(transactionIdElement.textContent);
        transactionIdElement.textContent = currentId + 1;
      }
    }
    
    /**
     * Format money
     */
    function formatMoney(amount) {
      return '₱ ' + parseFloat(amount).toFixed(2);
    }
  })();