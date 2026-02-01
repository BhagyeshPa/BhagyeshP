<?php
session_start();
require_once 'helpers.php';
requireLogin();
checkRole(['admin']); // Only Admin can view SOP access requests

$pdo = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Filters
$filterUser = $_GET['filter_user'] ?? '';
$filterSopNumber = $_GET['filter_sop_number'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

// Build query
$where = [];
$params = [];

if ($filterUser) {
    $where[] = "user_id LIKE ?";
    $params[] = "%$filterUser%";
}

if ($filterSopNumber) {
    $where[] = "sop_number LIKE ?";
    $params[] = "%$filterSopNumber%";
}

if ($filterDateFrom) {
    $where[] = "requested_at >= ?";
    $params[] = "$filterDateFrom 00:00:00";
}

if ($filterDateTo) {
    $where[] = "requested_at <= ?";
    $params[] = "$filterDateTo 23:59:59";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sop_access_requests $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get SOP access requests
$limit = (int) $perPage;
$offsetInt = (int) $offset;

$sql = "SELECT * FROM sop_access_requests $whereClause ORDER BY requested_at DESC LIMIT $limit OFFSET $offsetInt";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique SOP numbers for filter dropdown
$sopStmt = $pdo->query("SELECT DISTINCT sop_number FROM sop_access_requests ORDER BY sop_number");
$sopNumbers = $sopStmt->fetchAll(PDO::FETCH_COLUMN);
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
    <title>Kopran - Format Access Requests</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css">
    <style>
        .page-header h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-primary);
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: #f8f9fa;
            border-bottom: 2px solid var(--border-color);
        }

        .table th {
            font-weight: 600;
            color: var(--text-primary);
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .table td {
            padding: 15px;
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .reason-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
        }

        .btn-view-reason {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .pagination {
            margin-top: 20px;
            justify-content: center;
        }

        .pagination .page-link {
            color: var(--primary-color);
            border-color: var(--border-color);
        }

        .pagination .page-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .pagination .active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                <h2><i class="fas fa-file-request text-primary me-2"></i> Format Access Requests</h2>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" name="filter_user" placeholder="Filter by User ID" value="<?php echo htmlspecialchars($filterUser); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SOP Name</label>
                        <select class="form-select" name="filter_sop_number">
                            <option value="">All Formats</option>
                            <?php foreach ($sopNumbers as $sopNum): ?>
                                <option value="<?php echo htmlspecialchars($sopNum); ?>" <?php echo $filterSopNumber === $sopNum ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sopNum); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="filter_date_from" value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="filter_date_to" value="<?php echo htmlspecialchars($filterDateTo); ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i> Filter
                        </button>
                        <a href="sop_access_requests.php" class="btn btn-secondary w-100">
                            <i class="fas fa-redo me-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>SOP Name</th>
                                <th>SOP Title</th>
                                <th>Word Count</th>
                                <th>Requested At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox me-2"></i> No Format access requests found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requests as $req): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($req['user_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($req['sop_number']); ?></td>
                                        <td><?php echo htmlspecialchars($req['sop_title']); ?></td>
                                        <td>
                                            <span class="badge badge-status" style="background: #d4edda; color: #155724;">
                                                <?php echo $req['word_count']; ?> words
                                            </span>
                                        </td>
                                        <td><?php echo date('d-M-Y H:i', strtotime($req['requested_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info btn-view-reason" data-bs-toggle="modal" data-bs-target="#reasonModal" 
                                                onclick="viewReason('<?php echo htmlspecialchars(addslashes($req['reason'])); ?>', '<?php echo htmlspecialchars($req['user_id']); ?>', '<?php echo htmlspecialchars($req['sop_title']); ?>')">
                                                <i class="fas fa-eye me-1"></i> View Reason
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&filter_user=<?php echo urlencode($filterUser); ?>&filter_sop_number=<?php echo urlencode($filterSopNumber); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">First</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_sop_number=<?php echo urlencode($filterSopNumber); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_sop_number=<?php echo urlencode($filterSopNumber); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_sop_number=<?php echo urlencode($filterSopNumber); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">Next</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>&filter_user=<?php echo urlencode($filterUser); ?>&filter_sop_number=<?php echo urlencode($filterSopNumber); ?>&filter_date_from=<?php echo urlencode($filterDateFrom); ?>&filter_date_to=<?php echo urlencode($filterDateTo); ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <p class="text-center text-muted">Showing page <?php echo $page; ?> of <?php echo $totalPages; ?> (Total: <?php echo $totalRecords; ?> requests)</p>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>

    <!-- Reason Modal -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Format Access Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Requested By:</label>
                        <p id="reasonUser" class="text-muted"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">SOP Title:</label>
                        <p id="reasonSopTitle" class="text-muted"></p>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Reason:</label>
                        <div class="alert alert-light border" id="reasonText" style="max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewReason(reason, userId, sopTitle) {
            document.getElementById('reasonUser').textContent = userId;
            document.getElementById('reasonSopTitle').textContent = sopTitle;
            document.getElementById('reasonText').textContent = reason;
        }
    </script>
</body>

</html>
