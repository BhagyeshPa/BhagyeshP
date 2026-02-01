<?php
session_start();
require_once 'helpers.php';
requireLogin();

header('Content-Type: application/json');

$sopNumber = $_GET['number'] ?? $_GET['sop_number'] ?? '';

if (empty($sopNumber)) {
    echo json_encode(['success' => false, 'message' => 'SOP number is required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Fetch SOP details by number (only active SOPs)
    $stmt = $pdo->prepare("SELECT f.id, f.title, f.sop_number, f.image, f.status, d.code as department_code 
                           FROM fileup f 
                           LEFT JOIN departments d ON f.department_id = d.id 
                           WHERE f.sop_number LIKE ? AND f.status = 'active'");
    $stmt->execute(['%' . $sopNumber . '%']);
    $sop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sop) {
        echo json_encode(['success' => false, 'message' => 'Format not found']);
        exit;
    }
    
    // Fetch formats for this SOP
    $formatsStmt = $pdo->prepare("SELECT id, format_name, file_name FROM sop_formats WHERE sop_id = ?");
    $formatsStmt->execute([$sop['id']]);
    $formats = $formatsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'sop' => [
            'id' => $sop['id'],
            'title' => $sop['title'],
            'sop_number' => $sop['sop_number'],
            'version' => '1.0',
            'department_code' => $sop['department_code'],
            'main_file' => $sop['image'],
            'status' => $sop['status']
        ],
        'formats' => $formats
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
