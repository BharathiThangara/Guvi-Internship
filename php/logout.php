<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';

$headers = getallheaders();
$sessionToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (empty($sessionToken)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $result = $redis->del('session:' . $sessionToken);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Session not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>