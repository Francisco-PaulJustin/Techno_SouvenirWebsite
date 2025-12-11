    <div class="auth-modal-backdrop" id="login-required-backdrop" aria-hidden="true">
        <div class="auth-modal-card" role="dialog" aria-modal="true" aria-labelledby="login-required-title">
            <h2 id="login-required-title">You need an account</h2>
            <p>You need to log in or create an account to add items to the cart.</p>
            <div class="auth-modal-actions">
                <a href="login.php" class="btn gradient-btn auth-modal-btn">Login</a>
                <a href="signup.php" class="btn ghost-btn auth-modal-btn">Sign Up</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Your trusted source for unique souvenirs and gifts.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: memocraft@gmail.com</p>
                    <p>Phone: +63 0999 123 456</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/popup.js"></script>
    <script src="assets/js/main.js"></script>
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="assets/js/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

