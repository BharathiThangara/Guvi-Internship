<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['username']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$username = trim($input['username']);
$password = $input['password'];

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

try {
    $stmt = $mysqlConn->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    $sessionToken = bin2hex(random_bytes(32));
    
    $sessionData = json_encode([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'login_time' => time()
    ]);
    
    $redis->setex('session:' . $sessionToken, 86400, $sessionData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'sessionToken' => $sessionToken,
        'username' => $user['username']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>