<?php
require_once 'helpers.php';

header('Content-Type: application/json');

if (!isset($_GET['sop_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing SOP ID']);
    exit;
}

$sopId = (int) $_GET['sop_id'];
$pdo = getDBConnection();

try {
    // 1. Fetch Main SOP File
    $stmt = $pdo->prepare("SELECT title, sop_number, image FROM fileup WHERE id = ?");
    $stmt->execute([$sopId]);
    $mainSop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mainSop) {
        http_response_code(404);
        echo json_encode(['error' => 'Format not found']);
        exit;
    }

    // 2. Fetch Additional Formats (active only, exclude archived ones)
    $stmtFmt = $pdo->prepare("SELECT id, format_name, file_name, version FROM sop_formats WHERE sop_id = ? AND status = 'active' ORDER BY format_name ASC");
    $stmtFmt->execute([$sopId]);
    $formats = $stmtFmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'main_file' => $mainSop['image'],
        'title' => $mainSop['title'],
        'formats' => $formats
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>