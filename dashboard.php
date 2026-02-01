<?php
session_start();
require_once 'helpers.php'; // Required for right_panel.php and other helpers

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get user details from session
$fullName = $_SESSION['full_name'] ?? 'User';
$department = $_SESSION['department'] ?? 'Unknown';
$empCode = $_SESSION['emp_code'] ?? 'N/A';
$userId = $_SESSION['user_id'] ?? 'N/A';
$role = $_SESSION['role'] ?? 'user';

// Role-based content for three roles: user, qa, admin
$roleContent = [
    'user' => [
        'title' => 'User Dashboard',
        'description' => 'View and print Standard Operating Procedures.',
        'badge' => 'User',
        'badge_color' => '#10B981',
        'cards' => [
            ['icon' => 'fa-file-alt', 'title' => 'View Format', 'desc' => 'Browse and view SOPs', 'link' => 'view_sops.php'],
            ['icon' => 'fa-print', 'title' => 'Logs', 'desc' => 'View SOP print history', 'link' => 'print_logs.php'],
            ['icon' => 'fa-history', 'title' => 'Recent Activity', 'desc' => 'View your recent actions', 'link' => '#'],
            ['icon' => 'fa-user-circle', 'title' => 'My Profile', 'desc' => 'View your profile details', 'link' => 'my_profile.php'],
        ]
    ],
    'qa' => [
        'title' => 'QA Dashboard',
        'description' => 'Upload and manage scanned SOPs and their formats.',
        'badge' => 'QA User',
        'badge_color' => '#F59E0B',
        'cards' => [
            ['icon' => 'fa-upload', 'title' => 'Upload Format', 'desc' => 'Upload scanned SOP documents', 'link' => 'upload_sop.php'],
            ['icon' => 'fa-cog', 'title' => 'Manage Formats', 'desc' => 'Edit and manage SOP formats', 'link' => 'manage_formats.php'],
            ['icon' => 'fa-file-alt', 'title' => 'View Format', 'desc' => 'Browse and view all SOPs', 'link' => 'view_sops.php'],
            ['icon' => 'fa-archive', 'title' => 'Archived Formats', 'desc' => 'View archived format versions', 'link' => 'archived_formats.php'],
            ['icon' => 'fa-print', 'title' => 'Logs', 'desc' => 'View SOP print history', 'link' => 'print_logs.php'],
            ['icon' => 'fa-file-upload', 'title' => 'Upload Format Logs', 'desc' => 'View format upload history', 'link' => 'upload_format_logs.php'],
            ['icon' => 'fa-history', 'title' => 'Recent Activity', 'desc' => 'View your recent actions', 'link' => '#'],
            ['icon' => 'fa-user-circle', 'title' => 'My Profile', 'desc' => 'View your profile details', 'link' => 'my_profile.php'],
        ]
    ],
    'admin' => [
        'title' => 'Admin Dashboard',
        'description' => 'Manage users, view audit trail, and system administration.',
        'badge' => 'Administrator',
        'badge_color' => '#EF4444',
        'cards' => [
            ['icon' => 'fa-users-cog', 'title' => 'User Management', 'desc' => 'Create and manage users', 'link' => 'user_management.php'],
            ['icon' => 'fa-clipboard-list', 'title' => 'Audit Trail', 'desc' => 'View system audit logs', 'link' => 'audit_trail.php'],
            ['icon' => 'fa-print', 'title' => 'Logs', 'desc' => 'View SOP print history', 'link' => 'print_logs.php'],
            ['icon' => 'fa-file-upload', 'title' => 'Upload Format Logs', 'desc' => 'View format upload history', 'link' => 'upload_format_logs.php'],
            ['icon' => 'fa-archive', 'title' => 'Archived Formats', 'desc' => 'View archived format versions', 'link' => 'archived_formats.php'],
            ['icon' => 'fa-upload', 'title' => 'Upload Format', 'desc' => 'Upload scanned SOP documents', 'link' => 'upload_sop.php'],
            ['icon' => 'fa-file-alt', 'title' => 'View Format', 'desc' => 'Browse and view all SOPs', 'link' => 'view_sops.php'],
            ['icon' => 'fa-cog', 'title' => 'Manage Formats', 'desc' => 'Edit and manage SOP formats', 'link' => 'manage_formats.php'],
            ['icon' => 'fa-user-circle', 'title' => 'My Profile', 'desc' => 'View your profile details', 'link' => 'my_profile.php'],
        ]
    ],
];

// Get content for current role
$content = $roleContent[$role] ?? $roleContent['user'];

// Check for unauthorized access message
$errorMessage = '';
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $errorMessage = 'You do not have permission to access that page.';
}

// Fetch user's recent activity (last 10 activities)
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT action, details, timestamp, ip_address FROM audit_trail WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
$stmt->execute([$userId]);
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Kopran - Dashboard</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css"> <!-- Common Layout Styles -->
    <style>
        /* Specific Dashboard Styles overrides/additions if needed */
        .dashboard-welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2563eb 100%);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 32px;
            box-shadow: var(--shadow-md);
            border: none;
        }

        .dashboard-welcome-card h3 {
            color: white;
            font-size: 1.75rem;
            margin-bottom: 16px;
            font-family: 'Outfit', sans-serif;
        }

        .welcome-detail {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .welcome-detail i {
            width: 24px;
            margin-right: 8px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 24px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            display: inline-block;
        }

        .dashboard-action-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .dashboard-action-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .card-icon-wrapper {
            width: 56px;
            height: 56px;
            background: rgba(0, 86, 179, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .dashboard-action-card h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .dashboard-action-card p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Recent Activity Styles */
        .activity-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-top: 32px;
        }

        .activity-timeline {
            position: relative;
            padding-left: 40px;
        }

        .activity-item {
            position: relative;
            padding-bottom: 24px;
            border-left: 2px solid #e5e7eb;
            padding-left: 24px;
        }

        .activity-item:last-child {
            border-left: 2px solid transparent;
            padding-bottom: 0;
        }

        .activity-icon {
            position: absolute;
            left: -9px;
            top: 0;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .activity-content {
            background: #f9fafb;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .activity-action {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .activity-details {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .activity-meta {
            display: flex;
            gap: 16px;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .activity-meta i {
            margin-right: 4px;
        }

        .no-activity {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }

        .no-activity i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 16px;
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
            <!-- Unauthorized Alert -->
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Welcome/User Info Card -->
            <div class="dashboard-welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Welcome <?php echo htmlspecialchars($fullName); ?>!</h3>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="welcome-detail">
                                <i class="fas fa-id-badge"></i>
                                <span><?php echo htmlspecialchars($userId); ?></span>
                            </div>
                            <div class="welcome-detail">
                                <i class="fas fa-hashtag"></i>
                                <span><?php echo htmlspecialchars($empCode); ?></span>
                            </div>
                            <div class="welcome-detail">
                                <i class="fas fa-building"></i>
                                <span><?php echo htmlspecialchars($department); ?></span>
                            </div>
                            <div class="welcome-detail">
                                <i class="fas fa-shield-alt"></i>
                                <span><?php echo htmlspecialchars(ucfirst($role)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Access Grid -->
            <h2 class="section-title">Quick Access</h2>
            <div class="row">
                <?php foreach ($content['cards'] as $card): ?>
                    <div class="col-md-6 col-xl-4 mb-4">
                        <?php if ($card['title'] === 'Recent Activity'): ?>
                            <a href="#" class="dashboard-action-card" id="toggleRecentActivity">
                                <div class="card-icon-wrapper">
                                    <i class="fas <?php echo htmlspecialchars($card['icon']); ?>"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($card['title']); ?></h4>
                                <p><?php echo htmlspecialchars($card['desc']); ?></p>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($card['link']); ?>" class="dashboard-action-card">
                                <div class="card-icon-wrapper">
                                    <i class="fas <?php echo htmlspecialchars($card['icon']); ?>"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($card['title']); ?></h4>
                                <p><?php echo htmlspecialchars($card['desc']); ?></p>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Activity Section -->
            <div class="activity-card" id="recentActivitySection" style="display: none;">
                <h2 class="section-title mb-4">
                    <i class="fas fa-history text-primary me-2"></i> Recent Activity
                </h2>
                
                <?php if (empty($recentActivities)): ?>
                    <div class="no-activity">
                        <i class="fas fa-clipboard-list"></i>
                        <p class="mb-0">No recent activity found. Start by viewing or printing Format.</p>
                    </div>
                <?php else: ?>
                    <div class="activity-timeline">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon"></div>
                                <div class="activity-content">
                                    <div class="activity-action">
                                        <?php echo htmlspecialchars($activity['action']); ?>
                                    </div>
                                    <?php if (!empty($activity['details'])): ?>
                                        <div class="activity-details">
                                            <?php echo htmlspecialchars($activity['details']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="activity-meta">
                                        <span>
                                            <i class="far fa-clock"></i>
                                            <?php echo date('d-M-Y H:i:s', strtotime($activity['timestamp'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Panel (Knowledge Sharing) -->
        <?php include 'right_panel.php'; ?>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Recent Activity section
        document.getElementById('toggleRecentActivity')?.addEventListener('click', function(e) {
            e.preventDefault();
            const activitySection = document.getElementById('recentActivitySection');
            if (activitySection.style.display === 'none') {
                activitySection.style.display = 'block';
                // Smooth scroll to the section
                setTimeout(() => {
                    activitySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            } else {
                activitySection.style.display = 'none';
            }
        });
    </script>
</body>

</html>