<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
$pdo = capistra_pdo();
require_once dirname(__DIR__, 2) . '/protect/session_check.php';

// Verify database connection exists
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection failed. Please check your configuration.");
}

// A random temporary password is generated on reset and never displayed.
// The operator must communicate it out-of-band and the user must change it.

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete_admin'])) {
            // Delete admin
            $stmt = $pdo->prepare("DELETE FROM adminusers WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = "Admin deleted successfully!";
        } elseif (isset($_POST['reset_password'])) {
            // Reset password
            $tempPassword = bin2hex(random_bytes(6));
            $hashed_password = password_hash($tempPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE adminusers SET password = ?, failed_attempts = 0, locked_until = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $_POST['id']]);
            // The temporary password is intentionally NOT displayed or logged.
            $_SESSION['message'] = "Password reset. Communicate the temporary password out-of-band.";
        } elseif (isset($_POST['update_admin'])) {
            // Update admin details
            $stmt = $pdo->prepare("UPDATE adminusers SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $_POST['id']
            ]);
            $_SESSION['message'] = "Admin updated successfully!";
        } elseif (isset($_POST['add_admin'])) {
            // Add new admin
            $tempPassword = bin2hex(random_bytes(6));
            $hashed_password = password_hash($tempPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO adminusers (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $hashed_password
            ]);
            // The temporary password is intentionally NOT displayed or logged.
            $_SESSION['message'] = "New admin added. Communicate the temporary password out-of-band.";
        }
        
        header('Location: admin_data.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header('Location: admin_data.php');
        exit();
    }
}

// Fetch all admins using prepared statement
try {
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM adminusers ORDER BY created_at DESC");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching admin data: " . $e->getMessage());
}
include('../head/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .action-btns .btn { margin: 2px; }
    </style>
</head>
<body><div class="content-wrapper">
    <div class="container mt-4">
        <h2>Admin Management</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Add New Admin Form -->
        <div class="card mb-4">
            <div class="card-header"><h5>Add New Admin</h5></div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="col-md-4">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
                        </div>
                    </div>
                </form>
                <small class="text-muted">A random temporary password is generated and must be changed on first login.</small>
            </div>
        </div>
        
        <!-- Admins Table -->
        <?php if (!empty($admins)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $index => $admin): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($admin['username']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td><?= htmlspecialchars($admin['created_at']) ?></td>
                                <td class="action-btns">
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                        data-bs-target="#editModal<?= $admin['id'] ?>">
                                        Edit
                                    </button>
                                    
                                    <!-- Reset Password -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                        <button type="submit" name="reset_password" class="btn btn-sm btn-info"
                                            onclick="return confirm('Reset password to default?')">
                                            Reset Password
                                        </button>
                                    </form>
                                    
                                    <!-- Delete Button -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                        <button type="submit" name="delete_admin" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Permanently delete this admin?')">
                                            Delete
                                        </button>
                                    </form>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $admin['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Admin #<?= $index + 1 ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <input type="text" name="username" class="form-control" 
                                                                value="<?= htmlspecialchars($admin['username']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" name="email" class="form-control" 
                                                                value="<?= htmlspecialchars($admin['email']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_admin" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No admin users found.</div>
        <?php endif; ?>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include('../head/footer.php');
?>