<?php
session_start();
require_once 'includes/config.php';
$page_title = 'Contact';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message_text = $_POST['message'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($message_text)) {
        // Process contact form (send email or save to database)
        $message = 'Thank you for contacting us! We will get back to you soon.';
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>

<main>
    <section class="contact-section">
        <div class="container">
            <div class="section-heading" data-animate>
                <p class="eyebrow-text">Say Hello</p>
                <h1>We’re here to help with gifting, sourcing, and wholesale.</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="contact-content">
                <div class="contact-info" data-animate>
                    <h2>Get in Touch</h2>
                    <p>Email: <strong>hello@souvenirshop.com</strong></p>
                    <p>Phone: +1 (555) 123-4567</p>
                    <p>Studio: 123 Artisan Lane, Portland, OR</p>
                    <div class="contact-tags">
                        <span>Wholesale</span>
                        <span>Custom Orders</span>
                        <span>Press</span>
                    </div>
                </div>
                
                <form method="POST" action="contact.php" class="contact-form" data-animate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="Tell us how we can help">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Share the details of your project or request"></textarea>
                    </div>
                    
                    <button type="submit" class="btn gradient-btn">Send Message</button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

