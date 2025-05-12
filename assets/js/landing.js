// Landing page JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            handleSearch();
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Adjust for header
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Handle search function
    function handleSearch() {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            // In a real application, you would implement actual search functionality
            // For now, just show an alert
            alert('Search functionality would search for: ' + query);
            
            // Or redirect to a search results page
            // window.location.href = 'search.php?q=' + encodeURIComponent(query);
        }
    }
}); 