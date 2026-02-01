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

// Get filters from GET parameters
$filterEmpCode = $_GET['filter_emp'] ?? '';
$filterDept = $_GET['filter_dept'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

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

// Get all logs (no pagination for export)
$sql = "SELECT a.*, u.emp_code, u.full_name, u.role, u.department as user_department, f.title as sop_title 
        FROM audit_trail a 
        LEFT JOIN users u ON a.user_id = u.user_id 
        LEFT JOIN fileup f ON SUBSTRING_INDEX(a.details, 'SOP: ', -1) = f.sop_number AND f.status = 'active'
        $whereClause 
        ORDER BY a.timestamp DESC";
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
        if (preg_match("/Uploaded Format '(.+?)' for SOP: (.+?)$/", $details, $matches)) {
            $parsed['format_name'] = $matches[1];
            $parsed['sop_number'] = $matches[2];
        }
        elseif (preg_match("/Uploaded Format (.+?) for SOP: (.+?)$/", $details, $matches)) {
            $parsed['format_name'] = $matches[1];
            $parsed['sop_number'] = $matches[2];
        }
    } elseif ($action === 'Upload SOP') {
        // Extract SOP info
        if (preg_match("/Uploaded SOP: (.+?) - (.+?)$/", $details, $matches)) {
            $parsed['sop_number'] = $matches[1];
            $parsed['sop_name'] = $matches[2];
        }
    }
    
    return $parsed;
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Upload_Format_Logs_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Output HTML table with Excel XML namespace
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Upload Format Logs</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
echo '<body>';
echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
echo '<th>Action</th>';
echo '<th>Employee Code</th>';
echo '<th>Full Name</th>';
echo '<th>Designation</th>';
echo '<th>Department</th>';
echo '<th>SOP Number</th>';
echo '<th>SOP Name</th>';
echo '<th>Format Name</th>';
echo '<th>Details</th>';
echo '<th>Timestamp</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if (count($logs) > 0) {
    foreach ($logs as $log) {
        $parsed = parseUploadDetails($log['details'], $log['action']);
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($log['action']) . '</td>';
        echo '<td>' . htmlspecialchars($log['emp_code'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($log['full_name'] ?? 'N/A') . '</td>';
        echo '<td>' . ucfirst(htmlspecialchars($log['role'] ?? 'N/A')) . '</td>';
        echo '<td>' . htmlspecialchars($log['user_department'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($parsed['sop_number'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($parsed['sop_name'] ?: ($log['sop_title'] ?? 'N/A')) . '</td>';
        echo '<td>'. htmlspecialchars($parsed['format_name'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($log['details']) . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['timestamp']))) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="10" style="text-align:center;">No records found</td></tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';
exit;
?>
