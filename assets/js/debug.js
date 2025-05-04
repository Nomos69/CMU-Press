/**
 * Debug JavaScript file
 * Logs JavaScript errors to the console
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Debug script loaded');
    
    // Check if required DOM elements exist
    const requiredElements = [
        { id: 'transaction-id', name: 'Transaction ID' },
        { id: 'transaction-items', name: 'Transaction Items Table' },
        { id: 'item-search', name: 'Item Search Input' },
        { id: 'add-item-btn', name: 'Add Item Button' },
        { id: 'customer-field', name: 'Customer Field' },
        { id: 'add-customer-btn', name: 'Add Customer Button' },
        { id: 'subtotal', name: 'Subtotal' },
        { id: 'tax', name: 'Tax' },
        { id: 'total', name: 'Total' },
        { id: 'checkout-btn', name: 'Checkout Button' }
    ];
    
    console.log('Checking required DOM elements...');
    let missingElements = 0;
    
    requiredElements.forEach(element => {
        const el = document.getElementById(element.id);
        if (!el) {
            console.error(`Missing element: ${element.name} (${element.id})`);
            missingElements++;
        } else {
            console.log(`Found element: ${element.name} (${element.id})`);
        }
    });
    
    if (missingElements === 0) {
        console.log('All required DOM elements found');
    } else {
        console.error(`Missing ${missingElements} required DOM elements`);
    }
    
    // Check for JS errors
    window.addEventListener('error', function(e) {
        console.error('JavaScript error detected:', e.message, 'at', e.filename, 'line', e.lineno);
    });
    
    // Test transaction.js functions
    console.log('Testing transaction.js functions...');
    
    try {
        // Check if main transaction functions are defined
        if (typeof initializeTransaction === 'function') {
            console.log('initializeTransaction function found');
        } else {
            console.error('initializeTransaction function not found');
        }
        
        if (typeof searchAndAddItem === 'function') {
            console.log('searchAndAddItem function found');
        } else {
            console.error('searchAndAddItem function not found');
        }
        
        if (typeof processCheckout === 'function') {
            console.log('processCheckout function found');
        } else {
            console.error('processCheckout function not found');
        }
    } catch (error) {
        console.error('Error testing transaction functions:', error.message);
    }

    // Check if we're on the POS page
    if (document.getElementById('pos')) {
        console.log('POS page detected');
        
        // Debug for item search
        debugElementWithFunction('item-search', 'search input');
        debugElementWithFunction('add-item-btn', 'add item button');
        
        // Debug for inventory search
        debugElementWithFunction('inventory-search', 'inventory search');
        debugElementWithFunction('search-inventory-btn', 'search inventory button');
        
        // Add a debug button to test search
        addDebugButtons();
    }
});

/**
 * Debug an element and report if it exists
 * @param {string} id Element ID
 * @param {string} description Description of the element
 */
function debugElementWithFunction(id, description) {
    const element = document.getElementById(id);
    if (element) {
        console.log(`✅ Found ${description} (${id})`);
        
        // Log all event listeners (this is a limited approach)
        console.log(`Events on ${id}:`, element);
    } else {
        console.error(`❌ Missing ${description} (${id})`);
    }
}

/**
 * Add debug buttons to the page
 */
function addDebugButtons() {
    const container = document.createElement('div');
    container.style.position = 'fixed';
    container.style.bottom = '10px';
    container.style.right = '10px';
    container.style.zIndex = '9999';
    container.style.background = '#f0f0f0';
    container.style.padding = '10px';
    container.style.borderRadius = '5px';
    container.style.boxShadow = '0 0 5px rgba(0,0,0,0.2)';
    
    const testSearchButton = document.createElement('button');
    testSearchButton.textContent = 'Test Book Search';
    testSearchButton.style.marginRight = '5px';
    testSearchButton.style.padding = '5px 10px';
    testSearchButton.addEventListener('click', function() {
        console.log('Testing book search');
        
        // Test with a direct call to the search function
        try {
            searchAndAddItem('The Great Gatsby');
            console.log('Search function called successfully');
        } catch (error) {
            console.error('Error calling search function:', error);
            alert('Error in search function. See console for details.');
        }
    });
    
    const testInventorySearchButton = document.createElement('button');
    testInventorySearchButton.textContent = 'Test Inventory Search';
    testInventorySearchButton.style.padding = '5px 10px';
    testInventorySearchButton.addEventListener('click', function() {
        console.log('Testing inventory search');
        
        // Set value and trigger click
        const searchInput = document.getElementById('inventory-search');
        const searchButton = document.getElementById('search-inventory-btn');
        
        if (searchInput && searchButton) {
            searchInput.value = 'Test Book';
            searchButton.click();
            console.log('Inventory search triggered');
        } else {
            console.error('Inventory search elements not found');
            alert('Inventory search elements not found');
        }
    });
    
    const addMockBookButton = document.createElement('button');
    addMockBookButton.textContent = 'Add Mock Book';
    addMockBookButton.style.padding = '5px 10px';
    addMockBookButton.style.marginTop = '5px';
    addMockBookButton.addEventListener('click', function() {
        console.log('Adding mock book directly');
        
        try {
            // Add a mock book directly
            addTransactionItem({
                book_id: 999,
                title: 'Test Book',
                author: 'Test Author',
                price: 12.99,
                quantity: 1,
                total: 12.99
            });
            
            updateTransactionSummary();
            console.log('Mock book added successfully');
        } catch (error) {
            console.error('Error adding mock book:', error);
            alert('Error adding mock book. See console for details.');
        }
    });
    
    container.appendChild(testSearchButton);
    container.appendChild(testInventorySearchButton);
    container.appendChild(document.createElement('br'));
    container.appendChild(addMockBookButton);
    
    document.body.appendChild(container);
} 