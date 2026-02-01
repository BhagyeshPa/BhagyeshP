<?php
session_start();
require_once 'helpers.php'; // Includes database connection and helper functions

// Enforce login and check role
requireLogin();
checkRole(['admin', 'qa']); // Only Admin and QA can edit SOPs

$error = '';
$success = '';
$sop = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_formats.php");
    exit;
}

$sopId = (int) $_GET['id'];
$pdo = getDBConnection();

// Fetch SOP Details
$stmt = $pdo->prepare("SELECT * FROM fileup WHERE id = ?");
$stmt->execute([$sopId]);
$sop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sop) {
    die("Format not found.");
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Update Metadata
    if (isset($_POST['update_metadata'])) {
        $title = trim($_POST['title']);
        $sopNumber = trim($_POST['sop_number']);
        $deptId = trim($_POST['department']);

        if (!empty($title) && !empty($sopNumber)) {
            $stmt = $pdo->prepare("UPDATE fileup SET title = ?, sop_number = ?, department_id = ? WHERE id = ?");
            if ($stmt->execute([$title, $sopNumber, $deptId, $sopId])) {
                $success = "SOP details updated successfully.";
                // Refresh data
                $sop['title'] = $title;
                $sop['sop_number'] = $sopNumber;
                $sop['department_id'] = $deptId;
            } else {
                $error = "Failed to update details.";
            }
        } else {
            $error = "Title and SOP Number are required.";
        }
    }

    // 2. Replace Main File
    if (isset($_FILES['new_main_file']) && $_FILES['new_main_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['new_main_file'];
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];

        if (in_array($file['type'], $allowedTypes)) {
            $uploadDir = 'uploads/sops/';
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFilename = $sop['sop_number'] . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $uniqueFilename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Delete old file
                if (!empty($sop['image']) && file_exists($uploadDir . $sop['image'])) {
                    unlink($uploadDir . $sop['image']);
                }

                // Update DB
                $stmt = $pdo->prepare("UPDATE fileup SET image = ? WHERE id = ?");
                $stmt->execute([$uniqueFilename, $sopId]);
                $sop['image'] = $uniqueFilename;
                $success = "Main SOP file replaced successfully.";

                logAudit($pdo, $_SESSION['user_id'], 'Update SOP', "Replaced Main File for SOP: {$sop['sop_number']}", getClientIP());
            } else {
                $error = "Failed to upload new file.";
            }
        } else {
            $error = "Invalid file type.";
        }
    }

    // 3. Add or Replace Format
    if (isset($_POST['add_format'])) {
        $fmtName = trim($_POST['format_name']);
        $formatNumber = isset($_POST['format_number']) ? trim($_POST['format_number']) : '';
        
        if (!empty($fmtName) && isset($_FILES['format_file']) && $_FILES['format_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['format_file'];
            $uploadDir = 'uploads/sops/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFilename = $sop['sop_number'] . '_fmt_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $uniqueFilename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Check if format_number column exists
                $checkFormatNumber = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'format_number'");
                $formatNumberExists = $checkFormatNumber && $checkFormatNumber->rowCount() > 0;
                
                // Check if format with same name exists and is active
                $checkStmt = $pdo->prepare("SELECT id, version FROM sop_formats WHERE sop_id = ? AND format_name = ? AND status = 'active' LIMIT 1");
                $checkStmt->execute([$sopId, $fmtName]);
                $existingFmt = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existingFmt) {
                    // Archive old format
                    $stmt = $pdo->prepare("UPDATE sop_formats SET status = 'archived' WHERE id = ?");
                    $stmt->execute([$existingFmt['id']]);
                    
                    // Calculate new version
                    $oldVersion = floatval($existingFmt['version']);
                    $newVersion = $oldVersion + 1.0;
                    
                    // Insert new version as active
                    if ($formatNumberExists) {
                        $newStmt = $pdo->prepare("INSERT INTO sop_formats (sop_id, format_name, format_number, file_name, version, status) VALUES (?, ?, ?, ?, ?, 'active')");
                        $newStmt->execute([$sopId, $fmtName, $formatNumber, $uniqueFilename, (string)$newVersion]);
                    } else {
                        $newStmt = $pdo->prepare("INSERT INTO sop_formats (sop_id, format_name, file_name, version, status) VALUES (?, ?, ?, ?, 'active')");
                        $newStmt->execute([$sopId, $fmtName, $uniqueFilename, (string)$newVersion]);
                    }
                    
                    $success = "Format '$fmtName' replaced successfully. Version updated to " . (string)$newVersion . ". Old version archived.";
                    logAudit($pdo, $_SESSION['user_id'], 'Replace Format', "Replaced format '$fmtName' in SOP: {$sop['sop_number']} (v" . (string)$newVersion . ")", getClientIP());
                } else {
                    // Add new format with version 1.0
                    if ($formatNumberExists) {
                        $newStmt = $pdo->prepare("INSERT INTO sop_formats (sop_id, format_name, format_number, file_name, version, status) VALUES (?, ?, ?, ?, '1.0', 'active')");
                        $newStmt->execute([$sopId, $fmtName, $formatNumber, $uniqueFilename]);
                    } else {
                        $newStmt = $pdo->prepare("INSERT INTO sop_formats (sop_id, format_name, file_name, version, status) VALUES (?, ?, ?, '1.0', 'active')");
                        $newStmt->execute([$sopId, $fmtName, $uniqueFilename]);
                    }
                    
                    $success = "New format '$fmtName' (v1.0) added successfully.";
                    logAudit($pdo, $_SESSION['user_id'], 'Add Format', "Added format '$fmtName' to SOP: {$sop['sop_number']}", getClientIP());
                }
            } else {
                $error = "Failed to upload format file.";
            }
        } else {
            $error = "Format Name and File are required.";
        }
    }

    // 4. Delete Format
    if (isset($_POST['delete_format_id'])) {
        $fmtId = (int) $_POST['delete_format_id'];
        // Fetch file info to delete physical file
        $stmt = $pdo->prepare("SELECT file_name FROM sop_formats WHERE id = ? AND sop_id = ?");
        $stmt->execute([$fmtId, $sopId]);
        $fmt = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fmt) {
            $stmt = $pdo->prepare("DELETE FROM sop_formats WHERE id = ?");
            if ($stmt->execute([$fmtId])) {
                if (file_exists('uploads/sops/' . $fmt['file_name'])) {
                    unlink('uploads/sops/' . $fmt['file_name']);
                }
                $success = "Format deleted successfully.";
                logAudit($pdo, $_SESSION['user_id'], 'Delete Format', "Deleted format from SOP: {$sop['sop_number']}", getClientIP());
            }
        }
    }
}

// Fetch Departments
$deptStmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name ASC");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Existing Formats (active only)
$stmtFmt = $pdo->prepare("SELECT id, format_name, file_name, version, status FROM sop_formats WHERE sop_id = ? AND status = 'active' ORDER BY format_name ASC");
$stmtFmt->execute([$sopId]);
$formats = $stmtFmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Archived Formats
$stmtArchived = $pdo->prepare("SELECT id, format_name, file_name, version, status FROM sop_formats WHERE sop_id = ? AND status = 'archived' ORDER BY format_name ASC");
$stmtArchived->execute([$sopId]);
$archivedFormats = $stmtArchived->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Edit SOP -
        <?php echo htmlspecialchars($sop['sop_number']); ?>
    </title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css">
    <style>
        .page-header h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-primary);
        }

        .edit-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
        }

        .edit-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            padding: 15px 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .edit-card .card-body {
            padding: 20px;
        }

        .format-table td {
            vertical-align: middle;
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
                <h2><i class="fas fa-edit text-primary me-2"></i> Edit Format</h2>
                <a href="manage_formats.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Manage Formats</a>
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

            <div class="row">
                <!-- Full Width: Format Management -->
                <div class="col-lg-8 offset-lg-2">
                    <!-- Metadata Editor -->
                    <div class="edit-card">
                        <div class="card-header"><i class="fas fa-info-circle me-2"></i> Basic Details</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="update_metadata" value="1">
                                <div class="mb-3">
                                    <label class="form-label">SOP Name</label>
                                    <input type="text" class="form-control" name="sop_number" id="sopNumber"
                                        value="<?php echo htmlspecialchars($sop['sop_number']); ?>" required>
                                    <small class="text-muted">Type SOP number to auto-fetch title</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SOP Title</label>
                                    <input type="text" class="form-control" name="title" id="sopTitle"
                                        value="<?php echo htmlspecialchars($sop['title']); ?>" required>
                                    <small class="text-success d-none" id="fetchSuccess"><i class="fas fa-check-circle"></i> Title fetched</small>
                                    <small class="text-danger d-none" id="fetchError"><i class="fas fa-exclamation-circle"></i> Format not found</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" name="department" required>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>" <?php echo ($dept['id'] == $sop['department_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>
                                    Update Details</button>
                            </form>
                        </div>
                    </div>

                    <!-- Format Management -->
                    <div class="edit-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-copy me-2"></i> Format Management</span>
                        </div>
                        <div class="card-body">
                            <!-- Add/Replace Format Form -->
                            <div class="mb-4 p-3 border rounded bg-light">
                                <h6 class="mb-3 text-primary"><i class="fas fa-plus-circle me-2"></i>Add or Replace Format</h6>
                                <p class="text-muted small mb-3">Upload a format with an existing name to replace it. Old version will be archived.</p>
                                <form method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                                    <input type="hidden" name="add_format" value="1">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Format Number</label>
                                        <input type="text" class="form-control form-control-sm" name="format_number"
                                            placeholder="e.g. F-001" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Format Name</label>
                                        <input type="text" class="form-control form-control-sm" name="format_name"
                                            placeholder="e.g. Checklist" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">File</label>
                                        <input type="file" class="form-control form-control-sm" name="format_file"
                                            required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-success w-100"><i
                                                class="fas fa-plus"></i></button>
                                    </div>
                                </form>
                            </div>

                            <!-- List Existing Formats -->
                            <h6 class="mb-3 text-secondary">Active Formats</h6>
                            <?php if (empty($formats)): ?>
                                <p class="text-muted small text-center py-3 border rounded">No active formats.
                                </p>
                            <?php else: ?>
                                <table class="table table-sm table-hover format-table">
                                    <tbody>
                                        <?php foreach ($formats as $fmt): ?>
                                            <tr>
                                                <td><i class="fas fa-file me-2 text-muted"></i>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($fmt['format_name']); ?></strong>
                                                        <?php if (!empty($fmt['format_number'])): ?>
                                                        <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($fmt['format_number']); ?></span>
                                                        <?php endif; ?>
                                                        <span class="badge bg-info ms-2">v<?php echo htmlspecialchars($fmt['version']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="print_sop.php?file=<?php echo urlencode($fmt['file_name']); ?>&title=<?php echo urlencode($fmt['format_name']); ?>"
                                                        target="_blank" class="btn btn-sm btn-link text-secondary p-0 me-2"
                                                        title="View"><i class="fas fa-eye"></i></a>
                                                    <form method="POST" style="display:inline;"
                                                        onsubmit="return confirm('Delete this format?');">
                                                        <input type="hidden" name="delete_format_id"
                                                            value="<?php echo $fmt['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                                            title="Delete"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                            <!-- Archived Formats Section -->
                            <hr class="my-4">
                            <h6 class="mb-3 text-secondary"><i class="fas fa-archive me-2"></i>Archived Versions</h6>
                            <?php if (empty($archivedFormats)): ?>
                                <p class="text-muted small text-center py-3 border rounded">No archived format versions.
                                </p>
                            <?php else: ?>
                                <table class="table table-sm table-hover format-table">
                                    <tbody>
                                        <?php foreach ($archivedFormats as $fmt): ?>
                                            <?php 
                                                $filePath = 'uploads/sops/' . $fmt['file_name'];
                                                $fileExists = file_exists($filePath);
                                            ?>
                                            <tr class="text-muted">
                                                <td><i class="fas fa-file-archive me-2"></i>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($fmt['format_name']); ?></strong>
                                                        <span class="badge bg-secondary ms-2">v<?php echo htmlspecialchars($fmt['version']); ?></span>
                                                        <span class="badge bg-warning ms-1">Archived</span>
                                                        <?php if (!$fileExists): ?>
                                                            <span class="badge bg-danger ms-1">File Missing</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($fileExists): ?>
                                                        <a href="print_sop.php?file=<?php echo urlencode($fmt['file_name']); ?>&title=<?php echo urlencode($fmt['format_name']); ?>"
                                                            target="_blank" class="btn btn-sm btn-link text-secondary p-0 me-2"
                                                            title="View"><i class="fas fa-eye"></i></a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-link text-secondary p-0 me-2" disabled title="File not found">
                                                            <i class="fas fa-eye-slash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fetch SOP title when SOP number is typed
        let fetchTimeout;
        const sopNumberInput = document.getElementById('sopNumber');
        const sopTitleInput = document.getElementById('sopTitle');
        const fetchSuccess = document.getElementById('fetchSuccess');
        const fetchError = document.getElementById('fetchError');
        const originalNumber = sopNumberInput.value;

        sopNumberInput.addEventListener('input', function() {
            clearTimeout(fetchTimeout);
            fetchSuccess.classList.add('d-none');
            fetchError.classList.add('d-none');

            const sopNumber = this.value.trim();
            
            // Only fetch if number changed and has at least 3 characters
            if (sopNumber.length >= 3 && sopNumber !== originalNumber) {
                fetchTimeout = setTimeout(() => {
                    fetch('get_sop_by_number.php?sop_number=' + encodeURIComponent(sopNumber))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.sop) {
                                sopTitleInput.value = data.sop.title;
                                fetchSuccess.classList.remove('d-none');
                                setTimeout(() => fetchSuccess.classList.add('d-none'), 3000);
                            } else {
                                fetchError.classList.remove('d-none');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching SOP:', error);
                        });
                }, 800); // Wait 800ms after user stops typing
            }
        });
    </script>
</body>

</html>