<?php
session_start();
require_once 'helpers.php';
requireLogin();
// All users can view print logs

$pdo = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Filters
$filterEmpCode = $_GET['filter_emp'] ?? '';
$filterDept = $_GET['filter_dept'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

// Build query - Join audit_trail with users and parse SOP details from audit details
$where = ["a.action = 'Print SOP'"];
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
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_trail a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    $whereClause");
$countStmt->execute($params);
$totalLogs = $countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

// Get logs with user details
$sql = "SELECT a.*, u.emp_code, u.full_name, u.role, u.department as user_department 
        FROM audit_trail a 
        LEFT JOIN users u ON a.user_id = u.user_id 
        $whereClause 
        ORDER BY a.timestamp DESC 
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get distinct departments for filter
$deptStmt = $pdo->query("SELECT DISTINCT department FROM users ORDER BY department");
$departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

// Function to parse SOP details from audit details string
function parseSOPDetails($details, $pdo) {
    $parsed = [
        'sop_title' => '',
        'sop_number' => '',
        'reason' => '',
        'format_name' => '',
        'format_id' => '',
        'file_name' => ''
    ];
    
    // Extract SOP title and filename
    if (preg_match('/Printed\/Viewed SOP: (.+?) \((.+?)\)/', $details, $matches)) {
        $parsed['sop_title'] = $matches[1];
        $parsed['file_name'] = $matches[2]; // This is the actual file name
        
        // Try to get format information from sop_formats table
        try {
            $stmt = $pdo->prepare("SELECT sf.id, sf.format_name, f.sop_number 
                                  FROM sop_formats sf 
                                  JOIN fileup f ON sf.sop_id = f.id 
                                  WHERE sf.file_name = ? AND sf.status = 'active' LIMIT 1");
            $stmt->execute([$parsed['file_name']]);
            $format = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($format) {
                $parsed['format_name'] = $format['format_name'];
                $parsed['format_id'] = 'F-' . str_pad($format['id'], 3, '0', STR_PAD_LEFT);
                $parsed['sop_number'] = $format['sop_number'];
            }
        } catch (PDOException $e) {
            // If query fails, leave empty
        }
        
        // If no format found, it might be the main SOP file
        if (empty($parsed['format_name'])) {
            try {
                $stmt = $pdo->prepare("SELECT sop_number FROM fileup WHERE image = ? AND status = 'active' LIMIT 1");
                $stmt->execute([$parsed['file_name']]);
                $main = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($main) {
                    $parsed['sop_number'] = $main['sop_number'];
                    $parsed['format_name'] = 'Main SOP';
                    $parsed['format_id'] = '-';
                }
            } catch (PDOException $e) {
                // If query fails, leave empty
            }
        }
    }
    
    // Extract reason
    if (preg_match('/Reason: (.+)$/', $details, $matches)) {
        $parsed['reason'] = $matches[1];
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
    <title>Logs - KOPRAN SOP Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/layout_styles.css">
    <style>
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .log-reason {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .log-reason:hover {
            white-space: normal;
            overflow: visible;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="app-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="app-main">
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                <h2><i class="fas fa-print text-primary me-2"></i> Print Logs</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Employee Code</label>
                        <input type="text" class="form-control" name="filter_emp" 
                            value="<?php echo htmlspecialchars($filterEmpCode); ?>" 
                            placeholder="Search by emp code">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="filter_dept">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>" 
                                    <?php echo $filterDept === $dept ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="filter_date_from" 
                            value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="filter_date_to" 
                            value="<?php echo htmlspecialchars($filterDateTo); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
                <?php if ($filterEmpCode || $filterDept || $filterDateFrom || $filterDateTo): ?>
                    <div class="mt-2">
                        <a href="print_logs.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Total Logs: <?php echo $totalLogs; ?></h5>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="export_print_logs.php?filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export to Excel
                        </a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 100px;">Employee ID</th>
                                <th style="min-width: 150px;">Name</th>
                                <th style="min-width: 100px;">Designation</th>
                                <th style="min-width: 120px;">Department</th>
                                <th style="min-width: 120px;">SOP Number</th>
                                <th style="min-width: 200px;">SOP Title</th>
                                <th style="min-width: 100px;">Format No</th>
                                <th style="min-width: 150px;">Format Name</th>
                                <th style="min-width: 250px;">Reason</th>
                                <th style="min-width: 110px;">Date</th>
                                <th style="min-width: 80px;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No print logs found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <?php $sopDetails = parseSOPDetails($log['details'], $pdo); ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['emp_code'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($log['full_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($log['role'] ?? 'N/A')); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_department'] ?? 'N/A'); ?></td>
                                        <td><code><?php echo htmlspecialchars($sopDetails['sop_number'] ?: 'N/A'); ?></code></td>
                                        <td><?php echo htmlspecialchars($sopDetails['sop_title']); ?></td>
                                        <td><code><?php echo htmlspecialchars($sopDetails['format_id'] ?: 'N/A'); ?></code></td>
                                        <td><?php echo htmlspecialchars($sopDetails['format_name'] ?: 'N/A'); ?></td>
                                        <td>
                                            <span class="log-reason" title="<?php echo htmlspecialchars($sopDetails['reason']); ?>">
                                                <?php echo htmlspecialchars($sopDetails['reason']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d-M-Y', strtotime($log['timestamp'])); ?></td>
                                        <td><?php echo date('H:i:s', strtotime($log['timestamp'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter_emp=<?php echo urlencode($filterEmpCode); ?>&filter_dept=<?php echo urlencode($filterDept); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </main>

        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
