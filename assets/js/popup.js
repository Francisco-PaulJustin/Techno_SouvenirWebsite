/**
 * Custom Popup Alert and Confirm System
 * Replaces native browser alert() and confirm() with styled popups
 */

// Create popup container if it doesn't exist
function initPopupSystem() {
    if (document.getElementById('custom-popup-container')) {
        return;
    }

    const container = document.createElement('div');
    container.id = 'custom-popup-container';
    container.innerHTML = `
        <div id="custom-popup-overlay" class="custom-popup-overlay"></div>
        <div id="custom-popup-alert" class="custom-popup custom-popup-alert">
            <div class="custom-popup-icon">
                <span class="material-icons-round"></span>
            </div>
            <div class="custom-popup-content">
                <h3 class="custom-popup-title"></h3>
                <p class="custom-popup-message"></p>
            </div>
            <div class="custom-popup-actions">
                <button class="custom-popup-btn custom-popup-btn-primary" id="custom-popup-ok">OK</button>
            </div>
        </div>
        <div id="custom-popup-confirm" class="custom-popup custom-popup-confirm">
            <div class="custom-popup-icon">
                <span class="material-icons-round">help_outline</span>
            </div>
            <div class="custom-popup-content">
                <h3 class="custom-popup-title">Confirm Action</h3>
                <p class="custom-popup-message"></p>
            </div>
            <div class="custom-popup-actions">
                <button class="custom-popup-btn custom-popup-btn-secondary" id="custom-popup-cancel">Cancel</button>
                <button class="custom-popup-btn custom-popup-btn-primary" id="custom-popup-confirm-btn">Confirm</button>
            </div>
        </div>
    `;
    document.body.appendChild(container);
}

// Initialize on DOM load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initPopupSystem();
        // Ensure overlay is hidden on load
        const overlay = document.getElementById('custom-popup-overlay');
        if (overlay) {
            overlay.style.display = 'none';
            overlay.style.pointerEvents = 'none';
        }
    });
} else {
    initPopupSystem();
    // Ensure overlay is hidden on load
    const overlay = document.getElementById('custom-popup-overlay');
    if (overlay) {
        overlay.style.display = 'none';
        overlay.style.pointerEvents = 'none';
    }
}

/**
 * Custom Alert - Replaces window.alert()
 * @param {string} message - The message to display
 * @param {string} type - Type of alert: 'info', 'success', 'warning', 'error' (default: 'info')
 * @returns {Promise} - Resolves when user clicks OK
 */
function customAlert(message, type = 'info') {
    return new Promise((resolve) => {
        initPopupSystem();
        
        const overlay = document.getElementById('custom-popup-overlay');
        const popup = document.getElementById('custom-popup-alert');
        const iconEl = popup.querySelector('.custom-popup-icon .material-icons-round');
        const messageEl = popup.querySelector('.custom-popup-message');
        const okBtn = document.getElementById('custom-popup-ok');
        
        // Set icon and color based on type
        const config = {
            info: { icon: 'info', color: '#3498DB' },
            success: { icon: 'check_circle', color: '#2ECC71' },
            warning: { icon: 'warning', color: '#FFB800' },
            error: { icon: 'error', color: '#E74C3C' }
        };
        
        const alertConfig = config[type] || config.info;
        iconEl.textContent = alertConfig.icon;
        iconEl.style.color = alertConfig.color;
        popup.style.setProperty('--popup-accent', alertConfig.color);
        
        // Set message
        messageEl.textContent = message;
        
        // Show popup
        overlay.style.display = 'block';
        overlay.style.pointerEvents = 'all';
        popup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Animation
        setTimeout(() => {
            overlay.classList.add('active');
            popup.classList.add('active');
        }, 10);
        
        // Close handler
        const closePopup = () => {
            overlay.classList.remove('active');
            popup.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
                popup.style.display = 'none';
                overlay.style.pointerEvents = 'none';
                document.body.style.overflow = '';
                resolve();
            }, 300);
        };
        
        // Event listeners
        okBtn.onclick = closePopup;
        overlay.onclick = closePopup;
        
        // ESC key to close
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closePopup();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    });
}

/**
 * Custom Confirm - Replaces window.confirm()
 * @param {string} message - The message to display
 * @param {string} title - Optional title (default: 'Confirm Action')
 * @returns {Promise<boolean>} - Resolves to true if confirmed, false if cancelled
 */
function customConfirm(message, title = 'Confirm Action') {
    return new Promise((resolve) => {
        initPopupSystem();
        
        const overlay = document.getElementById('custom-popup-overlay');
        const popup = document.getElementById('custom-popup-confirm');
        const titleEl = popup.querySelector('.custom-popup-title');
        const messageEl = popup.querySelector('.custom-popup-message');
        const confirmBtn = document.getElementById('custom-popup-confirm-btn');
        const cancelBtn = document.getElementById('custom-popup-cancel');
        
        // Set content
        titleEl.textContent = title;
        messageEl.textContent = message;
        
        // Show popup
        overlay.style.display = 'block';
        overlay.style.pointerEvents = 'all';
        popup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Animation
        setTimeout(() => {
            overlay.classList.add('active');
            popup.classList.add('active');
        }, 10);
        
        // Close handler
        const closePopup = (result) => {
            overlay.classList.remove('active');
            popup.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
                popup.style.display = 'none';
                overlay.style.pointerEvents = 'none';
                document.body.style.overflow = '';
                resolve(result);
            }, 300);
        };
        
        // Event listeners
        confirmBtn.onclick = () => closePopup(true);
        cancelBtn.onclick = () => closePopup(false);
        overlay.onclick = () => closePopup(false);
        
        // ESC key to cancel
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closePopup(false);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    });
}

// Override native alert and confirm globally
// Note: These are async, so they return Promises instead of blocking
if (typeof window !== 'undefined') {
    // Store original functions for reference
    window._nativeAlert = window.alert;
    window._nativeConfirm = window.confirm;
    
    // Override with custom functions
    // Note: These return Promises, so code expecting synchronous behavior may need adjustment
    window.alert = function(message) {
        // If called synchronously, show the alert but don't block
        customAlert(message, 'info');
        return undefined;
    };
    
    window.confirm = function(message) {
        // For confirm, we need to handle it differently since it's used in onsubmit
        // Return false by default, but show the popup
        // The actual confirmation should be handled via async/await
        customConfirm(message).then(result => {
            // This won't work for synchronous code, but we've replaced all instances
        });
        return false; // Default to false for safety
    };
}

// Export functions for explicit use
if (typeof window !== 'undefined') {
    window.customAlert = customAlert;
    window.customConfirm = customConfirm;
}

