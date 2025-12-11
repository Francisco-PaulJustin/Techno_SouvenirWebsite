// Main JavaScript file

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    initImageFade();
    initScrollAnimations();
    initNavbarProfileMenu();
    initSmoothSections();
    initAuthModal();
    initMobileMenu();
});

function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;

    // Get cart count from session/localStorage or API
    fetch('api/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            cartCountElement.textContent = data.count || 0;
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Utility functions
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

function initImageFade() {
    document.querySelectorAll('img').forEach(img => {
        if (img.complete) {
            img.classList.add('img-loaded');
        } else {
            img.addEventListener('load', () => img.classList.add('img-loaded'));
        }
    });
}

function initScrollAnimations() {
    const elements = document.querySelectorAll('[data-animate]');
    if (!elements.length) return;

    if (!('IntersectionObserver' in window)) {
        elements.forEach(el => el.classList.add('animate-in'));
        return;
    }

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    elements.forEach(el => observer.observe(el));
}

function initNavbarProfileMenu() {
    const profileBtn = document.querySelector('.nav-profile-btn');
    const profileMenu = document.querySelector('.nav-profile-menu');
    if (!profileBtn || !profileMenu) return;

    const toggleMenu = () => {
        const expanded = profileBtn.getAttribute('aria-expanded') === 'true';
        profileBtn.setAttribute('aria-expanded', !expanded);
        profileMenu.classList.toggle('open');
    };

    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    document.addEventListener('click', () => {
        profileBtn.setAttribute('aria-expanded', 'false');
        profileMenu.classList.remove('open');
    });

    profileMenu.addEventListener('click', (e) => e.stopPropagation());
}

function initSmoothSections() {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

function initAuthModal() {
    const body = document.body;
    const isLoggedIn = body.getAttribute('data-logged-in') === '1';
    const backdrop = document.getElementById('login-required-backdrop');

    if (!backdrop) return;

    // Expose helpers globally for other scripts
    window.isUserLoggedIn = () => isLoggedIn;
    window.showLoginRequiredModal = () => {
        backdrop.classList.add('is-visible');
    };
}

function initMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navActions = document.querySelector('.nav-actions');
    const body = document.body;

    if (!toggle || !navMenu) return;

    toggle.addEventListener('click', function() {
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', !isExpanded);
        navMenu.classList.toggle('mobile-open');
        if (navActions) {
            navActions.classList.toggle('mobile-open');
        }
        body.classList.toggle('mobile-menu-active');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!toggle.contains(e.target) && !navMenu.contains(e.target) && 
            (!navActions || !navActions.contains(e.target))) {
            toggle.setAttribute('aria-expanded', 'false');
            navMenu.classList.remove('mobile-open');
            if (navActions) {
                navActions.classList.remove('mobile-open');
            }
            body.classList.remove('mobile-menu-active');
        }
    });

    // Close menu when clicking on a link
    navMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            toggle.setAttribute('aria-expanded', 'false');
            navMenu.classList.remove('mobile-open');
            if (navActions) {
                navActions.classList.remove('mobile-open');
            }
            body.classList.remove('mobile-menu-active');
        });
    });
}

