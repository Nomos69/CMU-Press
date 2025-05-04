/**
 * Book Requests JavaScript file for Bookstore POS
 * Handles book request management functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize book requests functionality
    initializeBookRequests();
});

/**
 * Initialize book requests functionality
 */
function initializeBookRequests() {
    // Initialize filter change handlers
    initializeFilters();
    
    // Initialize add request button
    initializeAddRequest();
    
    // Initialize edit request buttons
    initializeEditRequests();
    
    // Initialize fulfill request buttons
    initializeFulfillRequests();
    
    // Initialize cancel request buttons
    initializeCancelRequests();
    
    // Initialize export and print buttons
    initializeExportPrint();
}

/**
 * Initialize filters
 */
function initializeFilters() {
    const statusFilter = document.getElementById('status-filter');
    const priorityFilter = document.getElementById('priority-filter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (priorityFilter) {
        priorityFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
}

/**
 * Initialize add request button
 */
function initializeAddRequest() {
    const addRequestBtn = document.getElementById('add-request-btn');
    
    if (!addRequestBtn) return;
    
    addRequestBtn.addEventListener('click', function() {
        openAddRequestModal();
    });
}

/**
 * Initialize edit request buttons
 */
function initializeEditRequests() {
    const editButtons = document.querySelectorAll('.edit-request-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            openEditRequestModal(requestId);
        });
    });
}

/**
 * Initialize fulfill request buttons
 */
function initializeFulfillRequests() {
    const fulfillButtons = document.querySelectorAll('.fulfill-request-btn');
    
    fulfillButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            fulfillRequest(requestId);
        });
    });
}

/**
 * Initialize cancel request buttons
 */
function initializeCancelRequests() {
    const cancelButtons = document.querySelectorAll('.cancel-request-btn');
    
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            cancelRequest(requestId);
        });
    });
}

/**
 * Initialize export and print buttons
 */
function initializeExportPrint() {
    const exportBtn = document.getElementById('export-requests-btn');
    const printBtn = document.getElementById('print-requests-btn');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportRequests();
        });
    }
    
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            printRequests();
        });
    }
}

/**
 * Open add request modal
 */
function openAddRequestModal() {
    const template = document.getElementById('request-template');
    
    if (!template) return;
    
    openModal('Add New Book Request', template.content.cloneNode(true).querySelector('.request-form').outerHTML, addRequest);
    
    // Hide status field for new requests (always pending)
    const statusField = document.getElementById('request-status');
    if (statusField) {
        statusField.parentElement.style.display = 'none';
    }
}

/**
 * Open edit request modal
 * @param {string} requestId Request ID
 */
function openEditRequestModal(requestId) {
    // Get request details from the table row
    const row = document.querySelector(`tr[data-id="${requestId}"]`);
    if (!row) return;
    
    const title = row.cells[0].textContent;
    const author = row.cells[1].textContent;
    const requestedBy = row.cells[2].textContent;
    
    // Get priority from the class name of the priority badge
    const priorityBadge = row.cells[4].querySelector('.priority');
    const priority = priorityBadge ? 
        priorityBadge.classList.contains('high-priority') ? 'high' :
        priorityBadge.classList.contains('medium-priority') ? 'medium' : 'low'
        : 'medium';
    
    const quantity = row.cells[5].textContent;
    
    // Get status based on text content, not class
    const statusBadge = row.cells[6].querySelector('.status');
    const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : 'pending';
    let status;
    
    // Map the status text to the correct status value
    switch (statusText) {
        case 'fulfilled':
            status = 'fulfilled';
            break;
        default:
            status = 'pending';
    }
    
    const template = document.getElementById('request-template');
    
    if (!template) return;
    
    openModal('Edit Book Request', template.content.cloneNode(true).querySelector('.request-form').outerHTML, function() {
        updateRequest(requestId);
    });
    
    // Populate form fields
    document.getElementById('request-title').value = title;
    document.getElementById('request-author').value = author;
    document.getElementById('request-by').value = requestedBy;
    document.getElementById('request-priority').value = priority;
    document.getElementById('request-quantity').value = quantity;
    document.getElementById('request-status').value = status;
}

/**
 * Add request
 */
function addRequest() {
    const titleInput = document.getElementById('request-title');
    const authorInput = document.getElementById('request-author');
    const requestedByInput = document.getElementById('request-by');
    const prioritySelect = document.getElementById('request-priority');
    const quantityInput = document.getElementById('request-quantity');
    
    // Validate inputs
    if (!titleInput.value) {
        showNotification('Book title is required', 'warning');
        return;
    }
    
    if (!requestedByInput.value) {
        showNotification('Requested by is required', 'warning');
        return;
    }
    
    // Prepare request data
    const requestData = {
        title: titleInput.value,
        author: authorInput.value,
        requested_by: requestedByInput.value,
        priority: prioritySelect.value,
        quantity: parseInt(quantityInput.value)
    };
    
    // Show loading notification
    showNotification('Adding book request...', 'info');
    
    // Send request to server
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
        
        if (data.request_id) {
            showNotification('Book request added successfully', 'success');
            
            // Reload page to show new request
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to add book request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        closeModal();
        showNotification('An error occurred while adding the book request', 'error');
    });
}

/**
 * Update request
 * @param {string} requestId Request ID
 */
function updateRequest(requestId) {
    const titleInput = document.getElementById('request-title');
    const authorInput = document.getElementById('request-author');
    const requestedByInput = document.getElementById('request-by');
    const prioritySelect = document.getElementById('request-priority');
    const quantityInput = document.getElementById('request-quantity');
    const statusSelect = document.getElementById('request-status');
    
    // Validate inputs
    if (!titleInput.value) {
        showNotification('Book title is required', 'warning');
        return;
    }
    
    if (!requestedByInput.value) {
        showNotification('Requested by is required', 'warning');
        return;
    }
    
    // Prepare request data
    const requestData = {
        request_id: requestId,
        title: titleInput.value,
        author: authorInput.value,
        requested_by: requestedByInput.value,
        priority: prioritySelect.value,
        quantity: parseInt(quantityInput.value),
        status: statusSelect.value
    };
    
    // Show loading notification
    showNotification('Updating book request...', 'info');
    
    // Send request to API
    fetch('api/book_request/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        
        if (data.message && data.message.includes('successfully')) {
            showNotification(data.message, 'success');
            
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to update book request.', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating book request:', error);
        closeModal();
        showNotification('An error occurred while updating the book request.', 'error');
    });
}

/**
 * Fulfill request
 * @param {string} requestId Request ID
 */
function fulfillRequest(requestId) {
    if (confirm('Are you sure you want to fulfill this request? This will add the book to inventory with the requested quantity as stock.')) {
        // Show loading notification
        showNotification('Processing request...', 'info');
        
        // Prepare request data
        const requestData = {
            request_id: requestId,
            status: 'fulfilled'
        };
        
        // Send request to API
        fetch('api/book_request/update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message && data.message.includes('successfully')) {
                showNotification(data.message, 'success');
                
                // Reload page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to fulfill book request.', 'error');
            }
        })
        .catch(error => {
            console.error('Error fulfilling book request:', error);
            showNotification('An error occurred while fulfilling the book request.', 'error');
        });
    }
}

/**
 * Cancel request
 * @param {string} requestId Request ID
 */
function cancelRequest(requestId) {
    if (confirm('Are you sure you want to fulfill this request?')) {
        // Show loading notification
        showNotification('Fulfilling request...', 'info');
        
        // Prepare request data
        const requestData = {
            request_id: requestId,
            status: 'fulfilled'
        };
        
        // Send request to API
        fetch('api/book_request/update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message && data.message.includes('successfully')) {
                showNotification('Request fulfilled successfully', 'success');
                
                // Reload page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to fulfill book request.', 'error');
            }
        })
        .catch(error => {
            console.error('Error fulfilling book request:', error);
            showNotification('An error occurred while fulfilling the book request.', 'error');
        });
    }
}

/**
 * Export requests
 */
function exportRequests() {
    // Show loading notification
    showNotification('Preparing export...', 'info');
    
    // In a real application, this would make an API call to export the requests
    // For this demo, we'll simulate an API response
    setTimeout(() => {
        showNotification('Export completed', 'success');
        
        // Simulate download
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent('Book Requests Export');
        a.download = 'book_requests_export.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }, 1000);
}

/**
 * Print requests
 */
function printRequests() {
    // Show loading notification
    showNotification('Preparing print view...', 'info');
    
    // In a real application, this would prepare a print-friendly view
    // For this demo, we'll just print the current page
    setTimeout(() => {
        window.print();
    }, 500);
}

/**
 * Add CSS for book requests page
 */
const bookRequestStyles = document.createElement('style');
bookRequestStyles.textContent = `
    .filters {
        margin-bottom: 1.5rem;
    }
    
    .filter-form {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .filter-group {
        display: flex;
    }
    
    .filter-group input, .filter-group select {
        padding: 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }
    
    .filter-group input + button {
        border-radius: 0 4px 4px 0;
        border-left: none;
        background-color: var(--primary-color);
        color: white;
        padding: 0 0.8rem;
        cursor: pointer;
    }
    
    .filter-group input {
        border-radius: 4px 0 0 4px;
        min-width: 250px;
    }
    
    .clear-filters {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .requests-table-wrapper {
        overflow-x: auto;
        margin-bottom: 1.5rem;
    }
    
    .requests-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .requests-table th,
    .requests-table td {
        padding: 0.8rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    
    .requests-table th {
        font-weight: 500;
        border-bottom: 2px solid var(--border-color);
    }
    
    .actions {
        white-space: nowrap;
        text-align: center;
    }
    
    .actions button {
        background: none;
        border: none;
        cursor: pointer;
        margin: 0 5px;
        font-size: 1.1rem;
        padding: 5px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .actions button:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .actions button.edit-request-btn {
        color: var(--primary-color);
    }
    
    .actions button.fulfill-request-btn,
    .actions button.cancel-request-btn {
        color: var(--success-color);
    }
    
    .no-data {
        text-align: center;
        padding: 2rem 0;
        color: var(--light-text);
    }
    
    /* Status styles */
    .status {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status.pending {
        background-color: #e3f2fd;
        color: #1976d2;
    }
    
    .status.fulfilled {
        background-color: #e8f5e9;
        color: #388e3c;
    }
    
    /* Priority styles */
    .priority {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .priority.high-priority {
        background-color: #ffebee;
        color: #d32f2f;
    }
    
    .priority.medium-priority {
        background-color: #fff8e1;
        color: #ff8f00;
    }
    
    .priority.low-priority {
        background-color: #e8f5e9;
        color: #388e3c;
    }
    
    .priority-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .priority-stat {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 4px;
    }
    
    .priority-stat.high {
        background-color: #ffebee;
    }
    
    .priority-stat.medium {
        background-color: #fff8e1;
    }
    
    .priority-stat.low {
        background-color: #e8f5e9;
    }
    
    .priority-icon {
        font-size: 1.5rem;
        margin-right: 0.8rem;
    }
    
    .priority-stat.high .priority-icon {
        color: var(--error-color);
    }
    
    .priority-stat.medium .priority-icon {
        color: var(--warning-color);
    }
    
    .priority-stat.low .priority-icon {
        color: var(--success-color);
    }
    
    .priority-label {
        font-size: 0.8rem;
        margin-bottom: 0.2rem;
    }
    
    .priority-value {
        font-size: 1.2rem;
        font-weight: 500;
    }
    
    .request-actions {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .request-actions button {
        width: 100%;
    }
    
    @media print {
        header, nav, .right-column, .filters, .actions, .pagination-container {
            display: none !important;
        }
        
        .left-column {
            width: 100% !important;
        }
        
        .container {
            display: block !important;
        }
        
        body, html {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        
        .card-header {
            border-bottom: 1px solid #000 !important;
        }
        
        table th {
            background-color: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
    
    .stat-section {
        margin-bottom: 2rem;
    }
    
    .stat-section h3 {
        font-size: 1rem;
        margin-bottom: 1rem;
        color: var(--text-color);
    }
    
    .stat-bars {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .stat-bar {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .stat-label {
        width: 80px;
        font-size: 0.9rem;
    }
    
    .bar-container {
        flex: 1;
        height: 0.8rem;
        background-color: #f5f5f5;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .bar {
        height: 100%;
        border-radius: 4px;
    }
    
    .stat-value {
        width: 30px;
        text-align: right;
        font-size: 0.9rem;
        font-weight: 500;
    }
`;

document.head.appendChild(bookRequestStyles);