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
    // Sanitize input
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
            // Check if email already exists (prevent duplicates)
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $full_name = trim($first_name . ' ' . $last_name);
                
                // Insert new user using prepared statement
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
            // Optional: log $e->getMessage() to a file
            $error = 'Error creating account. Please try again later.';
        }
    }
}
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-container auth-card-animated">
            <h1>Sign Up</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
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
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES); ?>"
                        >
                        <small class="field-error" data-for="last_name"></small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
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
                        >
                        <small class="field-error" data-for="confirm_password"></small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary gradient-btn auth-btn">
                    Sign Up
                </button>
            </form>
            
            <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</main>
