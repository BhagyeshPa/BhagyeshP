<?php
session_start();
require_once 'helpers.php';
requireLogin();
checkRole(['admin']); // Only Admin can export

$pdo = getDBConnection();

// Get filters from GET parameters
$filterUser = $_GET['filter_user'] ?? '';
$filterAction = $_GET['filter_action'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

// Build query - same as audit_trail.php
$where = [];
$params = [];

if ($filterUser) {
    $where[] = "a.user_id LIKE ?";
    $params[] = "%$filterUser%";
}

if ($filterAction) {
    $where[] = "a.action LIKE ?";
    $params[] = "%$filterAction%";
}

if ($filterDateFrom) {
    $where[] = "a.timestamp >= ?";
    $params[] = "$filterDateFrom 00:00:00";
}

if ($filterDateTo) {
    $where[] = "a.timestamp <= ?";
    $params[] = "$filterDateTo 23:59:59";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get audit records with user details
$sql = "SELECT a.*, u.emp_code, u.full_name 
        FROM audit_trail a
        LEFT JOIN users u ON a.user_id = u.user_id
        $whereClause 
        ORDER BY a.timestamp DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$auditRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Log the export action
logAudit($pdo, $_SESSION['user_id'], 'Export Audit Trail', 'Exported audit trail to Excel', getClientIP());

// Set headers for Excel download
$filename = 'Audit_Trail_' . date('Y-m-d_His') . '.xls';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Output Excel content
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
echo '<style>';
echo 'table { border-collapse: collapse; width: 100%; }';
echo 'th { background-color: #4CAF50; color: white; font-weight: bold; border: 1px solid #ddd; padding: 8px; text-align: left; }';
echo 'td { border: 1px solid #ddd; padding: 8px; }';
echo 'tr:nth-child(even) { background-color: #f2f2f2; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<table>';

// Table headers
echo '<tr>';
echo '<th>Timestamp</th>';
echo '<th>Date</th>';
echo '<th>Time</th>';
echo '<th>Employee ID</th>';
echo '<th>Employee Name</th>';
echo '<th>User ID</th>';
echo '<th>Action</th>';
echo '<th>Details</th>';
echo '</tr>';

// Table data
foreach ($auditRecords as $record) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($record['timestamp']) . '</td>';
    echo '<td>' . date('d-M-Y', strtotime($record['timestamp'])) . '</td>';
    echo '<td>' . date('H:i:s', strtotime($record['timestamp'])) . '</td>';
    echo '<td>' . htmlspecialchars($record['emp_code'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($record['full_name'] ?? 'Unknown') . '</td>';
    echo '<td>' . htmlspecialchars($record['user_id']) . '</td>';
    echo '<td>' . htmlspecialchars($record['action']) . '</td>';
    echo '<td>' . htmlspecialchars($record['details']) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';

exit;
