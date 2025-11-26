// Product page functionality

document.addEventListener('DOMContentLoaded', function() {
    // Add to cart form
    const addToCartForm = document.querySelector('.add-to-cart-form');
    const messageEl = document.getElementById('cart-inline-message');

    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (typeof window.isUserLoggedIn === 'function' && !window.isUserLoggedIn()) {
                renderCartInlineMessage('login', null, messageEl);
                return;
            }

            const formData = new FormData(this);
            const productId = formData.get('product_id');
            const quantity = formData.get('quantity');
            
            addToCart(productId, quantity, messageEl);
        });
    }
});

let cartMessageTimeout;

function renderCartInlineMessage(type, backendMessage, messageEl) {
    if (!messageEl) return;

    let html = '';

    if (type === 'login') {
        html = `
            <p>Please log in or create an account to add this item to your cart.</p>
            <div class="cart-inline-actions">
                <a href="login.php" class="btn gradient-btn cart-inline-btn">Login</a>
                <a href="signup.php" class="btn ghost-btn cart-inline-btn">Sign Up</a>
            </div>
        `;
    } else if (type === 'success') {
        html = `<p>Item successfully added to your cart!</p>`;
    } else if (type === 'error') {
        const safeMessage = backendMessage || 'Error adding to cart. Please try again.';
        html = `<p>${safeMessage}</p>`;
    }

    messageEl.innerHTML = html;
    messageEl.classList.add('is-visible');

    if (cartMessageTimeout) {
        clearTimeout(cartMessageTimeout);
    }

    if (type === 'success') {
        cartMessageTimeout = setTimeout(() => {
            messageEl.classList.remove('is-visible');
        }, 4000);
    }
}

function addToCart(productId, quantity, messageEl) {
    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.requiresLogin) {
            renderCartInlineMessage('login', data.message, messageEl);
        } else if (data.success) {
            renderCartInlineMessage('success', data.message, messageEl);
            // Update cart count
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        } else {
            renderCartInlineMessage('error', data.message, messageEl);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        renderCartInlineMessage('error', null, messageEl);
    });
}

