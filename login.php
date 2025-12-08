<?php
session_start();
require_once 'includes/config.php';

$page_title = 'Login';
$additional_css = ['auth.css'];
$additional_js = ['auth.js'];
require_once 'includes/header.php';

// Default redirect target
$redirect = isset($_GET['redirect']) && $_GET['redirect'] !== '' ? $_GET['redirect'] : 'index.php';

// Normalize redirect to avoid open redirect issues (only allow local paths)
if (strpos($redirect, '://') !== false) {
    $redirect = 'index.php';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim and sanitize basic input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Look up the user using a prepared statement
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Successful login – regenerate session ID to prevent fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_first_name'] = $user['first_name'] ?? $user['name'];
            $_SESSION['user_last_name'] = $user['last_name'] ?? '';
            $_SESSION['user_name'] = trim(($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? ''));
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            $_SESSION['user_profile_image'] = !empty($user['profile_image']) ? $user['profile_image'] : null;

            // Set admin specific session variables if the user is an admin
            if ($_SESSION['user_role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $_SESSION['user_name'];
                $_SESSION['admin_email'] = $user['email'];
            }

            // Redirect based on user role
            if ($_SESSION['user_role'] === 'admin') {
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
?>

<main class="auth-page fade-in">
    <div class="auth-container auth-card-animated">
        <!-- Logo/Brand -->
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
        </div>
        
        <h1>Welcome Back</h1>
        <p class="auth-subtitle">Sign in to continue to your account</p>
        
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
                    placeholder="Enter your email"
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
            
            <button type="submit" class="auth-btn">
                Sign In
            </button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="signup.php">Create one</a></p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
