<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Prevent admins from accessing user profile page
requireCustomer();

$page_title = 'Profile';
$body_class = 'profile-page';
$additional_css = ['profile.css'];
$additional_js = ['profile.js'];

$message = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Fetch user info
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

// FORM PROCESSING
// Handle avatar upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
        $target_dir = "uploads/profiles/";
        // Ensure the directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_file_type = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $unique_file_name = uniqid() . '.' . $image_file_type;
        $target_file = $target_dir . $unique_file_name;
        $upload_ok = 1;
    
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check !== false) {
            $upload_ok = 1;
        } else {
            $error = "File is not an image.";
            $upload_ok = 0;
        }
    
        // Check file size (e.g., 5MB limit)
        if ($_FILES['profile_image']['size'] > 5000000) {
            $error = "Sorry, your file is too large. Max 5MB.";
            $upload_ok = 0;
        }
    
        // Allow certain file formats
        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif") {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $upload_ok = 0;
        }
    
        // Check if $upload_ok is set to 0 by an error
        if ($upload_ok == 0) {
            $error = "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                // Delete old profile image if it exists and is not a default image
                if ($user['profile_image'] && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
    
                $update_image_stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                if ($update_image_stmt->execute([$target_file, $user_id])) {
                    $user['profile_image'] = $target_file;
                    refreshUserSession($user);
                    $message = "Profile image updated successfully!";
                    header('Location: profile.php');
                    exit();
                } else {
                    $error = "Error updating database with new image path.";
                }
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
    
    // FORM PROCESSING
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form_type = $_POST['form_type'] ?? '';
    
        // Update Profile
        if ($form_type === 'profile_update') {
            $first_name = trim($_POST['first_name']);
            $last_name  = trim($_POST['last_name']);
            $email      = trim($_POST['email']);
            $phone      = trim($_POST['phone']);
            $address    = trim($_POST['address']);
    
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $checkStmt->execute([$email, $user_id]);
    
                if ($checkStmt->fetch()) {
                    $error = "Email already in use.";
                } else {
                    $updateStmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=? WHERE id=?");
                    if ($updateStmt->execute([$first_name, $last_name, $email, $phone, $address, $user_id])) {
                        $user['first_name'] = $first_name;
                        $user['last_name'] = $last_name;
                        $user['email'] = $email;
                        $user['phone'] = $phone;
                        $user['address'] = $address;
                        refreshUserSession($user);
                        $message = "Profile updated successfully!";
                        header('Location: profile.php?form_submitted=true&active_section=profile-details-section');
                        exit();
                    }
                }
            }
        }
    
        // Update Password
        elseif ($form_type === 'password_update') {
            $current_password = $_POST['current_password'];
            $new_password     = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
    
            if ($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
            } elseif (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
                $stmt->execute([$user_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!password_verify($current_password, $row['password'])) {
                    $error = "Incorrect current password.";
                } else {
                    $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                    if ($update->execute([$new_hashed, $user_id])) {
                        $message = "Password updated successfully!";
                        header('Location: profile.php?form_submitted=true&active_section=password-section');
                        exit();
                    }
                }
            }
        }
    }

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="profile-main fade-in">
    <div class="container">

        <!-- PROFILE HEADER -->
        <div class="profile-header glass-card">
            <div class="profile-avatar">
                <form id="avatar-form" action="profile.php" method="POST" enctype="multipart/form-data">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image">
                    <?php else: ?>
                        <span class="avatar-placeholder"><?= strtoupper($user['first_name'][0]) ?></span>
                    <?php endif; ?>
                    <label for="profile_image_upload" class="btn ghost-btn upload-btn">Upload Image</label>
                    <input type="file" name="profile_image" id="profile_image_upload" accept="image/*" style="display: none;">
                </form>
            </div>

            <div class="profile-meta">
                <p class="eyebrow-text">Your Account</p>
                <h1><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h1>
                <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                <?php if (!empty($user['phone'])): ?>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['address'])): ?>
                    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($user['address'])) ?></p>
                <?php endif; ?>

                <button class="btn ghost-btn" id="btnShowProfile" data-target-section="profile-details-section">Update Profile</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
    
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- PROFILE GRID (Hidden on load) -->
        <div class="profile-grid" id="profileGrid" style="display: none;">

            <!-- UPDATE PROFILE -->
            <section class="profile-card glass-card" id="profile-details-section">
                <header><h2>Update Profile</h2></header>

                <form method="POST">
                    <input type="hidden" name="form_type" value="profile_update">

                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>

                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>

                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

                    <label>Address</label>
                    <textarea name="address" required><?= htmlspecialchars($user['address']) ?></textarea>

                    <button class="btn gradient-btn">Save Changes</button>
                </form>
            </section>

            <!-- UPDATE PASSWORD -->
            <section class="profile-card glass-card" id="password-section">
                <header><h2>Change Password</h2></header>

                <form method="POST">
                    <input type="hidden" name="form_type" value="password_update">

                    <label>Current Password</label>
                    <input type="password" name="current_password" required>

                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">

                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6">

                    <button class="btn gradient-btn">Update Password</button>
                </form>
            </section>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
