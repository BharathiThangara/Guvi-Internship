<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    $stmt = $mysqlConn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit;
    }
    $stmt->close();
    
    $stmt = $mysqlConn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    $stmt->close();
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $mysqlConn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        
        
        echo json_encode(['success' => true, 'message' => 'Registration successful! Please login.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>