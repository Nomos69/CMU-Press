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
    // Create notifications container if it doesn't exist
    if (!document.getElementById('notifications-container')) {
        const container = document.createElement('div');
        container.id = 'notifications-container';
        document.body.appendChild(container);
        
        // Add style for notifications container
        const style = document.createElement('style');
        style.textContent = `
            #notifications-container {
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 9999;
                width: 300px;
            }
            
            .notification {
                padding: 12px 15px;
                margin-bottom: 10px;
                border-radius: 4px;
                color: white;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                display: flex;
                justify-content: space-between;
                align-items: center;
                animation: slideIn 0.3s ease-out forwards;
            }
            
            .notification.closing {
                animation: slideOut 0.3s ease-in forwards;
            }
            
            .notification-success {
                background-color: #4caf50;
            }
            
            .notification-error {
                background-color: #f44336;
            }
            
            .notification-warning {
                background-color: #ff9800;
            }
            
            .notification-info {
                background-color: #2196f3;
            }
            
            .notification-close {
                cursor: pointer;
                font-size: 16px;
                margin-left: 10px;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Show notification
 * @param {string} message Notification message
 * @param {string} type Notification type (success, error, warning, info)
 * @param {number} duration Duration in ms (0 for no auto-close)
 */
function showNotification(message, type = 'info', duration = 3000) {
    const container = document.getElementById('notifications-container');
    
    if (!container) {
        console.error('Notifications container not found');
        return;
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