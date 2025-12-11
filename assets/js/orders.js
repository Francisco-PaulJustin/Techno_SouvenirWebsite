document.addEventListener('DOMContentLoaded', () => {
    const orderHistorySection = document.getElementById('orderHistory');

    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const orderSuccess = urlParams.get('order_success');
    const orderId = urlParams.get('order_id');

    // Auto-scroll to new order if redirected from checkout
    if (orderSuccess === 'true' && orderId) {
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
    
    // Cancel order functionality
    document.addEventListener('click', async function(e) {
        const cancelButton = e.target.closest('.cancel-order-btn');
        if (cancelButton) {
            e.preventDefault();
            e.stopPropagation();
            
            const orderId = cancelButton.getAttribute('data-order-id');
            const orderTotal = cancelButton.getAttribute('data-order-total');
            
            if (!orderId) {
                if (typeof customAlert === 'function') {
                    customAlert('Order ID is missing', 'error');
                } else {
                    alert('Order ID is missing');
                }
                return;
            }
            
            // Show confirmation dialog
            let confirmed = false;
            if (typeof customConfirm === 'function') {
                confirmed = await customConfirm(
                    `Are you sure you want to cancel this order? This action cannot be undone.\n\nOrder Total: ${orderTotal}`,
                    'Cancel Order'
                );
            } else {
                confirmed = confirm(`Are you sure you want to cancel this order? This action cannot be undone.\n\nOrder Total: ${orderTotal}`);
            }
            
            if (!confirmed) {
                return;
            }
            
            // Disable button during request
            cancelButton.disabled = true;
            const originalHTML = cancelButton.innerHTML;
            cancelButton.innerHTML = '<span class="material-icons-round">hourglass_empty</span> Cancelling...';
            
            try {
                const response = await fetch('api/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    if (typeof customAlert === 'function') {
                        customAlert('Order cancelled successfully', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        alert('Order cancelled successfully');
                        location.reload();
                    }
                } else {
                    if (typeof customAlert === 'function') {
                        customAlert(data.message || 'Failed to cancel order', 'error');
                    } else {
                        alert(data.message || 'Failed to cancel order');
                    }
                    cancelButton.disabled = false;
                    cancelButton.innerHTML = originalHTML;
                }
            } catch (error) {
                console.error('Error cancelling order:', error);
                if (typeof customAlert === 'function') {
                    customAlert('An error occurred while cancelling the order. Please try again.', 'error');
                } else {
                    alert('An error occurred while cancelling the order. Please try again.');
                }
                cancelButton.disabled = false;
                cancelButton.innerHTML = originalHTML;
            }
        }
    });
});
