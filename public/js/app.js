/**
 * Application JavaScript
 * Handles AJAX requests and dynamic interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // CSRF token setup for fetch requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Add CSRF token to all fetch requests
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        options.headers = options.headers || {};
        if (csrfToken && !options.headers['X-CSRF-Token']) {
            options.headers['X-CSRF-Token'] = csrfToken;
        }
        return originalFetch(url, options);
    };

    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('[class*="rounded-md bg-"][class*="50 p-4"]').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

    // Confirm delete actions
    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm(form.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Handle PUT/DELETE method override
    document.querySelectorAll('input[name="_method"]').forEach(function(input) {
        const form = input.closest('form');
        if (form && (input.value === 'PUT' || input.value === 'DELETE')) {
            form.addEventListener('submit', function() {
                // Ensure method stays as POST for server-side handling
            });
        }
    });
});
