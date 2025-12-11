<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$page_title = 'Manage Users';
$message = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'] ?? null;
        if ($user_id && $user_id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $message = 'User deleted successfully!';
            } else {
                $error = 'Error deleting user.';
            }
        } else {
            $error = 'Cannot delete your own account.';
        }
    } elseif (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? '';
        if ($user_id && $role) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$role, $user_id])) {
                $message = 'User role updated successfully!';
            } else {
                $error = 'Error updating user role.';
            }
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by role
$admin_count = 0;
$customer_count = 0;
foreach ($users as $user) {
    if ($user['role'] === 'admin') $admin_count++;
    else $customer_count++;
}

require_once 'includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php require_once 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span class="material-icons-round">people</span>
                    Manage Users
                </h1>
                <p class="page-subtitle">View and manage user accounts</p>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <span class="material-icons-round">check_circle</span>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <span class="material-icons-round">error</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- User Stats -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Users</h3>
                    <div class="stat-icon purple">
                        <span class="material-icons-round">people</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo count($users); ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Customers</h3>
                    <div class="stat-icon blue">
                        <span class="material-icons-round">person</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo $customer_count; ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Administrators</h3>
                    <div class="stat-icon yellow">
                        <span class="material-icons-round">admin_panel_settings</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo $admin_count; ?></p>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Users</h2>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <span class="material-icons-round">people</span>
                                        <h3>No users found</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="cell-id">#<?php echo $user['id']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--clr-secondary); display: grid; place-items: center; color: var(--clr-primary); font-weight: 600;">
                                                <?php echo strtoupper(substr($user['first_name'] ?? $user['name'] ?? 'U', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 500;">
                                                    <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? $user['name'] ?? '')); ?>
                                                </div>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span style="font-size: 0.75rem; color: var(--clr-primary);">(You)</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <form method="POST" action="manage_users.php" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="btn btn-sm btn-outline" style="padding: 0.35rem 0.75rem; border-radius: 6px;" 
                                                    onchange="this.form.submit()" 
                                                    <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td class="text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="cell-actions">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="manage_users.php" style="display: inline;" id="delete-user-form-<?php echo $user['id']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="button" name="delete_user" class="btn btn-danger btn-sm" title="Delete User"
                                                        onclick="handleDeleteUser(<?php echo $user['id']; ?>, 'Are you sure you want to delete this user? This action cannot be undone.');">
                                                    <span class="material-icons-round">delete</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.8rem;">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<script>
// Handle delete user with custom confirm
async function handleDeleteUser(userId, message) {
    const confirmed = await customConfirm(message);
    if (confirmed) {
        document.getElementById('delete-user-form-' + userId).submit();
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
