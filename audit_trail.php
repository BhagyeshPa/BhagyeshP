<?php
session_start();

// Check if user is logged in and has Admin role
require_once 'helpers.php';
requireLogin();
checkRole(['admin']); // Only Admin can view audit trail

$pdo = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Filters
$filterUser = $_GET['filter_user'] ?? '';
$filterAction = $_GET['filter_action'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

// Build query
$where = [];
$params = [];

if ($filterUser) {
    $where[] = "user_id LIKE ?";
    $params[] = "%$filterUser%";
}

if ($filterAction) {
    $where[] = "action LIKE ?";
    $params[] = "%$filterAction%";
}

if ($filterDateFrom) {
    $where[] = "timestamp >= ?";
    $params[] = "$filterDateFrom 00:00:00";
}

if ($filterDateTo) {
    $where[] = "timestamp <= ?";
    $params[] = "$filterDateTo 23:59:59";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_trail $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get audit records with user details
// Integer casting for Limit/Offset to avoid PDO string quote issues
$limit = (int) $perPage;
$offsetInt = (int) $offset;

$sql = "SELECT a.*, u.emp_code, u.full_name 
        FROM audit_trail a
        LEFT JOIN users u ON a.user_id = u.user_id
        $whereClause 
        ORDER BY a.timestamp DESC 
        LIMIT $limit OFFSET $offsetInt";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$auditRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique actions for filter dropdown
$actionsStmt = $pdo->query("SELECT DISTINCT action FROM audit_trail ORDER BY action");
$actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);
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
    <title>Kopran - Audit Trail</title>
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

        .badge-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
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
                <h2><i class="fas fa-clipboard-list text-primary me-2"></i> Audit Trail</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" name="filter_user"
                            value="<?php echo htmlspecialchars($filterUser); ?>" placeholder="Search by user">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Action</label>
                        <select class="form-select" name="filter_action">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $filterAction === $action ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($action); ?>
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
                <?php if ($filterUser || $filterAction || $filterDateFrom || $filterDateTo): ?>
                    <div class="mt-2">
                        <a href="audit_trail.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Audit Records Table -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Total Records: <?php echo number_format($totalRecords); ?></h5>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="export_audit_trail.php?filter_user=<?php echo urlencode($filterUser); ?>&filter_action=<?php echo urlencode($filterAction); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export to Excel
                        </a>
                    <?php endif; ?>
                </div>
                </div>

                <?php if (empty($auditRecords)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No audit records found</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 150px;">Timestamp</th>
                                    <th style="min-width: 100px;">Emp ID</th>
                                    <th style="min-width: 150px;">Name</th>
                                    <th style="min-width: 120px;">User ID</th>
                                    <th style="min-width: 150px;">Action</th>
                                    <th style="min-width: 300px;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auditRecords as $record): ?>
                                    <tr>
                                        <td>
                                            <div><?php echo date('d-M-Y', strtotime($record['timestamp'])); ?></div>
                                            <small class="text-muted"><?php echo date('H:i:s', strtotime($record['timestamp'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo htmlspecialchars($record['emp_code'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($record['full_name'] ?? 'Unknown'); ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($record['user_id']); ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($record['action']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($record['details']); ?>
                                        </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($auditRecords) && $totalPages > 1): ?>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-4">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $page - 1; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_action=<?php echo urlencode($filterAction); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $i; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_action=<?php echo urlencode($filterAction); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $page + 1; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_action=<?php echo urlencode($filterAction); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>