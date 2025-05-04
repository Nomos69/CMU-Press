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
}); 