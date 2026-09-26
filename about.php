<?php
session_start();
require_once 'includes/config.php';
$page_title = 'About';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <section class="about-section">
        <div class="container">
            <div class="section-heading" data-animate>
                <p class="eyebrow-text">Our Story</p>
                <h1>Designed with wanderers in mind.</h1>
            </div>
            <div class="about-content" data-animate>
                <p>Welcome to our souvenir shop—your curated destination for keepsakes that celebrate culture, creativity, and cherished memories. Every item is handpicked from artisans around the world, ensuring authenticity and heart in every piece.</p>
                <div class="about-grid">
                    <div class="card">
                        <h2>Crafted with Purpose</h2>
                        <p>We collaborate directly with makers and boutique studios so you can gift sustainable, small-batch souvenirs that leave a lasting impression.</p>
                    </div>
                    <div class="card">
                        <h2>Why Travelers Love Us</h2>
                        <ul>
                            <li>Curated collections updated monthly</li>
                            <li>Ethically sourced artisan goods</li>
                            <li>Worldwide shipping & premium packaging</li>
                            <li>Concierge support 7 days a week</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

