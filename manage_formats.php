<?php
session_start();

// Check if user is logged in and has QA or Admin role
require_once 'helpers.php';
requireLogin();
checkRole(['qa', 'admin']); // Only QA and Admin can manage formats

$pdo = getDBConnection();

$success = '';
$error = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    try {
        // Use main connection
        // Get file info before deleting
        // 1. Fetch Main File Info
        $stmt = $pdo->prepare("SELECT title, sop_number, image FROM fileup WHERE id = ?");
        $stmt->execute([$deleteId]);
        $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fileInfo) {
            // 2. Fetch Additional Formats Info
            $stmtFmt = $pdo->prepare("SELECT file_name FROM sop_formats WHERE sop_id = ?");
            $stmtFmt->execute([$deleteId]);
            $formats = $stmtFmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Delete Physical Files (Additional Formats)
            foreach ($formats as $fmt) {
                $fmtPath = 'uploads/sops/' . $fmt['file_name'];
                if (file_exists($fmtPath)) {
                    unlink($fmtPath);
                }
            }

            // 4. Delete Physical File (Main SOP)
            $filePath = 'uploads/sops/' . $fileInfo['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // 5. Delete Database Entries (sop_formats will cascade if FK set, but explicit delete is safer)
            $pdo->prepare("DELETE FROM sop_formats WHERE sop_id = ?")->execute([$deleteId]);
            $pdo->prepare("DELETE FROM fileup WHERE id = ?")->execute([$deleteId]);

            // Log audit trail
            logAudit($pdo, $_SESSION['user_id'], 'Delete SOP', "Deleted SOP: {$fileInfo['sop_number']} and its formats", getClientIP());

            $success = 'SOP and associated formats deleted successfully.';
        } else {
            $error = 'Format not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error deleting SOP: ' . $e->getMessage();
    }
}

// Fetch all SOPs
try {
    // Use the main connection ($pdo) instead of creating a new one
    $stmt = $pdo->query("SELECT id, title, sop_number, image FROM fileup ORDER BY sop_number ASC");
    $sops = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching SOPs: ' . $e->getMessage();
    $sops = [];
}

// Fetch Archived Formats
$archivedFormats = [];
$archivedFormatsError = '';
$checkStatus = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'status'");
$statusColumnExists = $checkStatus && $checkStatus->rowCount() > 0;
$checkSfCreatedAt = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'created_at'");
$sfCreatedAtExists = $checkSfCreatedAt && $checkSfCreatedAt->rowCount() > 0;
$checkFileupCreatedAt = $pdo->query("SHOW COLUMNS FROM fileup LIKE 'created_at'");
$fileupCreatedAtExists = $checkFileupCreatedAt && $checkFileupCreatedAt->rowCount() > 0;

if (!$statusColumnExists) {
    $archivedFormatsError = "Database migration not yet applied. Please execute migrate_format_versioning.sql.";
} else {
    $dateSelect = $sfCreatedAtExists ? "sf.created_at" : ($fileupCreatedAtExists ? "f.created_at" : "NULL");
    try {
        $query = "
            SELECT 
                sf.id,
                sf.sop_id,
                sf.format_name,
                sf.version,
                sf.file_name,
                $dateSelect AS archived_at,
                f.sop_number,
                f.title AS sop_name,
                f.uploaded_by,
                f.department_id,
                d.name AS dept_name
            FROM sop_formats sf
            JOIN fileup f ON sf.sop_id = f.id
            LEFT JOIN departments d ON f.department_id = d.id
            WHERE sf.status = 'archived'
            ORDER BY archived_at DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $archivedFormats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $archivedFormats = [];
        $archivedFormatsError = "Error fetching archived formats: " . htmlspecialchars($e->getMessage());
    }
}
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
    <title>Kopran - Manage SOP Formats</title>
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
                <h2><i class="fas fa-cog text-primary me-2"></i> Manage Formats</h2>
                <div>
                    <a href="upload_sop.php" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-plus me-1"></i> Upload New Format
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
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

            <!-- SOPs Table -->
            <div class="manage-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-folder-open me-2 text-secondary"></i>All Formats</span>
                        <div>
                            <a href="archived_formats.php" class="btn btn-sm btn-outline-danger me-2">
                                <i class="fas fa-archive me-1"></i>Archived Formats
                            </a>
                            <span class="badge bg-secondary"><?php echo count($sops); ?> Documents</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($sops)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Formats found</h5>
                            <p class="text-muted">Upload your first Format to get started</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>SOP Name</th>
                                        <th>Title</th>
                                        <th>File Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sops as $sop): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($sop['sop_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($sop['title']); ?></td>
                                            <td>
                                                <small class="text-muted font-monospace">
                                                    <i class="fas fa-file-pdf me-1 text-danger"></i>
                                                    <?php echo htmlspecialchars($sop['image']); ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="uploads/sops/<?php echo htmlspecialchars($sop['image']); ?>"
                                                    target="_blank" class="btn btn-sm btn-outline-primary me-1" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_sop.php?id=<?php echo $sop['id']; ?>"
                                                    class="btn btn-sm btn-outline-info me-1" title="Edit SOP">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $sop['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this SOP and all its formats?');"
                                                    title="Delete SOP">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>

        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>