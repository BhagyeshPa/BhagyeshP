<?php
session_start();

// Check if user is logged in and has Admin role
require_once 'helpers.php';
requireLogin();
checkRole(['admin']); // Only Admin can manage users

$pdo = getDBConnection();

$success = '';
$error = '';

// Handle create user
if (isset($_POST['create_user'])) {
    $fullName = trim($_POST['full_name']);
    $userId = trim($_POST['user_id']);
    $empCode = trim($_POST['emp_code']);
    $department = trim($_POST['department']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($fullName) || empty($userId) || empty($empCode) || empty($department) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } else {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ? OR emp_code = ?");
            $stmt->execute([$userId, $empCode]);

            if ($stmt->rowCount() > 0) {
                $error = 'User ID or Employee Code already exists.';
            } else {
                // Store password as plain text
                $stmt = $pdo->prepare("INSERT INTO users (full_name, user_id, emp_code, department, password, role, password_changed_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$fullName, $userId, $empCode, $department, $password, $role]);

                logAudit(
                    $pdo,
                    $_SESSION['user_id'],
                    'Create User',
                    "Created user: $userId ($fullName) with role: $role",
                    getClientIP()
                );

                $success = "User created successfully!";
            }
        } catch (PDOException $e) {
            $error = 'Error creating user: ' . $e->getMessage();
        }
    }
}

// Handle reset password
if (isset($_POST['reset_password'])) {
    $targetUserId = (int) $_POST['target_user_id'];
    $newPassword = trim($_POST['new_password']);

    if (empty($newPassword)) {
        $error = 'Password cannot be empty.';
    } else {
        try {
            // Store password as plain text
            $stmt = $pdo->prepare("UPDATE users SET password = ?, password_changed_at = NOW(), failed_login_attempts = 0, account_locked = FALSE WHERE id = ?");
            $stmt->execute([$newPassword, $targetUserId]);

            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE id = ?");
            $stmt->execute([$targetUserId]);
            $targetUser = $stmt->fetchColumn();

            logAudit(
                $pdo,
                $_SESSION['user_id'],
                'Reset Password',
                "Reset password for user: $targetUser",
                getClientIP()
            );

            $success = "Password reset successfully!";
        } catch (PDOException $e) {
            $error = 'Error resetting password: ' . $e->getMessage();
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        // Apply dark mode immediately before page renders to prevent flash
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopran - User Management</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css"> <!-- Common Layout Styles -->
    <style>
        .page-header h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-primary);
        }

        .manage-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .manage-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .manage-card .card-body {
            padding: 24px;
        }

        .table thead th {
            font-weight: 600;
            background: #f1f5f9;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table tbody td {
            vertical-align: middle;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
        }

        .role-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .role-user {
            background: #d1fae5;
            color: #065f46;
        }

        .role-qa {
            background: #fed7aa;
            color: #92400e;
        }

        .role-admin {
            background: #fecaca;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Header -->
        <?php include 'header.php'; ?>

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="app-main">
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                <h2><i class="fas fa-users-cog text-primary me-2"></i> User Management</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Create User Form -->
            <div class="manage-card">
                <div class="card-header">
                    <i class="fas fa-user-plus me-2 text-primary"></i>Create New User
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">User ID *</label>
                                <input type="text" class="form-control" name="user_id" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Employee Code *</label>
                                <input type="text" class="form-control" name="emp_code" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Department *</label>
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="IT">IT</option>
                                    <option value="Q.A.">Q.A.</option>
                                    <option value="Q.C.">Q.C.</option>
                                    <option value="P&G">P&G</option>
                                    <option value="RA">RA</option>
                                    <option value="WAREHOUSE">WAREHOUSE</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Role *</label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="user">User</option>
                                    <option value="qa">QA</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small text-muted fw-bold">Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" name="create_user" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users List -->
            <div class="manage-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2 text-secondary"></i>All Users</span>
                        <span class="badge bg-secondary"><?php echo count($users); ?> Users</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>User ID</th>
                                    <th>Emp Code</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['emp_code']); ?></td>
                                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                                        <td>
                                            <span
                                                class="role-badge role-<?php echo htmlspecialchars($user['role'] ?? 'user'); ?>">
                                                <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'user')); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#resetModal<?php echo $user['id']; ?>">
                                                <i class="fas fa-key"></i> Reset
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Reset Password Modal -->
                                    <div class="modal fade" id="resetModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reset Password -
                                                        <?php echo htmlspecialchars($user['full_name']); ?></h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="target_user_id"
                                                            value="<?php echo $user['id']; ?>">
                                                        <label class="form-label">New Password</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i
                                                                    class="fas fa-lock"></i></span>
                                                            <input type="password" class="form-control" name="new_password"
                                                                required>
                                                        </div>
                                                        <small class="text-muted mt-2 d-block">This will reset failed login
                                                            attempts and unlock the account.</small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="reset_password"
                                                            class="btn btn-warning">Reset Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>