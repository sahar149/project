/**
 * Dabberha (دبرها) - Shared Client-side Scripts
 * Location: assets/js/main.js
 */

document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alert banners after 5 seconds if marked with data-auto-dismiss
    document.querySelectorAll('[data-auto-dismiss]').forEach(alertEl => {
        setTimeout(() => {
            alertEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alertEl.style.opacity = '0';
            alertEl.style.transform = 'translateY(-10px)';
            setTimeout(() => alertEl.remove(), 500);
        }, 5000);
    });

    // Close alert button handler
    document.querySelectorAll('[data-dismiss="alert"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const alertEl = btn.closest('.alert-box') || btn.parentElement;
            if (alertEl) {
                alertEl.style.transition = 'opacity 0.3s ease';
                alertEl.style.opacity = '0';
                setTimeout(() => alertEl.remove(), 300);
            }
        });
    });
});
