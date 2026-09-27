<?php
require_once 'includes/config.php';

$page_title = 'Login';
$additional_css = ['auth.css'];
$additional_js = ['auth.js'];

// Where to go after login. Only relative paths inside this site are allowed,
// so ?redirect=https://evil.com or //evil.com can't send users elsewhere.
$redirect = $_GET['redirect'] ?? '';
if (!is_string($redirect) || !preg_match('#^[A-Za-z0-9_-]+\.php(\?[A-Za-z0-9_=&%.-]*)?$#', $redirect)) {
    $redirect = 'index.php';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_first_name'] = $user['first_name'] ?? $user['name'];
            $_SESSION['user_last_name'] = $user['last_name'] ?? '';
            $_SESSION['user_name'] = trim(($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? ''));
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            $_SESSION['user_profile_image'] = !empty($user['profile_image']) ? $user['profile_image'] : null;

            if ($_SESSION['user_role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $_SESSION['user_name'];
                $_SESSION['admin_email'] = $user['email'];
                header('Location: admin/index.php');
            } else {
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once 'includes/header.php';
?>

<main class="auth-page">
    <!-- Left Visual Panel -->
    <div class="auth-visual">
        <div class="auth-visual-content">
            <div class="auth-visual-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <h2>Welcome Back to MemoCraft</h2>
            <p>Your one-stop destination for handcrafted souvenirs and unique travel keepsakes from around the world.</p>
            
            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>
                    <span>Handcrafted with love & care</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <span>Fast & secure delivery</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <span>100% secure checkout</span>
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
                <h1>Sign in to your account</h1>
                <p>Enter your credentials to access your account</p>
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
            
            <form method="POST" action="login.php?redirect=<?php echo urlencode($redirect); ?>" class="auth-form" id="login-form" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="name@example.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                    <small class="field-error" data-for="email"></small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                    <small class="field-error" data-for="password"></small>
                </div>
                
                <button type="submit" class="auth-btn">Sign In</button>
            </form>
            
            <p class="auth-link">Don't have an account? <a href="signup.php">Create one</a></p>
        </div>
    </div>
</main>
