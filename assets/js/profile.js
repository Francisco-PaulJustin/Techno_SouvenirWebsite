document.addEventListener('DOMContentLoaded', () => {
    const avatarForm = document.getElementById('avatar-form');
    const avatarInput = document.getElementById('profile_image_upload');
    const profileForm = document.querySelector('.profile-form');
    const passwordForm = document.getElementById('password-form');

    const profileGrid = document.getElementById('profileGrid');
    const orderHistorySection = document.getElementById('orderHistory');
    const btnShowProfile = document.getElementById('btnShowProfile');

    // Initial setup: Hide profile sections, show order history
    if (profileGrid) {
        profileGrid.style.display = 'none';
    }
    if (orderHistorySection) {
        orderHistorySection.style.display = 'block';
    }

    // Toggle visibility of profile sections vs. order history
    if (btnShowProfile) {
        btnShowProfile.addEventListener('click', () => {
            if (profileGrid.style.display === 'none') {
                profileGrid.style.display = 'grid';
                orderHistorySection.style.display = 'none';
            } else {
                // If already visible, maybe toggle back to order history or do nothing
                // For this request, we assume it toggles TO profile sections
            }
        });
    }

    // Handle form submissions to keep the relevant section open if navigated back
    const urlParams = new URLSearchParams(window.location.search);
    const formSubmitted = urlParams.get('form_submitted');
    const activeSection = urlParams.get('active_section');
    const orderSuccess = urlParams.get('order_success');
    const orderId = urlParams.get('order_id');

    if (formSubmitted && activeSection) {
        if (profileGrid) {
            profileGrid.style.display = 'grid';
        }
        if (orderHistorySection) {
            orderHistorySection.style.display = 'none';
        }
    }

    // Auto-scroll to new order if redirected from checkout
    if (orderSuccess === 'true' && orderId) {
        // Ensure order history section is visible
        if (orderHistorySection) {
            orderHistorySection.style.display = 'block';
        }
        if (profileGrid) {
            profileGrid.style.display = 'none';
        }

        // Wait for DOM to be fully rendered, then scroll to the order
        setTimeout(() => {
            const newOrderCard = document.querySelector(`[data-new-order="true"]`);
            if (newOrderCard) {
                // Scroll to the order card
                newOrderCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Expand the order details if not already expanded
                const orderHeader = newOrderCard.querySelector('.order-summary-header');
                const orderDetails = newOrderCard.querySelector('.order-details-panel');
                
                if (orderHeader && orderDetails && orderHeader.getAttribute('aria-expanded') === 'true') {
                    // Trigger the expand animation
                    orderDetails.removeAttribute('hidden');
                    orderDetails.style.display = 'block';
                    orderDetails.offsetHeight; // Force reflow
                    
                    orderDetails.style.maxHeight = '0';
                    orderDetails.style.opacity = '0';
                    orderDetails.style.paddingTop = '0';
                    orderDetails.style.paddingBottom = '0';
                    orderDetails.style.overflowY = 'hidden';
                    
                    requestAnimationFrame(() => {
                        orderDetails.style.maxHeight = orderDetails.scrollHeight + 'px';
                        orderDetails.style.opacity = '1';
                        orderDetails.style.paddingTop = '1.5rem';
                        orderDetails.style.paddingBottom = '1.5rem';
                    });
                    
                    orderDetails.addEventListener('transitionend', function expandHandler() {
                        orderDetails.style.maxHeight = 'none';
                        orderDetails.style.overflowY = 'visible';
                        orderDetails.removeEventListener('transitionend', expandHandler);
                    }, { once: true });
                }
                
                // Clean up URL
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }, 300);
    }

    // Profile picture upload
    if (avatarInput) {
        avatarInput.addEventListener('change', () => {
            if (avatarInput.files.length) {
                avatarForm.submit();
            }
        });
    }

    // Form validation (existing logic, retained)
    const validateField = (field) => {
        if (!field) return;
        if (field.required && !field.value.trim()) {
            field.classList.add('is-invalid');
        } else if (field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    };

    if (profileForm) {
        profileForm.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('input', () => validateField(field));
        });
    }

    if (passwordForm) {
        const newPassword = passwordForm.querySelector('#new_password');
        const confirmPassword = passwordForm.querySelector('#confirm_password');

        passwordForm.addEventListener('input', () => {
            if (newPassword && confirmPassword) {
                if (confirmPassword.value && confirmPassword.value !== newPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                } else {
                    confirmPassword.classList.remove('is-invalid');
                }
            }
        });
    }

    // Order history accordion functionality
    const orderSummaryHeaders = document.querySelectorAll('.order-summary-header');

    orderSummaryHeaders.forEach(header => {
        const detailsPanelId = header.getAttribute('aria-controls');
        const detailsPanel = document.getElementById(detailsPanelId);

        if (!detailsPanel) return; // Ensure panel exists

        // Set initial state based on 'hidden' attribute in HTML
        if (detailsPanel.hasAttribute('hidden')) {
            header.setAttribute('aria-expanded', 'false');
            detailsPanel.style.maxHeight = '0';
            detailsPanel.style.opacity = '0';
            detailsPanel.style.paddingTop = '0';
            detailsPanel.style.paddingBottom = '0';
            detailsPanel.style.overflowY = 'hidden';
        } else {
            header.setAttribute('aria-expanded', 'true');
            detailsPanel.style.maxHeight = 'none'; // Allow content to dictate height
            detailsPanel.style.opacity = '1';
            detailsPanel.style.paddingTop = '1.5rem';
            detailsPanel.style.paddingBottom = '1.5rem';
            detailsPanel.style.overflowY = 'visible';
        }

        header.addEventListener('click', () => {
            const isExpanded = header.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                // Collapse
                detailsPanel.style.maxHeight = detailsPanel.scrollHeight + 'px'; // Set explicit height for transition
                detailsPanel.style.opacity = '1';
                detailsPanel.style.paddingTop = '1.5rem';
                detailsPanel.style.paddingBottom = '1.5rem';
                detailsPanel.style.overflowY = 'hidden';

                requestAnimationFrame(() => {
                    header.setAttribute('aria-expanded', 'false');
                    detailsPanel.style.maxHeight = '0';
                    detailsPanel.style.opacity = '0';
                    detailsPanel.style.paddingTop = '0';
                    detailsPanel.style.paddingBottom = '0';
                });

                detailsPanel.addEventListener('transitionend', function collapseHandler() {
                    detailsPanel.setAttribute('hidden', '');
                    detailsPanel.removeEventListener('transitionend', collapseHandler);
                }, { once: true });

            } else {
                // Expand
                detailsPanel.removeAttribute('hidden');
                detailsPanel.style.display = 'block'; // Ensure it renders for scrollHeight

                // Force reflow to get correct scrollHeight after removing hidden
                detailsPanel.offsetHeight;

                detailsPanel.style.maxHeight = '0'; // Start from 0 for animation
                detailsPanel.style.opacity = '0';
                detailsPanel.style.paddingTop = '0';
                detailsPanel.style.paddingBottom = '0';
                detailsPanel.style.overflowY = 'hidden';

                requestAnimationFrame(() => {
                    header.setAttribute('aria-expanded', 'true');
                    detailsPanel.style.maxHeight = detailsPanel.scrollHeight + 'px';
                    detailsPanel.style.opacity = '1';
                    detailsPanel.style.paddingTop = '1.5rem';
                    detailsPanel.style.paddingBottom = '1.5rem';
                });

                detailsPanel.addEventListener('transitionend', function expandHandler() {
                    detailsPanel.style.maxHeight = 'none'; // Allow natural height after animation
                    detailsPanel.style.overflowY = 'visible';
                    detailsPanel.removeEventListener('transitionend', expandHandler);
                }, { once: true });
            }
        });
    });
});
