<?php
session_start();
require_once 'helpers.php';
requireLogin();

// Get user details from session
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? '';
$emp_code = $_SESSION['emp_code'] ?? '';
$department = $_SESSION['department'] ?? '';
$role = $_SESSION['role'] ?? '';
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
    <title>My Profile - Kopran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="css/layout_styles.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .profile-header i {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .profile-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .profile-header .role-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .profile-body {
            padding: 30px;
        }
        .profile-item {
            display: flex;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .profile-item:last-child {
            border-bottom: none;
        }
        .profile-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 20px;
            font-size: 18px;
        }
        .profile-content {
            flex: 1;
        }
        .profile-label {
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .profile-value {
            color: #333;
            font-size: 1.05rem;
            font-weight: 500;
        }
        .btn-back {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <i class="fas fa-user-circle"></i>
                        <h2><?php echo htmlspecialchars($full_name); ?></h2>
                        <div class="role-badge">
                            <?php 
                            $roleDisplay = ucfirst($role);
                            if ($role === 'qa') $roleDisplay = 'QA User';
                            echo htmlspecialchars($roleDisplay); 
                            ?>
                        </div>
                    </div>

                    <div class="profile-body">
                        <div class="profile-item">
                            <div class="profile-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="profile-content">
                                <div class="profile-label">User ID</div>
                                <div class="profile-value"><?php echo htmlspecialchars($user_id); ?></div>
                            </div>
                        </div>

                        <div class="profile-item">
                            <div class="profile-icon">
                                <i class="fas fa-address-card"></i>
                            </div>
                            <div class="profile-content">
                                <div class="profile-label">Employee Code</div>
                                <div class="profile-value"><?php echo htmlspecialchars($emp_code); ?></div>
                            </div>
                        </div>

                        <div class="profile-item">
                            <div class="profile-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="profile-content">
                                <div class="profile-label">Department</div>
                                <div class="profile-value"><?php echo htmlspecialchars($department); ?></div>
                            </div>
                        </div>

                        <div class="profile-item">
                            <div class="profile-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="profile-content">
                                <div class="profile-label">Role</div>
                                <div class="profile-value">
                                    <?php 
                                    $roleDisplay = ucfirst($role);
                                    if ($role === 'qa') $roleDisplay = 'QA User';
                                    if ($role === 'admin') $roleDisplay = 'Administrator';
                                    echo htmlspecialchars($roleDisplay); 
                                    ?>
                                </div>
                            </div>
                        </div>

                        <a href="dashboard.php" class="btn btn-primary btn-back">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
