import '../css/app.css'

class AdminPanel {
    constructor() {
        this.init();
    }

    init() {
        this.initSidebar();
        this.initTheme();
        this.initAlerts();
        this.initDropdowns();
        this.initTooltips();
    }

    initSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const mainContent = document.getElementById('adminMain');
        const mobileToggle = document.getElementById('mobileMenuToggle');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
                // Add overlay for mobile
                if (sidebar.classList.contains('mobile-open')) {
                    this.createOverlay();
                } else {
                    this.removeOverlay();
                }
            });
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                this.removeOverlay();
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                if (sidebar && sidebar.classList.contains('mobile-open')) {
                    if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                        sidebar.classList.remove('mobile-open');
                        this.removeOverlay();
                    }
                }
            }
        });
    }

    createOverlay() {
        // Remove existing overlay if any
        this.removeOverlay();
        
        const overlay = document.createElement('div');
        overlay.id = 'mobileOverlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden';
        overlay.addEventListener('click', () => {
            document.getElementById('adminSidebar').classList.remove('mobile-open');
            this.removeOverlay();
        });
        document.body.appendChild(overlay);
    }

    removeOverlay() {
        const overlay = document.getElementById('mobileOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    initTheme() {
        const savedTheme = localStorage.getItem('admin-theme') || 'light';
        this.applyTheme(savedTheme);
    }

    applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    initAlerts() {
        // Auto-dismiss success alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.admin-alert-success').forEach(alert => {
                alert.classList.add('opacity-0', 'translate-y-2', 'transition-all', 'duration-300');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);

        // Add dismiss functionality to all alerts
        document.querySelectorAll('.admin-alert').forEach(alert => {
            const dismissBtn = alert.querySelector('[data-dismiss="alert"]');
            if (dismissBtn) {
                dismissBtn.addEventListener('click', () => {
                    alert.classList.add('opacity-0', 'translate-y-2', 'transition-all', 'duration-300');
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                });
            }
        });
    }

    initDropdowns() {
        // Handle dropdown menus
        document.querySelectorAll('.relative.group').forEach(group => {
            const button = group.querySelector('button');
            const dropdown = group.querySelector('div');
            
            if (button && dropdown) {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!group.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            }
        });
    }

    initTooltips() {
        // Add tooltip functionality
        document.querySelectorAll('[title]').forEach(element => {
            const title = element.getAttribute('title');
            if (title) {
                element.removeAttribute('title');
                element.setAttribute('data-tooltip', title);
                
                element.addEventListener('mouseenter', (e) => {
                    this.showTooltip(e.target);
                });
                
                element.addEventListener('mouseleave', () => {
                    this.hideTooltip();
                });
            }
        });
    }

    showTooltip(element) {
        this.hideTooltip();
        
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded-md';
        tooltip.textContent = element.getAttribute('data-tooltip');
        tooltip.id = 'admin-tooltip';
        
        // Position tooltip
        const rect = element.getBoundingClientRect();
        tooltip.style.top = (rect.top - 30) + 'px';
        tooltip.style.left = (rect.left + rect.width / 2 - 20) + 'px';
        
        document.body.appendChild(tooltip);
    }

    hideTooltip() {
        const tooltip = document.getElementById('admin-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // Utility method to show loading state
    showLoading(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<div class="loading-spinner mr-2"></div> Loading...';
        button.disabled = true;

        return () => {
            button.innerHTML = originalText;
            button.disabled = false;
        };
    }

    // Utility method for API calls
    async apiCall(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    ...options.headers,
                },
                ...options,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminPanel = new AdminPanel();
});
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
