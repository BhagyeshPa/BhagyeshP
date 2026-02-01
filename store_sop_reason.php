<?php
session_start();
require_once 'helpers.php';
requireLogin();

header('Content-Type: application/json');

$reason = $_POST['reason'] ?? '';
$file = $_POST['file'] ?? '';
$title = $_POST['title'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate input
if (empty($reason) || empty($file) || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check minimum 6 words requirement
$words = count(array_filter(explode(' ', trim($reason))));
if ($words < 6) {
    echo json_encode(['success' => false, 'message' => "Minimum 6 words required. You have $words words"]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Store the reason in sop_access_requests table
    $stmt = $pdo->prepare("INSERT INTO sop_access_requests (user_id, sop_number, reason) 
                          VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $file, $reason]);
    
    // Log in audit trail
    $ipAddress = getClientIP();
    logAudit($pdo, $user_id, 'Format Access Request', "Requested access to: $title with reason: " . substr($reason, 0, 100), $ipAddress);
    
    // Store reason in session for print_sop.php
    $_SESSION['sop_request_reason'] = $reason;
    
    echo json_encode(['success' => true, 'message' => 'Request stored successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
