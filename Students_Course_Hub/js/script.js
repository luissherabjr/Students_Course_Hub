// ============================================
// STUDENT COURSE HUB - MAIN JAVASCRIPT FILE
// COMPLETE & FULLY FUNCTIONAL
// ============================================

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ JavaScript loaded successfully');
    
    // ========================================
    // MOBILE MENU TOGGLE - SIMPLE & RELIABLE
    // ========================================
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        console.log('✅ Mobile menu elements found');
        
        // DIRECT CLICK HANDLER - MOST RELIABLE METHOD
        menuToggle.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🎯 Hamburger menu clicked');
            
            // Toggle the show class
            if (navMenu.classList.contains('show')) {
                navMenu.classList.remove('show');
                console.log('Menu hidden');
            } else {
                navMenu.classList.add('show');
                console.log('Menu shown');
            }
            
            // Update ARIA expanded state
            const isExpanded = navMenu.classList.contains('show');
            this.setAttribute('aria-expanded', isExpanded);
            
            // Change icon between hamburger and X
            const icon = this.querySelector('i');
            if (icon) {
                if (isExpanded) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            }
            
            return false;
        };
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!menuToggle.contains(event.target) && !navMenu.contains(event.target)) {
                if (navMenu.classList.contains('show')) {
                    navMenu.classList.remove('show');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    
                    const icon = menuToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-bars';
                    }
                    console.log('Menu closed by outside click');
                }
            }
        });
        
        // Close menu when window is resized above mobile
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                if (navMenu.classList.contains('show')) {
                    navMenu.classList.remove('show');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    
                    const icon = menuToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-bars';
                    }
                    console.log('Menu closed by resize');
                }
            }
        });
        
        // Close menu when a nav link is clicked (on mobile only)
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    navMenu.classList.remove('show');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    
                    const icon = menuToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-bars';
                    }
                    console.log('Menu closed by link click');
                }
            });
        });
        
        console.log('✅ Mobile menu fully initialized');
    } else {
        console.warn('⚠️ Mobile menu elements not found', {menuToggle, navMenu});
    }
    
    // ========================================
    // MODAL HANDLING
    // ========================================
    
    // Open modals with data-modal attribute
    document.querySelectorAll('[data-modal]').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Close buttons
    document.querySelectorAll('.close, [data-close]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });
    
    // ========================================
    // DELETE BUTTON HANDLING
    // ========================================
    
    // Delete buttons with data attributes
    document.querySelectorAll('.delete-btn[data-id]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type') || 'item';
            const url = this.getAttribute('data-url');
            
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });
    
    // Traditional delete buttons (with onclick)
    document.querySelectorAll('.delete-btn:not([data-id])').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // ========================================
    // EDIT BUTTON HANDLING
    // ========================================
    
    document.querySelectorAll('.edit-btn[data-id]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type') || 'programme';
            window.location.href = `edit-${type}.php?id=${id}`;
        });
    });
    
    // ========================================
    // AUTO-HIDE ALERT MESSAGES
    // ========================================
    
    document.querySelectorAll('.alert, .error-message, .success-message').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // ========================================
    // FILTER SELECTS
    // ========================================
    
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            window.location.href = '?filter=' + this.value;
        });
    });
    
    console.log('✅ All event handlers initialized');
});

// ============================================
// GLOBAL FUNCTIONS - AVAILABLE EVERYWHERE
// ============================================

// Open modal function
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        // Focus first input for accessibility
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
        console.log(`Modal opened: ${modalId}`);
    }
}

// Close modal function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        // Reset any error styles
        modal.querySelectorAll('input, select, textarea').forEach(input => {
            input.style.borderColor = '#e0e0e0';
        });
        modal.querySelectorAll('.error-text').forEach(msg => msg.remove());
        console.log(`Modal closed: ${modalId}`);
    }
}

// Form validation function
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    let firstInvalid = null;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    // Clear previous errors
    form.querySelectorAll('.error-text').forEach(el => el.remove());
    inputs.forEach(input => {
        input.style.borderColor = '#e0e0e0';
        input.removeAttribute('aria-invalid');
    });
    
    // Check each input
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#dc3545';
            input.setAttribute('aria-invalid', 'true');
            isValid = false;
            
            if (!firstInvalid) {
                firstInvalid = input;
            }
            
            // Add error message
            const errorMsg = document.createElement('small');
            errorMsg.className = 'error-text';
            errorMsg.style.color = '#dc3545';
            errorMsg.style.display = 'block';
            errorMsg.style.marginTop = '5px';
            errorMsg.style.fontSize = '0.85rem';
            errorMsg.textContent = 'This field is required';
            
            const errorId = 'error-' + (input.id || Math.random().toString(36).substr(2, 9));
            input.setAttribute('aria-describedby', errorId);
            errorMsg.id = errorId;
            
            input.parentNode.appendChild(errorMsg);
        }
    });
    
    // Focus first invalid input
    if (firstInvalid) {
        firstInvalid.focus();
        showNotification('Please fill in all required fields', 'error');
    }
    
    return isValid;
}

// Show notification function
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.setAttribute('role', 'alert');
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Style the notification
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '15px 20px';
    notification.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
    notification.style.color = type === 'success' ? '#155724' : '#721c24';
    notification.style.borderRadius = '8px';
    notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    notification.style.zIndex = '9999';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '10px';
    notification.style.animation = 'slideInRight 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
    
    console.log(`Notification shown: ${message}`);
}

// Function to toggle status
function toggleStatus(itemId, type) {
    if (confirm('Toggle status?')) {
        window.location.href = `${type}.php?action=toggle-status&id=${itemId}`;
    }
}

// Function to export data
function exportData(type) {
    const filter = new URLSearchParams(window.location.search).get('filter') || 'all';
    if (confirm(`Download ${type} list as CSV file?`)) {
        window.location.href = `export-${type}.php?filter=${filter}`;
    }
}

// Add animation keyframes to document
(function() {
    if (!document.getElementById('animation-keyframes')) {
        const style = document.createElement('style');
        style.id = 'animation-keyframes';
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @keyframes slideInDown {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }
})();

console.log('✅ script.js fully loaded and ready');