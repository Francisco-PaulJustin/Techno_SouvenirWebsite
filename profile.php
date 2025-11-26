<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$page_title = 'Profile';
$body_class = 'profile-page';
$additional_css = ['profile.css'];
$additional_js = ['profile.js'];

$message = '';
$error = '';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, address, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

function refreshUserSession($user)
{
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_profile_image'] = $user['profile_image'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? '';

    if ($form_type === 'profile_update') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error = 'Please fill in the required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check unique email
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $user_id]);
            if ($checkStmt->fetch()) {
                $error = 'This email is already in use.';
            } else {
                $full_name = trim($first_name . ' ' . $last_name);
                $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                if ($updateStmt->execute([$first_name, $last_name, $full_name, $email, $phone, $address, $user_id])) {
                    $user['first_name'] = $first_name;
                    $user['last_name'] = $last_name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                    $user['address'] = $address;
                    refreshUserSession($user);
                    $message = 'Profile updated successfully.';
                } else {
                    $error = 'Unable to update profile. Please try again.';
                }
            }
        }
    } elseif ($form_type === 'password_update') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            $error = 'Please fill in all password fields.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($current_password, $row['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($updateStmt->execute([$hashed_password, $user_id])) {
                    $message = 'Password updated successfully.';
                } else {
                    $error = 'Unable to update password.';
                }
            }
        }
    } elseif ($form_type === 'avatar_update' && isset($_FILES['profile_image'])) {
        $profile_image = $_FILES['profile_image'];

        if ($profile_image['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($profile_image['type'], $allowed_types)) {
                $error = 'Invalid image type. Please upload JPG, PNG, GIF, or WEBP.';
            } else {
                $extension = pathinfo($profile_image['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $user_id . '_' . time() . '.' . strtolower($extension);
                $upload_dir = 'assets/images/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0775, true);
                }
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($profile_image['tmp_name'], $upload_path)) {
                    $updateStmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    if ($updateStmt->execute([$upload_path, $user_id])) {
                        if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                            @unlink($user['profile_image']);
                        }
                        $user['profile_image'] = $upload_path;
                        refreshUserSession($user);
                        $message = 'Profile picture updated.';
                    } else {
                        $error = 'Unable to save profile picture.';
                    }
                } else {
                    $error = 'Upload failed. Please try again.';
                }
            }
        } else {
            $error = 'Please choose a valid image file.';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="profile-main fade-in">
    <div class="container">
        <div class="profile-header glass-card">
            <div class="profile-avatar">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile picture">
                <?php else: ?>
                    <span class="avatar-placeholder"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></span>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="avatar-form" id="avatar-form">
                    <input type="hidden" name="form_type" value="avatar_update">
                    <label class="upload-btn gradient-btn">
                        <input type="file" name="profile_image" accept="image/*" hidden onchange="this.form.submit()">
                        Change Photo
                    </label>
                </form>
            </div>
            <div class="profile-meta">
                <p class="eyebrow-text">Your Account</p>
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <button class="btn ghost-btn" data-scroll-to="#profile-form">Edit Profile</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success slide-up"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error slide-up"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <section class="profile-card glass-card" id="profile-form">
                <header>
                    <div>
                        <p class="eyebrow-text">Profile</p>
                        <h2>Personal Details</h2>
                    </div>
                    <span class="status-pill live-pill">Live</span>
                </header>

                <form method="POST" class="profile-form" novalidate>
                    <input type="hidden" name="form_type" value="profile_update">
                    <div class="form-row">
                        <div class="form-group floating">
                            <label for="profile_first_name">First Name</label>
                            <input type="text" id="profile_first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group floating">
                            <label for="profile_last_name">Last Name</label>
                            <input type="text" id="profile_last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group floating">
                        <label for="profile_email">Email</label>
                        <input type="email" id="profile_email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group floating">
                            <label for="profile_phone">Phone (optional)</label>
                            <input type="tel" id="profile_phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group floating">
                            <label for="profile_address">Address (optional)</label>
                            <textarea id="profile_address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn gradient-btn">Save Changes</button>
                </form>
            </section>

            <section class="profile-card glass-card">
                <header>
                    <div>
                        <p class="eyebrow-text">Security</p>
                        <h2>Password</h2>
                    </div>
                </header>
                <form method="POST" class="profile-form" id="password-form" novalidate>
                    <input type="hidden" name="form_type" value="password_update">
                    <div class="form-group floating">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group floating">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        <div class="form-group floating">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn ghost-btn">Update Password</button>
                </form>
            </section>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

