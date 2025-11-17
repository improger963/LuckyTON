document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    
    if (mobileMenuToggle && adminSidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('mobile-open');
        });
    }
    
    // Sidebar toggle for small screens
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.add('hidden');
        });
    }
    
    // Close alerts
    const closeButtons = document.querySelectorAll('[onclick*="remove"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.admin-alert');
            if (alert) {
                alert.remove();
            }
        });
    });
    
    // Confirmation for destructive actions
    const deleteForms = document.querySelectorAll('form[action*="destroy"], form[action*="delete"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});