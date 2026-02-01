<?php
session_start();
require_once 'helpers.php';
requireLogin();
checkRole(['admin']); // Only Admin can export

$pdo = getDBConnection();

// Get filters from GET parameters
$filterNumber = $_GET['sop_number'] ?? '';
$filterTitle = $_GET['title'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Build query with filters - same as archived_sops.php
$query = "SELECT f.id, f.title, f.sop_number, f.image, f.created_at, 
                 d.code as department_code, d.name as department_name,
                 u.emp_code, u.full_name, u.department as user_department,
                 a.timestamp as archived_date, a.user_id as archived_by_user_id
          FROM fileup f 
          LEFT JOIN departments d ON f.department_id = d.id 
          LEFT JOIN audit_trail a ON a.details LIKE CONCAT('%SOP: ', f.sop_number, '%') 
                 AND a.action = 'Archive SOP'
          LEFT JOIN users u ON a.user_id = u.user_id
          WHERE f.status = 'archived'";

$params = [];

if (!empty($filterNumber)) {
    $query .= " AND f.sop_number LIKE ?";
    $params[] = '%' . $filterNumber . '%';
}

if (!empty($filterTitle)) {
    $query .= " AND f.title LIKE ?";
    $params[] = '%' . $filterTitle . '%';
}

if (!empty($filterDateFrom)) {
    $query .= " AND DATE(f.created_at) >= ?";
    $params[] = $filterDateFrom;
}

if (!empty($filterDateTo)) {
    $query .= " AND DATE(f.created_at) <= ?";
    $params[] = $filterDateTo;
}

$query .= " ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$archivedSOPs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Log the export action
logAudit($pdo, $_SESSION['user_id'], 'Export Archived SOPs', 'Exported archived SOPs to Excel', getClientIP());

// Set headers for Excel download
$filename = 'Archived_SOPs_' . date('Y-m-d_His') . '.xls';
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
echo '<th>SOP Number</th>';
echo '<th>SOP Title</th>';
echo '<th>Department</th>';
echo '<th>Employee ID</th>';
echo '<th>Employee Name</th>';
echo '<th>Employee Department</th>';
echo '<th>Archived Date</th>';
echo '<th>Archived Time</th>';
echo '</tr>';

// Table data
foreach ($archivedSOPs as $sop) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($sop['sop_number']) . '</td>';
    echo '<td>' . htmlspecialchars($sop['title']) . '</td>';
    echo '<td>' . htmlspecialchars($sop['department_code'] ?? 'GEN') . '</td>';
    echo '<td>' . htmlspecialchars($sop['emp_code'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($sop['full_name'] ?? 'Unknown') . '</td>';
    echo '<td>' . htmlspecialchars($sop['user_department'] ?? 'N/A') . '</td>';
    
    $date = $sop['archived_date'] ?? $sop['created_at'];
    echo '<td>' . date('d-M-Y', strtotime($date)) . '</td>';
    echo '<td>' . date('H:i:s', strtotime($date)) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';

exit;
