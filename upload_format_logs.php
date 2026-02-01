<?php
session_start();
require_once 'helpers.php';
requireLogin();

// Check if user is QA or Admin
if (!in_array($_SESSION['role'] ?? '', ['qa', 'admin'])) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

$pdo = getDBConnection();

// Filters
$filterEmpCode = $_GET['filter_emp'] ?? '';
$filterDept = $_GET['filter_dept'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

// Build query for upload actions
$where = ["a.action IN ('Upload Format', 'Upload SOP')"];
$params = [];

if ($filterEmpCode) {
    $where[] = "u.emp_code LIKE ?";
    $params[] = "%$filterEmpCode%";
}

if ($filterDept) {
    $where[] = "u.department LIKE ?";
    $params[] = "%$filterDept%";
}

if ($filterDateFrom) {
    $where[] = "a.timestamp >= ?";
    $params[] = "$filterDateFrom 00:00:00";
}

if ($filterDateTo) {
    $where[] = "a.timestamp <= ?";
    $params[] = "$filterDateTo 23:59:59";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM audit_trail a 
            LEFT JOIN users u ON a.user_id = u.user_id 
            $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $pageSize);

// Check if format_number column exists
$checkFormatNumber = $pdo->query("SHOW COLUMNS FROM sop_formats LIKE 'format_number'");
$formatNumberExists = $checkFormatNumber && $checkFormatNumber->rowCount() > 0;

// Get paginated logs
$formatNumberSelect = $formatNumberExists ? "sf.format_number," : "NULL as format_number,";
$sql = "SELECT a.*, u.emp_code, u.full_name, u.role, u.department as user_department, f.title as sop_title,
        $formatNumberSelect sf.format_name as db_format_name, sf.id as format_id
        FROM audit_trail a 
        LEFT JOIN users u ON a.user_id = u.user_id 
        LEFT JOIN fileup f ON SUBSTRING_INDEX(a.details, 'SOP: ', -1) = f.sop_number AND f.status = 'active'
        LEFT JOIN sop_formats sf ON sf.sop_id = f.id 
            AND sf.format_name = TRIM(BOTH \"'\" FROM SUBSTRING_INDEX(SUBSTRING_INDEX(a.details, \"'\", 2), \"'\", -1))
            AND sf.status = 'active'
        $whereClause 
        ORDER BY a.timestamp DESC 
        LIMIT " . intval($pageSize) . " OFFSET " . intval($offset);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to parse upload details
function parseUploadDetails($details, $action) {
    $parsed = [
        'sop_number' => '',
        'sop_name' => '',
        'format_name' => '',
        'reason' => ''
    ];
    
    if ($action === 'Upload Format') {
        // Extract format name and SOP number
        // Pattern: Uploaded Format 'Checklist' for SOP: SOP-MF-002
        // Try with single quotes first
        if (preg_match("/Uploaded Format '(.+?)' for SOP: (.+?)$/", $details, $matches)) {
            $parsed['format_name'] = $matches[1];
            $parsed['sop_number'] = $matches[2];
        }
        // Try without quotes as fallback
        elseif (preg_match("/Uploaded Format (.+?) for SOP: (.+?)$/", $details, $matches)) {
            $parsed['format_name'] = $matches[1];
            $parsed['sop_number'] = $matches[2];
        }
    } elseif ($action === 'Upload SOP') {
        // Extract SOP info
        // Pattern: Uploaded SOP: SOP-MF-001 - Multi-Format Test
        if (preg_match("/Uploaded SOP: (.+?) - (.+?)$/", $details, $matches)) {
            $parsed['sop_number'] = $matches[1];
            $parsed['sop_name'] = $matches[2];
        }
    }
    
    return $parsed;
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
    <title>Uploaded Format Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/layout_styles.css" rel="stylesheet">
    <style>
        .filters-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background-color: #f0f0f0;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            padding: 12px;
        }
        .table tbody td {
            padding: 10px 12px;
            vertical-align: middle;
        }
        .pagination {
            margin-top: 20px;
        }
        .no-records {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .action-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .action-badge.upload-format {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .action-badge.upload-sop {
            background-color: #f3e5f5;
            color: #7b1fa2;
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
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                <div>
                    <h2><i class="fas fa-file-upload text-primary me-2"></i>Uploaded Format Logs</h2>
                    <p class="text-muted mb-0">View all format and SOP upload activities</p>
                </div>
                <div>
                    <a href="export_upload_format_logs.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success">
                        <i class="fas fa-file-excel me-2"></i>Export to Excel
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_emp" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="filter_emp" name="filter_emp" 
                                   value="<?php echo htmlspecialchars($filterEmpCode); ?>" placeholder="Enter Employee ID">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_dept" class="form-label">Department</label>
                            <input type="text" class="form-control" id="filter_dept" name="filter_dept" 
                                   value="<?php echo htmlspecialchars($filterDept); ?>" placeholder="Enter Department">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filter_date_from" name="filter_date_from" 
                                   value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="filter_date_to" name="filter_date_to" 
                                   value="<?php echo htmlspecialchars($filterDateTo); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="upload_format_logs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>

            <!-- Results Info -->
            <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Showing <strong><?php echo count($logs); ?></strong> of <strong><?php echo $totalRecords; ?></strong> upload records
            </div>

            <!-- Table -->
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                    <th>SOP No</th>
                                    <th>SOP Name</th>
                                    <th>Format Name</th>
                                    <th>Format Number</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <?php 
                                        $uploadDetails = parseUploadDetails($log['details'], $log['action']);
                                        $dateObj = new DateTime($log['timestamp']);
                                        
                                        // Fetch format number directly if not in the log
                                        $formatNumber = $log['format_number'] ?? null;
                                        if (empty($formatNumber) && !empty($uploadDetails['format_name']) && !empty($uploadDetails['sop_number']) && $log['action'] === 'Upload Format') {
                                            try {
                                                $formatStmt = $pdo->prepare("SELECT format_number FROM sop_formats sf 
                                                    JOIN fileup f ON sf.sop_id = f.id 
                                                    WHERE f.sop_number = ? AND sf.format_name = ? AND sf.status = 'active' 
                                                    LIMIT 1");
                                                $formatStmt->execute([$uploadDetails['sop_number'], $uploadDetails['format_name']]);
                                                $formatResult = $formatStmt->fetch(PDO::FETCH_ASSOC);
                                                if ($formatResult) {
                                                    $formatNumber = $formatResult['format_number'];
                                                }
                                            } catch (Exception $e) {
                                                // Column might not exist
                                            }
                                        }
                                        
                                        // Debug: uncomment to see raw data
                                        // echo "<!-- Debug: Action=" . htmlspecialchars($log['action']) . " Format Number=" . htmlspecialchars($formatNumber ?? 'NULL') . " -->";
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['emp_code'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['full_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($log['role'] ?? 'N/A')); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_department'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="action-badge <?php echo $log['action'] === 'Upload Format' ? 'upload-format' : 'upload-sop'; ?>">
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($uploadDetails['sop_number'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($log['sop_title'] ?: $uploadDetails['sop_name'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($uploadDetails['format_name'] ?: '-'); ?></td>
                                        <td>
                                            <?php if (!empty($formatNumber)): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($formatNumber); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $dateObj->format('d-M-Y'); ?></td>
                                        <td><?php echo $dateObj->format('H:i:s'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <i class="fas fa-step-backward"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $totalPages; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <i class="fas fa-step-forward"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No upload records found.</p>
                </div>
            <?php endif; ?>
        </main>
        
        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
