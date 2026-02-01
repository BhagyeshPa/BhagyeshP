<?php
session_start();
require_once 'helpers.php';
requireLogin();

// Only allow QA and Admin to view archived formats
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['qa', 'admin'])) {
    die("Access Denied. Only QA and Admin can view archived formats.");
}

$pdo = getDBConnection();

// Check if required columns exist
$checkColumns = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'status'");
$statusColumnExists = $checkColumns && $checkColumns->rowCount() > 0;
$checkSfCreatedAt = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'created_at'");
$sfCreatedAtExists = $checkSfCreatedAt && $checkSfCreatedAt->rowCount() > 0;
$checkFileupCreatedAt = $pdo->query("SHOW COLUMNS FROM fileup LIKE 'created_at'");
$fileupCreatedAtExists = $checkFileupCreatedAt && $checkFileupCreatedAt->rowCount() > 0;
$checkFormatNumber = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'format_number'");
$formatNumberExists = $checkFormatNumber && $checkFormatNumber->rowCount() > 0;

$archivedFormats = [];
$error = '';
$loggedInName = $_SESSION['full_name'] ?? 'N/A';
$loggedInCode = $_SESSION['emp_code'] ?? 'N/A';

if (!$statusColumnExists) {
    $error = "Database migration not yet applied. Please execute migrate_format_versioning.sql first in phpMyAdmin.";
} else {
    $dateSelect = $sfCreatedAtExists ? "sf.created_at" : ($fileupCreatedAtExists ? "f.created_at" : "NULL");
    $formatNumberSelect = $formatNumberExists ? "sf.format_number," : "";
    // Fetch archived formats with their SOP details
    $query = "
        SELECT 
            sf.id,
            sf.sop_id,
            sf.format_name,
            $formatNumberSelect
            sf.version,
            sf.file_name,
            $dateSelect AS archived_at,
            f.sop_number,
            f.title AS sop_name,
            f.uploaded_by,
            d.name AS dept_name
        FROM sop_formats sf
        JOIN fileup f ON sf.sop_id = f.id
        LEFT JOIN departments d ON f.department_id = d.id
        WHERE sf.status = 'archived'
        ORDER BY archived_at DESC, sf.sop_id DESC
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $archivedFormats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $archivedFormats = [];
        $error = "Error fetching archived formats: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Formats - KOPRAN SOP Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            --soft-border: #e6edf3;
            --text-muted-strong: #6b7280;
        }
        .app-main {
            background: #f5f7fb;
            min-height: 100vh;
        }
        .card {
            border: 1px solid var(--soft-border);
            border-radius: 14px;
            box-shadow: var(--card-shadow);
        }
        .card-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border-bottom: none;
            border-radius: 14px 14px 0 0;
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            color: #fff;
        }
        .card-header h5 {
            font-weight: 700;
            margin: 0;
        }
        .card-header small {
            display: block;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 4px;
        }
        .badge-version {
            background-color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.35rem 0.6rem;
        }
        .badge-archived {
            background-color: #ef4444;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.32rem 0.55rem;
        }
        .table-responsive {
            border-radius: 12px;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background-color: #f1f5f9;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            font-size: 0.78rem;
            padding: 14px 16px;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
        }
        .table tbody tr:hover {
            background: #fafbfd;
        }
        .text-muted-strong {
            color: var(--text-muted-strong);
        }
        .file-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
        }
        .card-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 14px;
            border-radius: 0 0 14px 14px;
        }
        .alert {
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }
        .btn-light {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            color: #334155;
            transition: all 0.2s ease;
        }
        .btn-light:hover {
            background-color: #f8fafc;
            border-color: #cbd5f5;
        }
        @media (max-width: 992px) {
            .table thead th,
            .table tbody td {
                padding: 10px 12px;
            }
        }
        .file-link {
            cursor: pointer;
            color: #2563eb;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .file-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .modal-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-bottom: none;
        }
        .modal-header .btn-close {
            filter: invert(1);
        }
        /* Dark Mode Styles */
        html.dark-mode .app-main {
            background: #111827 !important;
        }
        html.dark-mode .card {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
        }
        html.dark-mode .card-body {
            background-color: #1f2937 !important;
        }
        html.dark-mode .card-header {
            background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%) !important;
            border-color: #374151 !important;
        }
        html.dark-mode .card-header h5,
        html.dark-mode .card-header small,
        html.dark-mode .card-title {
            color: #ffffff !important;
        }
        html.dark-mode .btn-light {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #e5e7eb !important;
        }
        html.dark-mode .btn-light:hover {
            background-color: #4b5563 !important;
            border-color: #6b7280 !important;
            color: #ffffff !important;
        }
        html.dark-mode .table thead th {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }
        html.dark-mode .file-pill {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #e5e7eb !important;
        }
        html.dark-mode .card-footer {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
        html.dark-mode .card-footer small,
        html.dark-mode .card-footer strong {
            color: #e5e7eb !important;
        }
        .file-viewer-container {
            max-height: 70vh;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .file-viewer-container iframe {
            width: 100%;
            border: none;
        }
        .file-viewer-image {
            max-width: 100%;
            height: auto;
            margin: 0 auto;
        }
        .download-section {
            padding: 15px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-0"><i class="fas fa-archive me-2"></i>Archived Formats</h5>
                            <small>Replaced or superseded format versions</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" id="exportExcelBtn">
                                <i class="fas fa-file-excel me-1"></i>Export to Excel
                            </button>
                            <a href="manage_formats.php" class="btn btn-light btn-sm">
                                <i class="fas fa-undo me-1"></i>Back to Manage Formats
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-warning m-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Setup Required:</strong> <?php echo $error; ?>
                                <br><small class="mt-2 d-block">Please execute the migration script in phpMyAdmin to enable version tracking.</small>
                            </div>
                        <?php elseif (empty($archivedFormats)): ?>
                            <div class="alert alert-info m-3" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                No archived formats found. Archived versions will appear here when you replace existing formats with newer versions.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th>Date &amp; Time Stamp</th>
                                            <th>SOP Number</th>
                                            <th>Format Name</th>
                                            <?php if ($formatNumberExists): ?>
                                            <th>Format Number</th>
                                            <?php endif; ?>
                                            <th>Version No.</th>
                                            <th>Emp ID</th>
                                            <th>Emp Name</th>
                                            <th>Department</th>
                                            <th>File Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($archivedFormats as $fmt): ?>
                                            <tr>
                                                <td>
                                                    <span class="text-muted-strong">
                                                        <?php echo $fmt['archived_at'] ? date('d-M-Y H:i', strtotime($fmt['archived_at'])) : 'N/A'; ?>
                                                    </span>
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($fmt['sop_number'] ?? 'N/A'); ?></strong></td>
                                                <td><?php echo htmlspecialchars($fmt['format_name'] ?? 'N/A'); ?></td>
                                                <?php if ($formatNumberExists): ?>
                                                <td>
                                                    <span class="badge bg-secondary" style="font-weight: 600;"><?php echo htmlspecialchars($fmt['format_number'] ?? 'N/A'); ?></span>
                                                </td>
                                                <?php endif; ?>
                                                <td>
                                                    <span class="badge badge-version">v<?php echo htmlspecialchars($fmt['version'] ?? '1.0'); ?></span>
                                                    <span class="badge badge-archived ms-1">Archived</span>
                                                </td>
                                                <td><span class="text-muted-strong font-monospace"><?php echo htmlspecialchars($loggedInCode ?: 'N/A'); ?></span></td>
                                                <td><?php echo htmlspecialchars($loggedInName); ?></td>
                                                <td><?php echo htmlspecialchars($fmt['dept_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <a class="file-link" data-bs-toggle="modal" data-bs-target="#fileViewer" data-file="<?php echo htmlspecialchars($fmt['file_name'] ?? ''); ?>" data-sop="<?php echo htmlspecialchars($fmt['sop_number'] ?? ''); ?>">
                                                        <span class="file-pill">
                                                            <i class="fas fa-file-pdf text-danger"></i>
                                                            <?php echo htmlspecialchars($fmt['file_name'] ?? 'N/A'); ?>
                                                        </span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($archivedFormats)): ?>
                        <div class="card-footer text-muted text-center">
                            <small>Total Archived Formats: <strong><?php echo count($archivedFormats); ?></strong></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <?php include 'right_panel.php'; ?>
    </div>

    <!-- File Viewer Modal -->
    <div class="modal fade" id="fileViewer" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-pdf text-white me-2"></i>
                        <span id="fileTitle"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="file-viewer-container" id="fileViewerContent">
                        <div class="d-flex justify-content-center align-items-center h-100 py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="download-section">
                    <a id="downloadLink" href="#" class="btn btn-primary btn-sm" download>
                        <i class="fas fa-download me-1"></i>Download File
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File viewer modal functionality
        document.getElementById('fileViewer').addEventListener('show.bs.modal', function(e) {
            const button = e.relatedTarget;
            const fileName = button.getAttribute('data-file');
            const sopNumber = button.getAttribute('data-sop');
            
            // Set modal title
            document.getElementById('fileTitle').textContent = fileName;
            
            // Build file path - files are stored in uploads/sops/
            const filePath = 'uploads/sops/' + encodeURIComponent(fileName);
            const fileExt = fileName.split('.').pop().toLowerCase();
            
            const viewerContent = document.getElementById('fileViewerContent');
            let content = '';
            
            // Display file based on extension
            if (['pdf'].includes(fileExt)) {
                content = '<iframe src="' + filePath + '" style="height: 600px;"></iframe>';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
                content = '<div class="text-center p-3"><img src="' + filePath + '" alt="' + fileName + '" class="file-viewer-image"></div>';
            } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileExt)) {
                content = `
                    <div class="text-center p-5">
                        <i class="fas fa-file text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">
                            <strong>${fileName}</strong><br>
                            <small>Preview not available for this file type. Please download to view.</small>
                        </p>
                    </div>
                `;
            } else {
                content = `
                    <div class="text-center p-5">
                        <i class="fas fa-file-download text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">
                            <strong>${fileName}</strong><br>
                            <small>File preview not supported. Click download to access the file.</small>
                        </p>
                    </div>
                `;
            }
            
            viewerContent.innerHTML = content;
            
            // Set download link
            document.getElementById('downloadLink').href = filePath;
            document.getElementById('downloadLink').download = fileName;
        });

        // Export to Excel functionality
        document.getElementById('exportExcelBtn')?.addEventListener('click', function() {
            const table = document.querySelector('.table');
            if (!table) {
                alert('No data to export');
                return;
            }

            // Get table data
            let tableData = [];
            const headers = [];
            
            // Extract headers
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            tableData.push(headers);

            // Extract rows
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(td => {
                    // Get text content, cleaning badges and icons
                    let text = td.textContent.trim();
                    // Remove extra whitespace
                    text = text.replace(/\s+/g, ' ');
                    rowData.push(text);
                });
                tableData.push(rowData);
            });

            // Create CSV content
            let csv = '';
            tableData.forEach(row => {
                csv += row.map(cell => {
                    // Escape quotes and wrap in quotes if contains comma
                    cell = cell.replace(/"/g, '""');
                    return '"' + cell + '"';
                }).join(',') + '\n';
            });

            // Create blob and download
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            const filename = 'archived_formats_' + new Date().toISOString().split('T')[0] + '.csv';
            
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
</body>
</html>
