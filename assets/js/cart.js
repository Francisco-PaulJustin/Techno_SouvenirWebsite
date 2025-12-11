// Cart functionality

document.addEventListener('DOMContentLoaded', function() {
    // Update quantity
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const quantity = this.value;
            updateCartQuantity(productId, quantity);
        });
    });
    
    // Remove from cart
    const removeButtons = document.querySelectorAll('.remove-from-cart');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeFromCart(productId);
        });
    });
});

function updateCartQuantity(productId, quantity) {
    fetch('api/update_quantity.php', {
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
        if (data.success) {
            location.reload();
        } else {
            customAlert('Error updating quantity: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        customAlert('Error updating quantity', 'error');
    });
}

async function removeFromCart(productId) {
    const confirmed = await customConfirm('Are you sure you want to remove this item from your cart?');
    if (!confirmed) {
        return;
    }
    
    fetch('api/delete_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            customAlert('Error removing item: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        customAlert('Error removing item', 'error');
    });
}

