<?php
session_start();
require_once 'includes/config.php';

$page_title = 'Sign Up';
$additional_css = ['auth.css'];
$additional_js = ['auth.js'];
require_once 'includes/header.php';

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email_raw = trim($_POST['email'] ?? '');
    $email = filter_var($email_raw, FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $full_name = trim($first_name . ' ' . $last_name);
                
                $insert = $pdo->prepare(
                    'INSERT INTO users (first_name, last_name, name, email, password, role)
                     VALUES (:first_name, :last_name, :name, :email, :password, :role)'
                );
                
                $insert->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':name' => $full_name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':role' => 'customer',
                ]);
                
                $message = 'Account created successfully! You can now login.';
            }
        } catch (PDOException $e) {
            $error = 'Error creating account. Please try again later.';
        }
    }
}
?>

<main class="auth-page">
    <!-- Left Visual Panel -->
    <div class="auth-visual">
        <div class="auth-visual-content">
            <div class="auth-visual-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </div>
            <h2>Join the MemoCraft Family</h2>
            <p>Create your account and start exploring our collection of unique, handcrafted souvenirs from around the world.</p>
            
            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <span>Easy checkout process</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 12 20 22 4 22 4 12"></polyline>
                            <rect x="2" y="7" width="20" height="5"></rect>
                            <line x1="12" y1="22" x2="12" y2="7"></line>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                        </svg>
                    </div>
                    <span>Exclusive member deals</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <span>Track your orders easily</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Form Panel -->
    <div class="auth-form-panel">
        <div class="auth-form-wrapper auth-card-animated">
            <!-- Brand -->
            <div class="auth-brand">
                <a href="index.php">
                    <div class="auth-brand-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <span>MemoCraft</span>
                </a>
            </div>
            
            <!-- Header -->
            <div class="auth-header">
                <h1>Create your account</h1>
                <p>Fill in your details to get started</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="signup.php" class="auth-form" id="signup-form" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            required
                            autocomplete="given-name"
                            placeholder="John"
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES); ?>"
                        >
                        <small class="field-error" data-for="first_name"></small>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            required
                            autocomplete="family-name"
                            placeholder="Doe"
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES); ?>"
                        >
                        <small class="field-error" data-for="last_name"></small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="name@example.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>"
                    >
                    <small class="field-error" data-for="email"></small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="6"
                            autocomplete="new-password"
                            placeholder="Min. 6 characters"
                        >
                        <small class="field-error" data-for="password"></small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            minlength="6"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        >
                        <small class="field-error" data-for="confirm_password"></small>
                    </div>
                </div>
                
                <button type="submit" class="auth-btn">Create Account</button>
            </form>
            
            <p class="auth-link">Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </div>
</main>
