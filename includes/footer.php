<!-- Modal templates -->
<div id="modal-overlay" class="hidden">
        <div id="modal-container">
            <div id="modal-header">
                <h2 id="modal-title">Modal Title</h2>
                <button id="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div id="modal-content">
                <!-- Modal content will be dynamically inserted here -->
            </div>
            <div id="modal-footer">
                <button id="modal-cancel" class="btn-secondary">Cancel</button>
                <button id="modal-confirm" class="btn-primary">Confirm</button>
            </div>
        </div>
    </div>
    
    <!-- Recent Transactions Modal Template -->
    <template id="recent-transactions-template">
        <div class="recent-transactions">
            <h2>Recent Transactions</h2>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>TIME</th>
                        <th>CUSTOMER</th>
                        <th>ITEMS</th>
                        <th>TOTAL</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Transaction rows will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </template>
    
    <!-- Book Request Modal Template -->
    <template id="book-request-template">
        <div class="book-request-form">
            <div class="form-group">
                <label for="book-title">Book Title*</label>
                <input type="text" id="book-title" placeholder="Enter book title" required>
            </div>
            <div class="form-group">
                <label for="book-author">Author (if known)</label>
                <input type="text" id="book-author" placeholder="Enter author name">
            </div>
            <div class="form-group">
                <label for="requester-name">Requested By*</label>
                <input type="text" id="requester-name" placeholder="Customer or staff name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="request-priority">Priority</label>
                    <select id="request-priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="request-quantity">Quantity</label>
                    <input type="number" id="request-quantity" value="1" min="1">
                </div>
            </div>
            <p class="form-note">* Required fields</p>
        </div>
    </template>
    
    <!-- Customer Add/Edit Modal Template -->
    <template id="customer-template">
        <div class="customer-form">
            <div class="form-group">
                <label for="customer-name">Name*</label>
                <input type="text" id="customer-name" placeholder="Enter customer name" required>
            </div>
            <div class="form-group">
                <label for="customer-email">Email</label>
                <input type="email" id="customer-email" placeholder="Enter customer email">
            </div>
            <div class="form-group">
                <label for="customer-phone">Phone</label>
                <input type="text" id="customer-phone" placeholder="Enter customer phone">
            </div>
            <div class="form-group">
                <label for="customer-loyalty">
                    <input type="checkbox" id="customer-loyalty">
                    Has Loyalty Card
                </label>
            </div>
            <p class="form-note">* Required fields</p>
        </div>
    </template>
    
    <!-- JavaScript files -->
    <script src="assets/js/main.js"></script>
    
    <?php if (isset($activeTab)): ?>
        <?php if ($activeTab === 'pos'): ?>
            <script src="assets/js/transaction.js"></script>
        <?php elseif ($activeTab === 'inventory'): ?>
            <script src="assets/js/inventory.js"></script>
        <?php elseif ($activeTab === 'book_requests'): ?>
            <script src="assets/js/book-requests.js"></script>
        <?php elseif ($activeTab === 'reports'): ?>
            <script src="assets/js/reports.js"></script>
        <?php elseif ($activeTab === 'settings'): ?>
            <script src="assets/js/settings.js"></script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>