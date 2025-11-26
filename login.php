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

            header('Location: ' . $redirect);
            exit;
        } else {
            // Either user not found or password mismatch
            $error = 'Invalid email or password.';
        }
    }
}
?>

<main class="auth-page fade-in">
    <div class="container">
        <div class="auth-container auth-card-animated">
            <h1>Login</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php?redirect=<?php echo urlencode($redirect); ?>" class="auth-form" id="login-form" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
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
                    >
                    <small class="field-error" data-for="password"></small>
                </div>
                
                <button type="submit" class="btn btn-primary gradient-btn auth-btn">
                    Login
                </button>
            </form>
            
            <p class="auth-link">Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>
</main>

*** End of File