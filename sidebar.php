<aside class="app-sidebar">
    <nav>
        <a href="dashboard.php"
            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <a href="view_sops.php"
            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'view_sops.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-pdf"></i> View Formats
        </a>

        <?php if (in_array($_SESSION['role'] ?? '', ['qa', 'admin'])): ?>
            <a href="manage_formats.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_formats.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder-open"></i> Manage Formats
                <!-- manage_formats was missing -->
            </a>
            <a href="upload_sop.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload_sop.php' ? 'active' : ''; ?>">
                <i class="fas fa-upload"></i> Upload Format
            </a>
            <a href="archived_formats.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'archived_formats.php' ? 'active' : ''; ?>">
                <i class="fas fa-archive"></i> Archived Formats
            </a>
            <a href="manage_tips.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_tips.php' ? 'active' : ''; ?>">
                <i class="fas fa-lightbulb"></i> Manage Tips
            </a>
            <a href="audit_trail.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'audit_trail.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Audit Trail
                <!-- audit_trail was missing, available for QA/Admin -->
            </a>
            <a href="upload_format_logs.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload_format_logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-upload"></i> Upload Format Logs
            </a>
        <?php endif; ?>

        <!-- Print Logs available for all users -->
        <a href="print_logs.php"
            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'print_logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-print"></i> Logs
        </a>

        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="user_management.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user_management.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> Users
            </a>
        <?php endif; ?>

        <a href="my_profile.php"
            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle"></i> My Profile
        </a>
    </nav>
</aside>