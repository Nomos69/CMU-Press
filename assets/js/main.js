/**
 * Main JavaScript file for Bookstore POS
 * Contains common functionality used across all pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize global elements and functionality
    initializeNotifications();
    initializeModals();
    initializeSearch();
});

/**
 * Initialize notification system
 */
function initializeNotifications() {
    // Add notification styles if not already added
    if (!document.getElementById('notification-styles')) {
        const notificationStyles = document.createElement('style');
        notificationStyles.id = 'notification-styles';
        notificationStyles.textContent = `
            .notification {
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 4px;
                background-color: white;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                justify-content: space-between;
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
                max-width: 400px;
                margin-bottom: 10px;
            }
            
            .notification.info {
                border-left: 4px solid #2196f3;
            }
            
            .notification.success {
                border-left: 4px solid #4caf50;
            }
            
            .notification.warning {
                border-left: 4px solid #ff9800;
            }
            
            .notification.error {
                border-left: 4px solid #f44336;
            }
            
            .notification-message {
                margin-right: 20px;
            }
            
            .notification-close {
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
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
        `;
        document.head.appendChild(notificationStyles);
    }
}

/**
 * Show a notification message
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (info, success, warning, error)
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close"><i class="fas fa-times"></i></button>
    `;
    
    // Add notification to the page
    document.body.appendChild(notification);
    
    // Add event listener to close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
        notification.remove();
    });
    
    // Automatically remove notification after 5 seconds
    setTimeout(() => {
        if (document.body.contains(notification)) {
            notification.remove();
        }
    }, 5000);
}

/**
 * Initialize modal functionality
 */
function initializeModals() {
    const modalOverlay = document.getElementById('modal-overlay');
    if (!modalOverlay) return;
    
    const modalClose = document.getElementById('modal-close');
    const modalCancel = document.getElementById('modal-cancel');
    
    // Close modal when close button is clicked
    modalClose.addEventListener('click', function() {
        closeModal();
    });
    
    // Close modal when cancel button is clicked
    modalCancel.addEventListener('click', function() {
        closeModal();
    });
    
    // Close modal when clicking outside the modal
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });
    
    // Initialize book request button if it exists
    const requestBookBtn = document.querySelector('.request-book-btn');
    if (requestBookBtn) {
        requestBookBtn.addEventListener('click', function() {
            openBookRequestModal();
        });
    }
}

/**
 * Open modal
 * @param {string} title - The modal title
 * @param {string} content - The modal content (HTML)
 * @param {function} confirmCallback - Callback function for confirm button
 */
function openModal(title, content, confirmCallback = null) {
    const modalOverlay = document.getElementById('modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    const modalConfirm = document.getElementById('modal-confirm');
    
    // Set modal title and content
    modalTitle.textContent = title;
    modalContent.innerHTML = content;
    
    // Set confirm button callback if provided
    if (confirmCallback) {
        modalConfirm.onclick = confirmCallback;
    } else {
        modalConfirm.onclick = closeModal;
    }
    
    // Show modal
    modalOverlay.classList.remove('hidden');
}

/**
 * Close modal
 */
function closeModal() {
    const modalOverlay = document.getElementById('modal-overlay');
    modalOverlay.classList.add('hidden');
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