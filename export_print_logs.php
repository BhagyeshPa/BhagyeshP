<?php
session_start();
require_once 'helpers.php';
requireLogin();
// All users can export print logs

$pdo = getDBConnection();

// Filters
$filterEmpCode = $_GET['filter_emp'] ?? '';
$filterDept = $_GET['filter_dept'] ?? '';
$filterDateFrom = $_GET['filter_date_from'] ?? '';
$filterDateTo = $_GET['filter_date_to'] ?? '';

// Build query
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

// Get all logs (no pagination for export)
$sql = "SELECT a.*, u.emp_code, u.full_name, u.role, u.department as user_department 
        FROM audit_trail a 
        LEFT JOIN users u ON a.user_id = u.user_id 
        $whereClause 
        ORDER BY a.timestamp DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        $parsed['file_name'] = $matches[2];
        
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

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Print_Logs_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Print Logs</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
echo '<body>';
echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
echo '<th>Employee ID</th>';
echo '<th>Name</th>';
echo '<th>Designation</th>';
echo '<th>Department</th>';
echo '<th>SOP Number</th>';
echo '<th>SOP Title</th>';
echo '<th>Format No</th>';
echo '<th>Format Name</th>';
echo '<th>Reason</th>';
echo '<th>Date</th>';
echo '<th>Time</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($logs as $log) {
    $sopDetails = parseSOPDetails($log['details'], $pdo);
    echo '<tr>';
    echo '<td>' . htmlspecialchars($log['emp_code'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($log['full_name'] ?? 'N/A') . '</td>';
    echo '<td>' . ucfirst(htmlspecialchars($log['role'] ?? 'N/A')) . '</td>';
    echo '<td>' . htmlspecialchars($log['user_department'] ?? 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($sopDetails['sop_number'] ?: 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($sopDetails['sop_title']) . '</td>';
    echo '<td>' . htmlspecialchars($sopDetails['format_id'] ?: 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($sopDetails['format_name'] ?: 'N/A') . '</td>';
    echo '<td>' . htmlspecialchars($sopDetails['reason']) . '</td>';
    echo '<td>' . date('d-M-Y', strtotime($log['timestamp'])) . '</td>';
    echo '<td>' . date('H:i:s', strtotime($log['timestamp'])) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

// Log the export action
logAudit($pdo, $_SESSION['user_id'], 'Export Print Logs', 'Exported print logs to Excel', getClientIP());
exit;
