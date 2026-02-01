<?php
session_start();
require_once 'helpers.php'; // Includes database connection and helper functions

// Enforce login and check role
requireLogin();
checkRole(['admin', 'qa']); // Only Admin and QA can upload SOPs

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $title = trim($_POST['title']);
    $department_id = trim($_POST['department']);
    $sopNumber = trim($_POST['sop_number']);

    // File upload handling for MAIN SOP (optional now)
    $file = $_FILES['sop_file'] ?? null;

    // Basic validation
    if (empty($title) || empty($department_id) || empty($sopNumber)) {
        $error = 'Please fill in all required fields (SOP Number, Title, Department).';
    } elseif ($file && $file['error'] !== UPLOAD_ERR_OK && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = 'Error uploading main file. Error code: ' . $file['error'];
    } else {
        // File type validation (allow PDF, DOC, DOCX, Images) - only if file is uploaded
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        if ($file && !empty($file['name']) && !in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only PDF, DOC, DOCX, JPG, and PNG are allowed.';
        } else {
            // Check for existing SOP Number
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM fileup WHERE sop_number = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$sopNumber]);
            $existingSop = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Process Main File Upload
            $uploadDir = 'uploads/sops/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uniqueFilename = '';
            $targetPath = '';
            $mainFileUploaded = false;

            // Only process main file if uploaded
            if ($file && !empty($file['name'])) {
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueFilename = $sopNumber . '_' . time() . '.' . $fileExtension;
                $targetPath = $uploadDir . $uniqueFilename;
                $mainFileUploaded = move_uploaded_file($file['tmp_name'], $targetPath);
            }

            if (!$file || empty($file['name']) || $mainFileUploaded) {
                try {
                    $pdo->beginTransaction();

                    // If old version exists, archive it
                    if ($existingSop) {
                        $archiveStmt = $pdo->prepare("UPDATE fileup SET status = 'archived' WHERE id = ?");
                        $archiveStmt->execute([$existingSop['id']]);
                        logAudit($pdo, $_SESSION['user_id'], 'Archive SOP', "Archived old version of SOP: $sopNumber", getClientIP());
                    }

                    // 1. Insert new SOP version into fileup (image can be empty if no main file)
                    $imageField = $uniqueFilename ?: ''; // Use empty string if no main file
                    $stmt = $pdo->prepare("INSERT INTO fileup (title, sop_number, image, department_id, status) VALUES (?, ?, ?, ?, 'active')");
                    $stmt->execute([$title, $sopNumber, $imageField, $department_id]);
                    $sopId = $pdo->lastInsertId();

                    // 2. Insert Additional Formats
                    if (isset($_POST['format_name']) && is_array($_POST['format_name'])) {
                        // Check if format_number column exists
                        $checkFormatNumber = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'format_number'");
                        $formatNumberExists = $checkFormatNumber && $checkFormatNumber->rowCount() > 0;
                        
                        if ($formatNumberExists) {
                            $stmtFormat = $pdo->prepare("INSERT INTO sop_formats (sop_id, format_name, format_number, file_name, version, status) VALUES (?, ?, ?, ?, '1.0', 'active')");
                        } else {
                            $stmtFormat = $pdo->prepare("INSERT INTO sop_formats (sop_id, format_name, file_name, version, status) VALUES (?, ?, ?, '1.0', 'active')");
                        }

                        foreach ($_POST['format_name'] as $index => $formatName) {
                            $formatName = trim($formatName);
                            $formatNumber = isset($_POST['format_number'][$index]) ? trim($_POST['format_number'][$index]) : '';
                            
                            // Check if corresponding file exists and has no error
                            if (!empty($formatName) && isset($_FILES['format_file']['name'][$index]) && $_FILES['format_file']['error'][$index] === UPLOAD_ERR_OK) {

                                $formatFileTmp = $_FILES['format_file']['tmp_name'][$index];
                                $formatFileName = $_FILES['format_file']['name'][$index];
                                $formatFileExt = pathinfo($formatFileName, PATHINFO_EXTENSION);

                                // Generate unique name for format file
                                $uniqueFormatFilename = $sopNumber . '_fmt_' . ($index + 1) . '_' . time() . '.' . $formatFileExt;
                                $formatTargetPath = $uploadDir . $uniqueFormatFilename;

                                if (move_uploaded_file($formatFileTmp, $formatTargetPath)) {
                                    if ($formatNumberExists) {
                                        $stmtFormat->execute([$sopId, $formatName, $formatNumber, $uniqueFormatFilename]);
                                    } else {
                                        $stmtFormat->execute([$sopId, $formatName, $uniqueFormatFilename]);
                                    }
                                    logAudit($pdo, $_SESSION['user_id'], 'Upload Format', "Uploaded Format '$formatName' for SOP: $sopNumber", getClientIP());
                                }
                            }
                        }
                    }

                    // Log SOP Upload to audit trail
                    $uploadType = $mainFileUploaded ? "with main file" : "formats only";
                    logAudit($pdo, $_SESSION['user_id'], 'Upload SOP', "Uploaded SOP: $sopNumber - $title ($uploadType)" . ($existingSop ? " (New Version)" : ""), getClientIP());

                    $pdo->commit();
                    $success = "SOP and formats uploaded successfully!" . ($existingSop ? " Old version has been archived." : "");
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Database error: ' . $e->getMessage();
                    // Clean up main file if DB insert failed
                    if (file_exists($targetPath))
                        unlink($targetPath);
                }
            } else {
                $error = 'Failed to upload main file.';
            }
        }
    }
}

// Fetch departments for the dropdown
$pdo = getDBConnection();
$deptStmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name ASC");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Kopran - Upload SOP</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css">
    <style>
        .page-header h2 { font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text-primary); }
        .upload-card { background: white; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); overflow: hidden; }
        .upload-card .card-header { background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 20px 24px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; }
        .upload-card .card-body { padding: 32px; }
        .form-label { font-weight: 500; color: var(--text-secondary); margin-bottom: 8px; }
        .form-control, .form-select { padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 0.95rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .format-row { background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 15px; position: relative; }
        .btn-remove-format { position: absolute; top: 10px; right: 10px; color: #ef4444; cursor: pointer; }
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
                <h2><i class="fas fa-cloud-upload-alt text-primary me-2"></i> Upload New Format</h2>
                <a href="manage_formats.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Manage Formats</a>
            </div>

            <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
            <?php endif; ?>

            <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="upload-card">
                        <div class="card-header">
                            <i class="fas fa-file-alt me-2 text-primary"></i> SOP Details
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="sop_number" class="form-label">SOP Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="sop_number" name="sop_number" placeholder="e.g. SOP-QA-001" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">SOP Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter descriptive title" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                        <?php if (empty($departments)): ?>
                                                <div class="alert alert-warning py-2 mb-0"><small><i class="fas fa-exclamation-triangle me-1"></i> No departments found. Please add departments first.</small></div>
                                                <!-- Fallback if no deps -->
                                                <input type="hidden" name="department" value="1"> 
                                        <?php else: ?>
                                            <select class="form-select" id="department" name="department" required>
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']) . ' (' . htmlspecialchars($dept['code']) . ')'; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <hr class="my-4 text-muted">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-muted"><i class="fas fa-copy me-2"></i>Upload Format</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addFormatBtn">
                                        <i class="fas fa-plus me-1"></i> Add Format
                                    </button>
                                </div>
                                <p class="text-muted small mb-3">You can attach up to 10 additional formats (e.g., Checklist, Flowchart, Logbook) to this SOP.</p>

                                <div id="formatsContainer">
                                    <!-- Dynamic Format Rows will appear here -->
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-cloud-upload-alt me-2"></i> Upload Formats
                                    </button>
                                </div>
                            </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('formatsContainer');
            const addBtn = document.getElementById('addFormatBtn');
            let formatCount = 0;
            const maxFormats = 10;

            addBtn.addEventListener('click', function() {
                if (formatCount >= maxFormats) {
                    alert('You can only add up to 10 additional formats.');
                    return;
                }
                formatCount++;
                
                const row = document.createElement('div');
                row.className = 'format-row';
                row.innerHTML = `
                    <i class="fas fa-times btn-remove-format" onclick="removeFormat(this)" title="Remove"></i>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small">Format No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="format_number[]" placeholder="e.g., 1, 2, 3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Format Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="format_name[]" placeholder="e.g., Checklist" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Format File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" name="format_file[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                `;
                container.appendChild(row);
            });

            window.removeFormat = function(element) {
                element.parentElement.remove();
                formatCount--;
            };
        });
    </script>
</body>
</html>
